<?php

namespace App\Utils;

use \App\Constants\SysConst;
use Illuminate\Support\Arr;
use App\Models\Vacations\requestVacationLog;
use GuzzleHttp\Client;
use GuzzleHttp\Request;
use GuzzleHttp\Exception\RequestException;

class incidencesUtils {
    public static function getMyEmployeeslIncidences(){
        $org_chart_job_id = delegationUtils::getOrgChartJobIdUser();
        $arrOrgJobs = orgChartUtils::getAllChildsOrgChartJobNoBoss($org_chart_job_id);
        $lIncidences = [];
        $lEmployees = EmployeeVacationUtils::getlEmployees($arrOrgJobs);
        foreach($lEmployees as $emp){
            array_push($lIncidences, incidencesUtils::getUserIncidences($emp->id));
        }

        $lIncidences = Arr::collapse($lIncidences);

        return $lIncidences;
    }

    public static function getMyManagerlIncidences($org_chart_job_id){
        $arrOrgJobs = orgChartUtils::getAllChildsOrgChartJobNoBoss($org_chart_job_id);
        $lIncidences = [];
        $lEmployees = EmployeeVacationUtils::getlEmployees($arrOrgJobs);
        foreach($lEmployees as $emp){
            array_push($lIncidences, incidencesUtils::getUserIncidences($emp->id));
        }

        $lIncidences = Arr::collapse($lIncidences);

        return $lIncidences;
    }

    public static function getUserIncidences($user_id){
        $lIncidences = \DB::table('applications as ap')
                        ->leftJoin('cat_incidence_tps as tp', 'tp.id_incidence_tp', '=', 'ap.type_incident_id')
                        ->leftJoin('cat_incidence_cls as cl', 'cl.id_incidence_cl', '=', 'tp.incidence_cl_id')
                        ->leftJoin('sys_applications_sts as st', 'st.id_applications_st', '=', 'ap.request_status_id')
                        ->leftJoin('users as u', 'u.id', '=', 'ap.user_apr_rej_id')
                        ->leftJoin('users as emp', 'emp.id', '=', 'ap.user_id')
                        ->where('type_incident_id', '!=', SysConst::TYPE_VACACIONES)
                        ->where('ap.is_deleted', 0)
                        ->where('ap.user_id', $user_id)
                        ->select(
                            'ap.*',
                            'tp.id_incidence_tp',
                            'tp.incidence_tp_name',
                            'cl.id_incidence_cl',
                            'cl.incidence_cl_name',
                            'st.applications_st_name',
                            'u.full_name_ui as user_apr_rej_name',
                            'emp.full_name_ui as employee',
                        )
                        ->get();

        return $lIncidences;
    }

    public static function checkExternalIncident($oApplication){
        $employee = \DB::table('users')
                        ->where('id', $oApplication->user_id)
                        ->first();

        $ext_company_id = \DB::table('ext_company')
                            ->where('id_company', $employee->company_id)
                            ->value('external_id');

        $typeIncident = \DB::table('cat_incidence_tps')
                            ->where('id_incidence_tp', $oApplication->type_incident_id)
                            ->first();

        $userVacation = \DB::table('vacation_users')
                            ->where('user_id', $employee->id)
                            ->where('is_deleted', 0)
                            ->get();
        $count = 0;
        
        $arrJson = [
            'to_insert' => false,
            'application_id' => $oApplication->id_application,
            'folio' => $oApplication->folio_n,
            'employee_id' => $employee->external_id_n,
            'company_id' => $ext_company_id,
            'type_pay_id' => $employee->payment_frec_id,
            'type_incident_id' => $typeIncident->id_incidence_tp,
            'class_incident_id' => $typeIncident->incidence_cl_id,
            'date_send' => $oApplication->date_send_n,
            'date_ini' => $oApplication->start_date,
            'date_end' => $oApplication->end_date,
            'total_days' => $oApplication->total_days
        ];
        $config = \App\Utils\Configuration::getConfigurations();
        $client = new Client([
            'base_uri' => $config->urlSync,
            'timeout' => 30.0,
        ]);

        $str = json_encode($arrJson);

        $response = $client->request('GET', 'postIncidents/' . json_encode($arrJson));
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        return json_encode(['code' => $data->response->code, 'message' => $data->response->message]);
    }

