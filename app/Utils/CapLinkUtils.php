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
}
?>