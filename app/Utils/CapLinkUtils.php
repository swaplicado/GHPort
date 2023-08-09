<?php  namespace App\Utils;

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

        $appBreakDowns = ApplicationsBreakdown::where('application_id', $oIncidences->id_application)->get()->pluck('id_application_breakdown')->toArray();

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

        $response = $client->request('GET', 'getCancel/' . json_encode($arrJson));
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
                "email": "cap@swaplicado.com.mx",
                "password": "1234"
        }';

        $response = $client->request('POST', 'login' , [
            'body' => $body
        ]);

        $jsonString = $response->getBody()->getContents();

        $data = json_decode($jsonString);

        return $data;
    }

    public static function cancelIncidenceCAP($oIncidence){
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

        $external_employee_id = \DB::table('users')
                                    ->where('id', $oIncidence->user_id)
                                    ->value('external_id_n');

        $oIncidence->type_incident_id = incidencesUtils::matchCapIncidence($oIncidence->type_incident_id);

        $body = '{
            "num_employee": '.$external_employee_id.',
            "incident_id": '.$oIncidence->id_application.'
        }';
        
        $request = new \GuzzleHttp\Psr7\Request('POST', 'cancelincident', $headers, $body);
        $response = $client->sendAsync($request)->wait();
        $jsonString = $response->getBody()->getContents();

        // $data = json_decode($jsonString);
        return $jsonString;
    }
}
?>