    public static function sendIncidence($oApplication){
        $employee = \DB::table('users')
                        ->where('id', $oApplication->user_id)
                        ->first();

        $ext_company_id = \DB::table('ext_company')
                            ->where('id_company', $employee->company_id)
                            ->value('external_id');

        $typeIncident = \DB::table('cat_incidence_tps')
                            ->where('id_incidence_tp', $oApplication->type_incident_id)
                            ->first();

        if($oApplication->type_incident_id == SysConst::TYPE_CUMPLEAÑOS){
            $appBreakdown = \DB::table('applications_breakdowns')
                                ->where('application_id', $oApplication->id_application)
                                ->first();

            $row = [
                'breakdown_id' => $appBreakdown->id_application_breakdown,
                'folio' => $oApplication->folio_n,
                'effective_days' => 1,
                'year' => $appBreakdown->application_year,
                'anniversary' => 0,
                'start_date' => $oApplication->start_date,
                'end_date' => $oApplication->end_date,
            ];
        }else{
            $row = [
                'breakdown_id' => $oApplication->id_application,
                'folio' => $oApplication->folio_n,
                'effective_days' => $oApplication->total_days,
                'year' => 0,
                'anniversary' => 0,
                'start_date' => $oApplication->start_date,
                'end_date' => $oApplication->end_date,
            ];
        }
        $rows = [];
        array_push($rows, $row);

        $ext_ids = \DB::table('tp_incidents_pivot')
                    ->where('tp_incident_id', $typeIncident->id_incidence_tp)
                    ->where('int_sys_id', 2)
                    ->first();
        
        $arrJson = [
            'to_insert' => true,
            'application_id' => $oApplication->id_application,
            'folio' => $oApplication->folio_n,
            'employee_id' => $employee->external_id_n,
            'company_id' => $ext_company_id,
            'type_pay_id' => $employee->payment_frec_id,
            'type_incident_id' => $typeIncident->id_incidence_tp,
            'class_incident_id' => $typeIncident->incidence_cl_id,
            'tp_abs' => $ext_ids->ext_tp_incident_id,
            'cl_abs' => $ext_ids->ext_cl_incident_id,  
            'date_send' => $oApplication->date_send_n,
            'date_ini' => $oApplication->start_date,
            'date_end' => $oApplication->end_date,
            'total_days' => $oApplication->total_days,
            'rows' => $rows,
        ];

        $str = json_encode($arrJson);

        $config = \App\Utils\Configuration::getConfigurations();
        $client = new Client([
            'base_uri' => $config->urlSync,
            'timeout' => 30.0,
        ]);

        $response = $client->request('GET', 'postIncidents/' . json_encode($arrJson));
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        $oVacLog = new requestVacationLog();
        $oVacLog->application_id = $oApplication->id_application;
        $oVacLog->employee_id = $oApplication->user_id;
        $oVacLog->response_code = $data->response->code;
        $oVacLog->message = $data->response->message;
        $oVacLog->created_by = delegationUtils::getIdUser();
        $oVacLog->updated_by = delegationUtils::getIdUser();
        $oVacLog->save();

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

    public static function sendToCAP($oApplication){
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

        $oApplication->cl_incident_id = \DB::table('cat_incidence_tps as tp')
                                            ->leftJoin('cat_incidence_cls as cl', 'cl.id_incidence_cl', '=', 'tp.incidence_cl_id')
                                            ->where('tp.id_incidence_tp', $oApplication->type_incident_id)
                                            ->value('cl.id_incidence_cl');

        $external_employee_id = \DB::table('users')
                                    ->where('id', $oApplication->user_id)
                                    ->value('external_id_n');

        $oApplication->type_incident_id = incidencesUtils::matchCapIncidence($oApplication->type_incident_id);

        $body = '{
            "ini_date": "'.$oApplication->start_date.'",
            "end_date": "'.$oApplication->end_date.'",
            "ext_key": "'.$oApplication->id_application.'",
            "ext_sys": "pgh",
            "folio": "'.$oApplication->folio_n.'",
            "cls_inc_id": "'.$oApplication->cl_incident_id.'",
            "type_inc_id": "'.$oApplication->type_incident_id.'",
            "type_sub_inc_id": null,
            "emp_comments": "'.$oApplication->emp_comments_n.'",
            "sup_comments": "'.$oApplication->sup_comments_n.'",
            "employee_id": '.$external_employee_id.',
            "inc_dates": '.$oApplication->ldays.'
        }';
        
        $request = new \GuzzleHttp\Psr7\Request('POST', 'saveincident', $headers, $body);
        $response = $client->sendAsync($request)->wait();
        $jsonString = $response->getBody()->getContents();

        // $data = json_decode($jsonString);
        return $jsonString;
    }

    public static function matchCapIncidence($incidence_id){
        $capIncidence_id = \DB::table('tp_incidents_pivot')
                                ->where('tp_incident_id', $incidence_id)
                                ->where('int_sys_id', 3)
                                ->value('ext_tp_incident_id');

        return $capIncidence_id;
    }
}