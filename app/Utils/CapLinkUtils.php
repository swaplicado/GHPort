<?php  namespace App\Utils;

use App\Constants\SysConst;
use App\Models\Vacations\ApplicationsBreakdown;
use GuzzleHttp\Client;

class CapLinkUtils {
    public static function cancelIncidence($oIncidences){
        $employee = \DB::table('users')
                        ->where('id', $oIncidences->user_id)
                        ->first();

        $ext_company_id = \DB::table('ext_company')
                            ->where('id_company', $employee->company_id)
                            ->value('external_id');

        if($oIncidences->type_incident_id == SysConst::TYPE_CUMPLEAÑOS || $oIncidences->type_incident_id == SysConst::TYPE_VACACIONES){
            $appBreakDowns = ApplicationsBreakdown::where('application_id', $oIncidences->id_application)->get()->pluck('id_application_breakdown')->toArray();
        }else{
            $appBreakDowns = [$oIncidences->id_application];
        }

        $typeIncident = \DB::table('cat_incidence_tps')
                            ->where('id_incidence_tp', $oIncidences->type_incident_id)
                            ->first();

        $ext_ids = \DB::table('tp_incidents_pivot')
                        ->where('tp_incident_id', $typeIncident->id_incidence_tp)
                        ->where('int_sys_id', 2)
                        ->first();

        $arrJson = [
            'employee_id' => $employee->external_id_n,
            'company_id' => $ext_company_id,
            'appBreakDowns' => $appBreakDowns,

        ];

        $config = \App\Utils\Configuration::getConfigurations();
        $client = new Client([
            'base_uri' => $config->urlSync,
            'timeout' => 30.0,
        ]);
        $sArr = json_encode($arrJson);

        $response = $client->request('GET', 'getCancel/' . $sArr);
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        return json_encode(['code' => $data->response->code, 'message' => $data->response->message]);
    }

    public static function loginToCAP(){
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        $config = \App\Utils\Configuration::getConfigurations();
        $client = new Client([
            'base_uri' => $config->urlSyncCAP,
            'timeout' => 30.0,
            'headers' => $headers,
            'verify' => false
        ]);

        $body = '{
                "name": "Admin",
                "password": "Super2023!"
        }';

        $response = $client->request('POST', 'login' , [
            'body' => $body
        ]);

        $jsonString = $response->getBody()->getContents();

        $data = json_decode($jsonString);

        return $data;
    }

    public static function cancelIncidenceCAP($oIncidence, $type = 'INCIDENCE'){
        $data = incidencesUtils::loginToCAP();
        $config = \App\Utils\Configuration::getConfigurations();
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => $data->token_type.' '.$data->access_token
        ];
        
        $client = new Client([
            'base_uri' => $config->urlSyncCAP,
            'timeout' => 30.0,
            'headers' => $headers
        ]);

        $external_employee_num = \DB::table('users')
                                    ->where('id', $oIncidence->user_id)
                                    ->value('employee_num');

        // $oIncidence->type_incident_id = incidencesUtils::matchCapIncidence($oIncidence->type_incident_id);

        if($type == 'INCIDENCE'){
            $oIncidence->incidence_id = $oIncidence->id_application;
            $oIncidence->permission_id = 0;
        }else{
            $oIncidence->permission_id = $oIncidence->id_hours_leave;
            $oIncidence->incidence_id = 0;
        }
        $body = '{
            "num_employee": '.$external_employee_num.',
            "incident_id": '.$oIncidence->incidence_id.',
            "adjust_id": '.$oIncidence->permission_id.'
        }';

        if($type == 'INCIDENCE'){
            $request = new \GuzzleHttp\Psr7\Request('POST', 'cancelincident', $headers, $body);
        }else{
            $request = new \GuzzleHttp\Psr7\Request('POST', 'canceladjust', $headers, $body);
        }

        $response = $client->sendAsync($request)->wait();
        $jsonString = $response->getBody()->getContents();

        // $data = json_decode($jsonString);
        return $jsonString;
    }
}
?>