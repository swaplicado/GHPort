<?php

namespace App\Utils;

use \App\Constants\SysConst;
use Illuminate\Support\Arr;

class incidencesUtils {
    public static function getMyEmployeeslPermissions(){
        $org_chart_job_id = delegationUtils::getOrgChartJobIdUser();
        $arrOrgJobs = orgChartUtils::getAllChildsOrgChartJobNoBoss($org_chart_job_id);
        $lPermissions = [];
        $lEmployees = EmployeeVacationUtils::getlEmployees($arrOrgJobs);
        foreach($lEmployees as $emp){
            array_push($lPermissions, incidencesUtils::getUserPermissions($emp->id));
        }

        $lPermissions = Arr::collapse($lPermissions);

        return $lPermissions;
    }

    public static function getUserPermissions($user_id){
        $lPermissions = \DB::table('hours_leave as hr')
                        ->leftJoin('cat_permission_tp as tp', 'tp.id_permission_tp', '=', 'hr.type_permission_id')
                        ->leftJoin('sys_applications_sts as st', 'st.id_applications_st', '=', 'hr.request_status_id')
                        ->leftJoin('users as u', 'u.id', '=', 'hr.user_apr_rej_id')
                        ->where('hr.is_deleted', 0)
                        ->where('hr.user_id', $user_id)
                        ->select(
                            'hr.*',
                            'tp.id_permission_tp',
                            'tp.permission_tp_name',
                            'st.applications_st_name',
                            'u.full_name_ui as user_apr_rej_name'
                        )
                        ->get();

        return $lPermissions;
    }

}