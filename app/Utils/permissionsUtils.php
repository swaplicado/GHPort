<?php

namespace App\Utils;

use \App\Constants\SysConst;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use GuzzleHttp\Client;

class permissionsUtils {
    public static function getMyEmployeeslPermissions($cl = 0){
        $org_chart_job_id = delegationUtils::getOrgChartJobIdUser();
        $arrOrgJobs = orgChartUtils::getAllChildsOrgChartJobNoBoss($org_chart_job_id);
        $lPermissions = [];
        $lEmployees = EmployeeVacationUtils::getlEmployees($arrOrgJobs);
        foreach($lEmployees as $emp){
            array_push($lPermissions, permissionsUtils::getUserPermissions($emp->id,$cl));
        }

        $lPermissions = Arr::collapse($lPermissions);

        return $lPermissions;
    }

    public static function getMyManagerlPermissions($org_chart_job_id, $cl){
        $arrOrgJobs = orgChartUtils::getAllChildsOrgChartJobNoBoss($org_chart_job_id);
        $lPermissions = [];
        $lEmployees = EmployeeVacationUtils::getlEmployees($arrOrgJobs);
        foreach($lEmployees as $emp){
            array_push($lPermissions, permissionsUtils::getUserPermissions($emp->id,$cl));
        }

        $lPermissions = Arr::collapse($lPermissions);

        return $lPermissions;
    }

    public static function getPermission($permission_id){
        $oPermission = \DB::table('hours_leave as h')
                        ->leftJoin('cat_permission_tp as pt', 'pt.id_permission_tp', '=', 'h.type_permission_id')
                        ->leftJoin('permission_cl as cl', 'cl.id_permission_cl', '=', 'h.cl_permission_id')
                        ->leftJoin('users as u', 'u.id', '=', 'h.user_apr_rej_id')
                        ->leftJoin('users as emp', 'emp.id', '=', 'h.user_id')
                        ->where('h.id_hours_leave', $permission_id)
                        ->select(
                            'h.*',
                            'pt.permission_tp_name',
                            'cl.permission_cl_name',
                            'u.full_name_ui as user_apr_rej_name',
                            'emp.full_name_ui as employee',
                        )
                        ->first();

        if($oPermission->type_permission_id!== SysConst::PERMISO_INTERMEDIO){
            $result = permissionsUtils::convertMinutesToHours($oPermission->minutes);
            $oPermission->hours = $result[0];
            $oPermission->min = $result[1];
            $oPermission->time = $result[0].':'.$result[1].' hrs';
        }else{
            $interOut = Carbon::createFromFormat('H:i:s', $oPermission->intermediate_out)->format('h:i A');
            $interReturn = Carbon::createFromFormat('H:i:s', $oPermission->intermediate_return)->format('h:i A');
            $oPermission->time = $interOut.' a '.$interReturn;
        }
        

        return $oPermission;
    }

