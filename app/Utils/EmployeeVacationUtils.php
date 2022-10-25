<?php namespace App\Utils;

use Carbon\Carbon;
use App\Constants\SysConst;

class EmployeeVacationUtils {
    public const months_code = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
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
    
    public static function getEmployeeVacations($id, $years, $startYear = null){
        $oVacation = \DB::table('vacation_users as vu')
                        ->where('vu.is_deleted', 0)
                        ->where('vu.user_id', $id)
                        ->where('vu.date_end', '<', Carbon::now()->addYears($years));

        if(!is_null($startYear)){
            $oVacation = $oVacation->where('year', '>=', $startYear);
        }

         $oVacation = $oVacation->select(
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
                                                            SysConst::APPLICATION_CREADO,
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

    public static function getEmployeeVacationsData($id){
        $config = \App\Utils\Configuration::getConfigurations();

        $user = \DB::table('users as u')
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
                    ->where('u.id', $id)
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
                    ->first();

        $user->vacation = EmployeeVacationUtils::getEmployeeVacations($id, $config->showVacation->years);
        $user->actual_vac_days = 0;
        $user->prox_vac_days = 0;
        $user->prop_vac_days = 0;

        foreach($user->vacation as $vac){
            $date_start = Carbon::parse($vac->date_start);
            $date_end = Carbon::parse($vac->date_end);

            $oVacConsumed = EmployeeVacationUtils::getVacationConsumed($id, $vac->year);
            $vac_request = EmployeeVacationUtils::getVacationRequested($id, $vac->year);

            $vac->request = 0;
            $vac->oRequest = null;
            if(!is_null($vac_request)){
                if(sizeof($vac_request) > 0){
                    $vac->request = collect($vac_request)->sum('days_effective');
                    $vac->oRequest = $vac_request;
                }
            }

            $vac->oVacConsumed = null;
            $vac->num_vac_taken = 0;
            $vac->remaining = $vac->vacation_days - $vac->request;
            if(!is_null($oVacConsumed)){
                if(sizeof($oVacConsumed) > 0){
                    $vac->oVacConsumed = $oVacConsumed;
                    $vac->num_vac_taken = collect($oVacConsumed)->sum('day_consumption');
                    $vac->remaining = $vac->vacation_days - collect($oVacConsumed)->sum('day_consumption') - $vac->request;
                }
            }

            if(Carbon::today()->gt($date_start) && Carbon::today()->lt($date_end)){
                $user->prox_vac_days = $vac->remaining;
                $user->prop_vac_days = number_format(((Carbon::today()->diffInDays($date_start) * $user->prox_vac_days) / $date_start->diffInDays($date_end)), 2);
            }

            if($date_start->lt(Carbon::today()) && $date_end->lt(Carbon::today()) && Carbon::today()->diffInYears($date_end) < 1){
                $user->actual_vac_days = $vac->remaining;
            }

            $date_expiration = Carbon::parse($date_end->addDays(1))->addYears($config->expiration_vacations->years)->addMonths($config->expiration_vacations->months);
            
            if(Carbon::now()->greaterThan($date_expiration)){
                if($vac->remaining > 0){
                    $vac->expired = $vac->remaining;
                    $vac->remaining = 0;
                }else{
                    $vac->expired = 0;
                }
            }else{
                $vac->expired = 0;
            }
        }

        if(count($user->vacation) > 0){
            $coll = collect($user->vacation);
            $user->tot_vacation_days = $coll->sum('vacation_days');
            $user->tot_vacation_taken = $coll->sum('num_vac_taken');
            $user->tot_vacation_remaining = $coll->sum('remaining');
            $user->tot_vacation_expired = $coll->sum('expired');
            $user->tot_vacation_request = $coll->sum('request');
        }else{
            $user->tot_vacation_days = 0;
            $user->tot_vacation_taken = 0;
            $user->tot_vacation_remaining = 0;
            $user->tot_vacation_expired = 0;
            $user->tot_vacation_request = 0;
        }
        $user->applications = EmployeeVacationUtils::getApplications($id, Carbon::now()->year);
        return $user;
    }

    public static function getVacations($lEmployees, $startYear = null){
        $config = \App\Utils\Configuration::getConfigurations();
        foreach($lEmployees as $emp){
            $emp->vacation = EmployeeVacationUtils::getEmployeeVacations($emp->id, $config->showVacation->years, $startYear);

            foreach($emp->vacation as $vac){
                $date_start = Carbon::parse($vac->date_start);
                $date_end = Carbon::parse($vac->date_end);
                
                $vac->date_start = EmployeeVacationUtils::months_code[$date_start->month].'-'.$date_start->format('Y');
                $vac->date_end = EmployeeVacationUtils::months_code[$date_end->month].'-'.$date_end->format('Y');

                $oVacConsumed = EmployeeVacationUtils::getVacationConsumed($emp->id, $vac->year);
                $vac_request = EmployeeVacationUtils::getVacationRequested($emp->id, $vac->year);
                
                if(!is_null($vac_request)){
                    $vac->request = collect($vac_request)->sum('days_effective');
                }else{
                    $vac->request = 0;
                }

                if(!is_null($oVacConsumed)){
                    $vac->oVacConsumed = $oVacConsumed;
                    $vac->num_vac_taken = collect($oVacConsumed)->sum('day_consumption');
                    $vac->remaining = $vac->vacation_days - collect($oVacConsumed)->sum('day_consumption') - $vac->request;
                }else{
                    $vac->oVacConsumed = null;
                    $vac->num_vac_taken = 0;
                    $vac->remaining = $vac->vacation_days - $vac->request;
                }

                $date_expiration = Carbon::parse($date_end->addDays(1))->addYears($config->expiration_vacations->years)->addMonths($config->expiration_vacations->months);
                
                if(Carbon::now()->greaterThan($date_expiration)){
                    if($vac->remaining > 0){
                        $vac->expired = $vac->remaining;
                        $vac->remaining = 0;
                    }else{
                        $vac->expired = 0;
                    }
                }else{
                    $vac->expired = 0;
                }
            }
            
            if(count($emp->vacation) > 0){
                $coll = collect($emp->vacation);
                $emp->tot_vacation_days = $coll->sum('vacation_days');
                $emp->tot_vacation_taken = $coll->sum('num_vac_taken');
                $emp->tot_vacation_remaining = $coll->sum('remaining');
                $emp->tot_vacation_expired = $coll->sum('expired');
                $emp->tot_vacation_request = $coll->sum('request');
            }else{
                $emp->tot_vacation_days = 0;
                $emp->tot_vacation_taken = 0;
                $emp->tot_vacation_remaining = 0;
                $emp->tot_vacation_expired = 0;
                $emp->tot_vacation_request = 0;
            }
        }

        return $lEmployees;
    }

    public static function getTakedDays($oUser){
        $holidays = \DB::table('holidays')
                        ->where('fecha', '>', Carbon::now()->subDays(30)->toDateString())
                        ->where('is_deleted', 0)
                        ->pluck('fecha')->toArray();

        foreach($oUser->applications as $app){
            $returnDate = Carbon::parse($app->end_date)->addDays(1);
            for($i = 0; $i < 31; $i++){
                switch ($returnDate->dayOfWeek) {
                    case 6:
                        if($oUser->payment_frec_id == SysConst::QUINCENA){
                            $returnDate->addDays(2);
                        }
                        break;
                    case 0:
                        $returnDate->addDays(1);
                        break;
                    default:
                        break;
                }
                
                if(!in_array($returnDate->toDateString(), $holidays)){
                    $app->returnDate = $returnDate->toDateString();
                    break;
                }else{
                    $returnDate->addDays(1);
                }
            }

            $oDate = Carbon::parse($app->start_date);
            $diffDays =  $oDate->diffInDays(Carbon::parse($app->end_date));
            $app->takedDays = 0;
            for ($i = 0; $i <= $diffDays; $i++) {
                if(
                    (!$app->take_rest_days ? ($oDate->dayOfWeek != 0 && $oDate->dayOfWeek != 6) : true) &&
                    (!$app->take_holidays ? (!in_array($oDate->toDateString(), $holidays)) : true)
                ){
                    $app->takedDays = $app->takedDays + 1;
                }
                $oDate->addDays(1);
            }
        }

        return $oUser->applications;
    }
}