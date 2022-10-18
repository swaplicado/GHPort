<?php namespace App\Utils;

use Carbon\Carbon;
use App\Constants\SysConst;

class EmployeeVacationUtils {

    public static function getlEmployees($arrOrgJobs){
        $lEmployees = \DB::table('users as u')
                        ->leftJoin('ext_jobs as j', 'j.id_job', '=', 'u.job_id')
                        ->leftJoin('ext_departments as d', 'd.id_department', '=', 'j.department_id')
                        ->leftJoin('cat_vacation_plans as vp', 'vp.id_vacation_plan', '=', 'u.vacation_plan_id')
                        ->where(function($query){
                            $query->where('j.is_deleted', 0)->orWhere('j.is_deleted', null);
                        })
                        ->where(function($query){
                            $query->where('d.is_deleted', 0)->orWhere('d.is_deleted', null);
                        })
                        ->where(function($query){
                            $query->where('vp.is_deleted', 0)->orWhere('vp.is_deleted', null);
                        })
                        ->where('u.is_active', 1)
                        ->where('u.is_delete', 0)
                        ->whereIn('u.org_chart_job_id', $arrOrgJobs)
                        ->select(
                            'u.id',
                            'u.employee_num',
                            'u.full_name_ui as employee',
                            'u.full_name',
                            'u.last_admission_date',
                            'u.org_chart_job_id',
                            'u.payment_frec_id',
                            'j.id_job',
                            'j.job_name_ui',
                            'd.id_department',
                            'd.department_name_ui',
                            'vp.id_vacation_plan',
                            'vp.vacation_plan_name',
                        )
                        ->get();

        return $lEmployees;
    }
    
    public static function getEmployeeVacations($id, $years){
        $oVacation = \DB::table('vacation_users as vu')
                        ->where('vu.is_deleted', 0)
                        ->where('vu.user_id', $id)
                        ->where('vu.date_end', '<', Carbon::now()->addYears($years))
                        ->select(
                            'vu.user_admission_log_id',
                            'vu.id_anniversary',
                            'vu.year',
                            'vu.date_start',
                            'vu.date_end',
                            'vu.vacation_days',
                            'vu.is_expired',
                            'vu.is_expired_manually',
                        )
                        ->orderBy('year', 'desc')
                        ->get();

        return $oVacation;
    }

    public static function getVacationConsumed($id, $year){
        $consumed_byApplication = \DB::table('vacation_allocations as va')
                                            ->Join('applications_breakdowns as ab', 'ab.id_application_breakdown', '=', 'va.application_breakdown_id')
                                            ->where('va.user_id', $id)
                                            ->where('va.is_deleted', 0)
                                            ->where('ab.application_year',  $year)
                                            ->select('va.*')
                                            ->get();

        $consumed_byAnniversary = \DB::table('vacation_allocations as va')
                                    ->where('va.user_id', $id)
                                    ->where('va.anniversary_count', $year)
                                    ->where('va.is_deleted', 0)
                                    ->get();

        $oConsumed = collect($consumed_byApplication)->merge(collect($consumed_byAnniversary));

        return $oConsumed;
    }

    public static function getVacationRequested($id, $year){
        $oRequested = \DB::table('applications as a')
                        ->join('applications_breakdowns as ab', 'ab.application_id', '=', 'a.id_application')
                        ->leftJoin('sys_applications_sts as as', 'as.id_applications_st', '=', 'a.request_status_id')
                        ->where('a.user_id', $id)
                        ->whereIn('a.request_status_id', [
                                                            SysConst::APPLICATION_ENVIADO,
                                                            SysConst::APPLICATION_APROBADO,
                                                        ]
                        )
                        ->where('a.is_deleted', 0)
                        ->where('ab.application_year', $year)
                        ->where(function($query){
                            $query->where('as.is_deleted', 0)
                                ->orWhere('as.is_deleted', null);
                        })
                        ->select(
                            'a.*',
                            'ab.days_effective',
                            'ab.application_year',
                            'ab.admition_count',
                            'as.applications_st_name',
                            'as.applications_st_code'
                        )
                        ->get();

        return $oRequested;
    }

    public static function getApplications($id, $year, $status = [1,2,3,4]){
        $oRequested = \DB::table('applications as a')
                        ->leftJoin('sys_applications_sts as as', 'as.id_applications_st', '=', 'a.request_status_id')
                        ->where('a.user_id', $id)
                        ->whereIn('a.request_status_id', $status)
                        ->where('a.is_deleted', 0)
                        ->whereYear('a.updated_at', $year)
                        ->where(function($query){
                            $query->where('as.is_deleted', 0)
                                ->orWhere('as.is_deleted', null);
                        })
                        ->select(
                            'a.*',
                            'as.applications_st_name',
                            'as.applications_st_code'
                        )
                        ->get();

        return $oRequested;
    }
}