    public static function getUserPermissions($user_id,$cl = 0){
        if($cl != 0){
            $lPermissions = \DB::table('hours_leave as hr')
                        ->leftJoin('cat_permission_tp as tp', 'tp.id_permission_tp', '=', 'hr.type_permission_id')
                        ->leftJoin('permission_cl as cl', 'cl.id_permission_cl', '=', 'hr.cl_permission_id')
                        ->leftJoin('sys_applications_sts as st', 'st.id_applications_st', '=', 'hr.request_status_id')
                        ->leftJoin('users as u', 'u.id', '=', 'hr.user_apr_rej_id')
                        ->leftJoin('users as emp', 'emp.id', '=', 'hr.user_id')
                        ->where('hr.is_deleted', 0)
                        ->where('hr.user_id', $user_id)
                        ->where('cl.id_permission_cl',$cl)
                        ->select(
                            'hr.*',
                            'tp.id_permission_tp',
                            'tp.permission_tp_name',
                            'cl.permission_cl_name',
                            'st.applications_st_name',
                            'u.full_name_ui as user_apr_rej_name',
                            'emp.full_name_ui as employee',
                        )
                        ->get();
        }else{
            $lPermissions = \DB::table('hours_leave as hr')
                        ->leftJoin('cat_permission_tp as tp', 'tp.id_permission_tp', '=', 'hr.type_permission_id')
                        ->leftJoin('permission_cl as cl', 'cl.id_permission_cl', '=', 'hr.cl_permission_id')
                        ->leftJoin('sys_applications_sts as st', 'st.id_applications_st', '=', 'hr.request_status_id')
                        ->leftJoin('users as u', 'u.id', '=', 'hr.user_apr_rej_id')
                        ->leftJoin('users as emp', 'emp.id', '=', 'hr.user_id')
                        ->where('hr.is_deleted', 0)
                        ->where('hr.user_id', $user_id)
                        ->select(
                            'hr.*',
                            'tp.id_permission_tp',
                            'tp.permission_tp_name',
                            'cl.permission_cl_name',
                            'st.applications_st_name',
                            'u.full_name_ui as user_apr_rej_name',
                            'emp.full_name_ui as employee',
                        )
                        ->get();
        }
        

        foreach ($lPermissions as $permission) {
            if($permission->id_permission_tp!== SysConst::PERMISO_INTERMEDIO){
                $result = permissionsUtils::convertMinutesToHours($permission->minutes);
                $permission->time = $result[0].':'.$result[1].' hrs';
            }else{
                $interOut = Carbon::createFromFormat('H:i:s', $permission->intermediate_out)->format('h:i A');
                $interReturn = Carbon::createFromFormat('H:i:s', $permission->intermediate_return)->format('h:i A');
                $permission->time = $interOut.' a '.$interReturn;
            }
        }

        return $lPermissions;
    }

    public static function hoursToMinutes($hours){
        return $hours * 60;
    }

    /**
     * Funcion que transformar el formato xx a formato entero,
     * si los minutos son 05 regresa solo 5, si son 15 regresa 15.
     */
    public static function minutesToInteger($minutes){
        return intval($minutes);
    }

    public static function getTime($hours, $minutes){
        $time = permissionsUtils::hoursToMinutes($hours) + permissionsUtils::minutesToInteger($minutes);
        return $time;
    }

    public static function convertMinutesToHours($total_minutes) {
        $hours = floor($total_minutes / 60);
        $minutes = $total_minutes % 60;
        $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
        return array($hours, $minutes);
    }
    
    public static function sendPermissionToCAP($oPermission){
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

        $adjus_type_id = $oPermission->type_permission_id == 1 ? 3 : 8;

        $external_employee_id = \DB::table('users')
                                    ->where('id', $oPermission->user_id)
                                    ->value('external_id_n');

        $body = '{
            "dt_date": "'.$oPermission->start_date.'",
            "minutes": "'.$oPermission->minutes.'",
            "comments": "'.$oPermission->emp_comments_n.'",
            "ext_key": "'.$oPermission->id_hours_leave.'",
            "ext_sys": "pgh",
            "adjust_type_id": '.$adjus_type_id.',
            "employee_id": '.$external_employee_id.'
          }';
        
        $request = new \GuzzleHttp\Psr7\Request('POST', 'saveadjust', $headers, $body);
        $response = $client->sendAsync($request)->wait();
        $jsonString = $response->getBody()->getContents();

        $data = json_decode($jsonString);
        return $data;
    }

    public static function diffInMinutes($timeA, $timeB){
        $hora1 = Carbon::parse($timeA);
        $hora2 = Carbon::parse($timeB);

        $diferencia = $hora1->diffInMinutes($hora2);

        return $diferencia;
    }

    public static function checkExistPermission($oPermission){
        try {
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

            $adjus_type_id = $oPermission->type_permission_id == 1 ? 3 : 8;

            $external_employee_id = \DB::table('users')
                                        ->where('id', $oPermission->user_id)
                                        ->value('external_id_n');

            $body = '{
                "employee_id": '.$external_employee_id.',
                "adjust_type_id": '.$adjus_type_id.',
                "dt_date": "'.$oPermission->start_date.'"
            }';
            
            $request = new \GuzzleHttp\Psr7\Request('POST', 'checkadjust', $headers, $body);
            $response = $client->sendAsync($request)->wait();
            $jsonString = $response->getBody()->getContents();

            $data = json_decode($jsonString);
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => $th->getMessage()]);
        }

        return json_encode(['success' => true, 'data' => $data]);
    }
}