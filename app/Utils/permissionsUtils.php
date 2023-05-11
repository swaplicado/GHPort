<?php

namespace App\Utils;

use \App\Constants\SysConst;
use Illuminate\Support\Arr;

class permissionsUtils {
    public static function getMyEmployeeslPermissions(){
        $org_chart_job_id = delegationUtils::getOrgChartJobIdUser();
        $arrOrgJobs = orgChartUtils::getAllChildsOrgChartJobNoBoss($org_chart_job_id);
        $lPermissions = [];
        $lEmployees = EmployeeVacationUtils::getlEmployees($arrOrgJobs);
        foreach($lEmployees as $emp){
            array_push($lPermissions, permissionsUtils::getUserPermissions($emp->id));
        }

        $lPermissions = Arr::collapse($lPermissions);

        return $lPermissions;
    }

    public static function getPermission($permission_id){
        $oPermission = \DB::table('hours_leave')
                        ->where('id_hours_leave', $permission_id)
                        ->first();

        $result = permissionsUtils::convertMinutesToHours($oPermission->minutes);
        $oPermission->hours = $result[0];
        $oPermission->min = $result[1];

        return $oPermission;
    }

    public static function getUserPermissions($user_id){
        $lPermissions = \DB::table('hours_leave as hr')
                        ->leftJoin('cat_permission_tp as tp', 'tp.id_permission_tp', '=', 'hr.type_permission_id')
                        ->leftJoin('sys_applications_sts as st', 'st.id_applications_st', '=', 'hr.request_status_id')
                        ->leftJoin('users as u', 'u.id', '=', 'hr.user_apr_rej_id')
                        ->leftJoin('users as emp', 'emp.id', '=', 'hr.user_id')
                        ->where('hr.is_deleted', 0)
                        ->where('hr.user_id', $user_id)
                        ->select(
                            'hr.*',
                            'tp.id_permission_tp',
                            'tp.permission_tp_name',
                            'st.applications_st_name',
                            'u.full_name_ui as user_apr_rej_name',
                            'emp.full_name_ui as employee',
                        )
                        ->get();

        foreach ($lPermissions as $permission) {
            $result = permissionsUtils::convertMinutesToHours($permission->minutes);
            $permission->time = $result[0].':'.$result[1].' hrs';
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
    
}