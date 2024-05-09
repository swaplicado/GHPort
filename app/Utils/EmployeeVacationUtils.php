<?php namespace App\Utils;

use Carbon\Carbon;
use App\Constants\SysConst;
use App\Models\Vacations\Application;
use App\Models\Adm\VacationAllocation;
use App\Models\Vacations\ApplicationsBreakdown;
use App\Models\Seasons\SpecialSeason;
class EmployeeVacationUtils {
    public const months_code = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    
    /**
     * Obtiene la lista de empleados a partir de un arreglo con los id de los org_jobs
     */
    public static function getlEmployees($arrOrgJobs){
        $lEmployees = \DB::table('users as u')
                        ->leftJoin('users_vs_photos as up', 'up.user_id', '=', 'u.id')
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
                        ->where('u.id', '!=', 1)
                        ->whereIn('u.org_chart_job_id', $arrOrgJobs)
                        ->select(
                            'u.id',
                            'u.employee_num',
                            'u.full_name_ui as employee',
                            'u.full_name',
                            'u.birthday_n',
                            'u.last_admission_date',
                            'u.benefits_date',
                            'u.org_chart_job_id',
                            'u.payment_frec_id',
                            'u.company_id',
                            'u.external_id_n',
                            'j.id_job',
                            'j.job_name_ui',
                            'd.id_department',
                            'd.department_name_ui',
                            'vp.id_vacation_plan',
                            'vp.vacation_plan_name',
                            'up.photo_base64_n as photo64',
                        )
                        ->orderBy('employee')
                        ->get();

        return $lEmployees;
    }
    
    /**
     * Obtiene las vacaciones de un empleado, sin descontar las solicitudes ni las programadas
     */
    public static function getEmployeeVacations($id, $years, $getYears = null, $startYear = null, $disableLastYear = false){
        $config = \App\Utils\Configuration::getConfigurations();
        $lastAniversary = \DB::table('vacation_users')
                            ->where('user_id', $id)
                            ->where('date_end', '<=', Carbon::now()->toDateString())
                            ->max('date_end');

        $dEnd = Carbon::now()->addYears($years)->toDateString();
        $dtStart = Carbon::parse($lastAniversary)->subYears($getYears)->toDateString();

        $oVacation = \DB::table('vacation_users as vu')
                        ->where('vu.is_deleted', 0)
                        ->where('vu.user_id', $id)
                        ->where('vu.date_end', '<', $dEnd);

        if(!is_null($getYears)){
            $oVacation = $oVacation->where('vu.date_end', '>=', $dtStart);
        }

        if(!is_null($startYear)){
            $oVacation = $oVacation->where('year', '>=', $startYear);
        }

         $oVacation = $oVacation->select(
                            'vu.id_vacation_user',
                            'vu.user_admission_log_id',
                            'vu.id_anniversary',
                            'vu.year',
                            'vu.date_start',
                            'vu.date_end',
                            'vu.vacation_days',
                            'vu.is_expired',
                            'vu.is_expired_manually',
                        )
                        ->orderBy('year', $config->orderVac)
                        ->get();

        if($disableLastYear){
            if(count($oVacation) > 0){
                $oVacation[0]->vacation_days = 0;
            }
        }

        return $oVacation;
    }

    /**
     * Obtiene el proximo renglon de vacaciones
     */
    public static function getProxVacation($employee_id){
        $nextVac = \DB::table('vacation_users')
                            ->where('user_id', $employee_id)
                            ->where('date_end', '>', Carbon::now()->toDateString())
                            ->orderBy('date_end', 'asc')
                            ->first();

        return $nextVac;
    }

    /**
     * Obtiene el proximo renglon de vacaciones junto con los datos de las applications a ese año
     */
    public static function getProxVacationWithApplications($employee_id){
        $nextVac = \DB::table('vacation_users')
                            ->where('user_id', $employee_id)
                            ->where('date_end', '>', Carbon::now()->toDateString())
                            ->orderBy('date_end', 'asc')
                            ->first();

        $appBrk = \DB::table('applications as a')
                    ->join('applications_breakdowns as ab', 'ab.application_id', '=', 'a.id_application')
                    ->where('a.user_id', $employee_id)
                    ->where('a.is_deleted', 0)
                    ->whereIn('a.request_status_id', [SysConst::APPLICATION_APROBADO, SysConst::APPLICATION_ENVIADO])
                    ->where('a.type_incident_id', SysConst::TYPE_VACACIONES)
                    ->where('ab.application_year', $nextVac->year)
                    ->sum('ab.days_effective');

        // $appBrk = \DB::table('applications_breakdowns')
        //             ->where('application_year', $nextVac->year)
        //             ->sum('days_effective');

        $nextVac->vacation_days = $nextVac->vacation_days - $appBrk;

        return $nextVac;
    }

    /**
     * Obtiene las vacaciones consumidas por un empleado
     */
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
                                    ->where('va.id_anniversary', $year)
                                    ->where('va.application_breakdown_id', null)
                                    ->where('va.is_deleted', 0)
                                    ->get();

        $oConsumed = collect($consumed_byApplication)->merge(collect($consumed_byAnniversary));

        return $oConsumed;
    }

    /**
     * Obtiene las solicitudes con sus renglones, de vacaciones de un empleado, solo las creadas, enviadas y aprobadas
     */
    public static function getVacationRequested($id, $year){
        $oRequested = \DB::table('applications as a')
                        ->join('applications_breakdowns as ab', 'ab.application_id', '=', 'a.id_application')
                        ->leftJoin('sys_applications_sts as as', 'as.id_applications_st', '=', 'a.request_status_id')
                        ->where('a.user_id', $id)
                        ->whereIn('a.request_status_id', [
                                                            // SysConst::APPLICATION_CREADO,
                                                            SysConst::APPLICATION_ENVIADO,
                                                            SysConst::APPLICATION_APROBADO,
                                                        ]
                        )
                        ->where('a.is_deleted', 0)
                        ->where('a.type_incident_id', SysConst::TYPE_VACACIONES)
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

    /**
     * Obtiene las vacaciones programadas de un empleado
     */
    public static function getProgramed($id, $year){
        $programed = \DB::table('programed_aux')
                        ->where('employee_id', $id)
                        ->where('is_deleted',0)
                        ->where('year', $year)
                        ->get();

        return $programed;
    }

    /**
     * Obtiene las solicitudes de vacaciones con el estatus que se inserte en el metodo
     */
    public static function getApplications($id, $year, $status = [
                                                                SysConst::APPLICATION_CREADO,
                                                                SysConst::APPLICATION_ENVIADO,
                                                                SysConst::APPLICATION_RECHAZADO,
                                                                SysConst::APPLICATION_APROBADO,
                                                                SysConst::APPLICATION_CONSUMIDO,
                                                                SysConst::APPLICATION_CANCELADO,
                                                            ]
                                                        ){
        $oRequested = \DB::table('applications as a')
                        ->leftJoin('sys_applications_sts as as', 'as.id_applications_st', '=', 'a.request_status_id')
                        ->leftJoin('users as u', 'u.id', '=', 'a.user_apr_rej_id')
                        ->leftJoin('applications_vs_types as at', 'at.application_id', '=', 'a.id_application')
                        ->where('a.user_id', $id)
                        ->whereIn('a.request_status_id', $status)
                        ->where('a.type_incident_id', SysConst::TYPE_VACACIONES)
                        ->where('a.is_deleted', 0);

        if(!is_null($year)){
            $oRequested = $oRequested->whereYear('a.updated_at', $year);
        }

        $oRequested = $oRequested->where(function($query){
                            $query->where('as.is_deleted', 0)
                                ->orWhere('as.is_deleted', null);
                        })
                        ->where(function($query){
                            $query->where('a.user_apr_rej_id', '!=', null)
                                ->orWhere('a.user_apr_rej_id', null);
                        })
                        ->select(
                            'a.*',
                            'at.id_application_vs_type',
                            'at.id_application_vs_type',
                            'at.application_id',
                            'at.is_normal',
                            'at.is_past',
                            'at.is_advanced',
                            'at.is_proportional',
                            'at.is_season_special',
                            'at.is_recover_vacation',
                            'at.is_event',
                            'u.full_name_ui as user_apr_rej_name',
                            'as.applications_st_name',
                            'as.applications_st_code',
                        )
                        ->get();

        return $oRequested;
    }

    /**
     * Obtiene las vacaciones de un empleado,
     * regresa un objecto usuario con la informacion de sus vacaciones:
     *  ganadas,
     *  gozadas,
     *  pendientes,
     *  vencidas,
     *  solicitadas
     */
    public static function getEmployeeVacationsData($id, $isAllHistory = false, $customYear = null){
        $config = \App\Utils\Configuration::getConfigurations();

        $user = \DB::table('users as u')
                    ->leftJoin('users_vs_photos as up', 'up.user_id', '=', 'u.id')
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
                        'u.birthday_n',
                        'u.last_admission_date',
                        'u.benefits_date',
                        'u.org_chart_job_id',
                        'u.payment_frec_id',
                        'u.company_id',
                        'u.job_id',
                        'j.id_job',
                        'j.job_name_ui',
                        'd.id_department',
                        'd.department_name_ui',
                        'vp.id_vacation_plan',
                        'vp.vacation_plan_name',
                        'up.photo_base64_n as photo64',
                    )
                    ->first();

        if(is_null($customYear)){
            $checkYear = EmployeeVacationUtils::getLastYearNumberIncident($id);
            $customYear = $config->showVacation->years;
            $disableLastYear = $customYear < $checkYear;
            $customYear = $customYear < $checkYear ? $checkYear : $customYear;
            
            if(!is_null($checkYear)){
                $from = Carbon::parse($user->benefits_date);
                $to = Carbon::today()->locale('es');
                if($to->diffInYears($from) < 1){
                    $customYear = 1;
                }
            }
            $user->vacation = EmployeeVacationUtils::getEmployeeVacations($id, $customYear, null, null, $disableLastYear);
        }else{
            $user->vacation = EmployeeVacationUtils::getEmployeeVacations($id, $customYear);
        }
        // $oNextVacation = EmployeeVacationUtils::getProxVacation($id);
        if(count($user->vacation) > 0){
            $oNextVacation = EmployeeVacationUtils::getProxVacationWithApplications($id);
        }else{
            $oNextVacation = null;
        }
        
        $user->actual_vac_days = 0;
        $user->prox_vac_days = 0;
        $user->prop_vac_days = 0;

        foreach($user->vacation as $vac){
            $date_start = Carbon::parse($vac->date_start);
            $date_end = Carbon::parse($vac->date_end);

            $nextDateStart = Carbon::parse($oNextVacation->date_start);
            $nextDateEnd = Carbon::parse($oNextVacation->date_end);

            $oVacConsumed = EmployeeVacationUtils::getVacationConsumed($id, $vac->year);
            $vac_request = EmployeeVacationUtils::getVacationRequested($id, $vac->year);
            foreach($oVacConsumed as $Vcon){
                if($Vcon->application_breakdown_id != null){
                    $application_id = \DB::table('applications_breakdowns as ab')
                                        ->where('id_application_breakdown', $Vcon->application_breakdown_id)
                                        ->value('application_id');
    
                    foreach($vac_request as $req){
                        if($req->id_application == $application_id){
                            $req->days_effective = $req->days_effective - $Vcon->day_consumption;
                        }
                    }
                }
            }
            $vac_programed = EmployeeVacationUtils::getProgramed($id, $vac->year);

            $vac->request = 0;
            $vac->oRequest = null;
            if(!is_null($vac_request)){
                if(sizeof($vac_request) > 0){
                    $vac->request = collect($vac_request)->sum('days_effective');
                    $vac->oRequest = $vac_request;
                }
            }

            $vac->programed = 0;
            $vac->oProgramed = null;
            if(!is_null($vac_programed)){
                if(sizeof($vac_programed) > 0){
                    $vac->programed = collect($vac_programed)->sum('days_to_consumed');
                    $vac->request = $vac->request + $vac->programed;
                    $vac->oProgramed = $vac_programed;
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

            if(Carbon::today()->gt($date_start) && Carbon::today()->lt($nextDateEnd) && $date_end->diffInYears(Carbon::today()) < 1){
                // $user->prox_vac_days = $vac->remaining;
                $user->prox_vac_days = $oNextVacation->vacation_days;
                $d = Carbon::today()->diffInDays($date_end);
                $b = $nextDateStart->diffInDays($nextDateEnd);
                // $user->prop_vac_days = number_format(((Carbon::today()->diffInDays($date_start) * $user->prox_vac_days) / $date_start->diffInDays($date_end)), 2);
                $user->prop_vac_days = number_format(((Carbon::today()->diffInDays($date_end) * $user->prox_vac_days) / $nextDateStart->diffInDays($nextDateEnd)), 2);
            }

            if($date_start->lt(Carbon::today()) && $date_end->lt(Carbon::today()) && Carbon::today()->diffInYears($date_end) < 1){
                $user->actual_vac_days = $vac->remaining;
            }

            $date_expiration = Carbon::parse($date_end->addDays(1))->addYears($config->expiration_vacations->years)->addMonths($config->expiration_vacations->months);
            $vac->is_recovered = 0;
            if(Carbon::now()->greaterThan($date_expiration)){
                if($vac->remaining > 0){
                    $now_date = Carbon::now()->toDateString();
                    $oVacRecover = \DB::table('recovered_vacations')
                                        ->where('user_id', $user->id)
                                        ->where('vacation_user_id', $vac->id_vacation_user)
                                        ->where(function($query) use($now_date){
                                            $query->where('end_date', '>=', $now_date)
                                                ->orWhere('is_used', 1);
                                        })
                                        ->where('is_deleted', 0)
                                        ->first();

                    if(is_null($oVacRecover)){
                        $vac->expired = $vac->remaining;
                        $vac->remaining = 0;
                    }else{
                        $vac->expired = $vac->remaining;
                        // $vac->expired = $vac->remaining - $oVacRecover->recovered_days;
                        // $vac->expired = $vac->remaining - ($oVacRecover->recovered_days - $oVacRecover->consumed_days_n);
                        $vac->remaining = $oVacRecover->recovered_days - $vac->request;
                        $vac->is_recovered = 1;
                    }
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
        if(!$isAllHistory && count($user->vacation) > 3){
            $user->vacation = $user->vacation->slice(0, ((-1 * count($user->vacation) + 3)));
        }
        return $user;
    }

    /**
     * Reporte de todas las vacaciones
     */
    public static function getVacations($lEmployees, $startYear = null){
        $config = \App\Utils\Configuration::getConfigurations();
        foreach($lEmployees as $emp){
            $emp->vacation = EmployeeVacationUtils::getEmployeeVacations($emp->id, $config->showVacation->years, null, $startYear);

            foreach($emp->vacation as $vac){
                $date_start = Carbon::parse($vac->date_start);
                $date_end = Carbon::parse($vac->date_end);
                
                $vac->date_start = EmployeeVacationUtils::months_code[$date_start->month].'-'.$date_start->format('Y');
                $vac->date_end = EmployeeVacationUtils::months_code[$date_end->month].'-'.$date_end->format('Y');

                $oVacConsumed = EmployeeVacationUtils::getVacationConsumed($emp->id, $vac->year);
                $vac_request = EmployeeVacationUtils::getVacationRequested($emp->id, $vac->year);
                $vac_programed = EmployeeVacationUtils::getProgramed($emp->id, $vac->year);
                
                if(!is_null($vac_request)){
                    $vac->request = collect($vac_request)->sum('days_effective');
                }else{
                    $vac->request = 0;
                }

                $vac->programed = 0;
                $vac->oProgramed = null;
                if(!is_null($vac_programed)){
                    if(sizeof($vac_programed) > 0){
                        $vac->programed = collect($vac_programed)->sum('days_to_consumed');
                        $vac->request = $vac->request + $vac->programed;
                        $vac->oProgramed = $vac_programed;
                    }
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

    /**
     * Calcula los dias eficaces de vacaciones
     */
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

    public static function syncVacConsumed($id){
        $config = \App\Utils\Configuration::getConfigurations();
        
        $lVacRequest = Application::where('user_id', $id)
                                    ->where('is_deleted', 0)
                                    ->where('type_incident_id', SysConst::TYPE_VACACIONES)
                                    ->get();
        $vacAlloc = VacationAllocation::where('user_id', $id)->where('is_deleted', 0)->get();
        foreach($lVacRequest as $req){
            $app_breakdowns_ids = ApplicationsBreakdown::where('application_id', $req->id_application)->pluck('id_application_breakdown')->toArray();
            $consumedDays = $vacAlloc->whereIn('application_breakdown_id', $app_breakdowns_ids)->sum('day_consumption');
            $total_days = $req->total_days - $consumedDays;
            if($total_days <= 0){
                $req->request_status_id = SysConst::APPLICATION_CONSUMIDO;
                $req->update();
            }
        }
    }

    /**
     * Obtine las aplications de un usuario, solo con estatus enviado, aprobado y consumido.
     * Regresa un array con todos los dias comprendidos de cada aplication
     */
    public static function getEmpApplicationsEA($user_id){
        $applicationsEA = Application::where('user_id', $user_id)
                                    ->whereIn('request_status_id', [SysConst::APPLICATION_ENVIADO, SysConst::APPLICATION_APROBADO, sysConst::APPLICATION_CONSUMIDO])
                                    ->where('is_deleted', 0)
                                    ->where('type_incident_id', SysConst::TYPE_VACACIONES)
                                    ->select('start_date', 'end_date', 'ldays', 'emp_comments_n')
                                    ->get();
        
        $arrDatesApplications = [];
        foreach($applicationsEA as $app){
            $lDays = json_decode($app->ldays);
            foreach($lDays as $day){
                if($day->taked){
                    $date = Carbon::parse($day->date);
                    // $arrDatesApplications[] = $date->toDateString();
                    $arrDatesApplications[] = ['name' => 'Solicitud de vacaciones', 'date' => $date->toDateString(), 'comments' => $app->emp_comments_n];
                }
            }
        }

        return $arrDatesApplications;
    }

    /**
     * Obtiene las temporadas especiales de un usuario.
     * regresa una coleccion las temporadas especiales
     */
    public static function getSpecialSeasonByEmp($user_id){
        $oUser = \DB::table('users as u')
                    ->leftJoin('ext_jobs as j', 'u.job_id', '=', 'j.id_job')
                    ->where('u.id', $user_id)
                    ->where('u.is_delete', 0)
                    ->where('u.is_active', 1)
                    ->where('j.is_deleted', 0)
                    ->select(
                        'u.*',
                        'j.department_id'
                    )
                    ->first();

        $lSpecialSeason = \DB::table('special_season as ss')
                            ->leftJoin('special_season_types as sst', 'sst.id_special_season_type', '=', 'ss.special_season_type_id')
                            ->where(function($query) use($oUser) {
                                $query->where('ss.user_id', $oUser->id)
                                        ->orWhere('ss.depto_id', $oUser->department_id)
                                        ->orWhere('ss.org_chart_job_id', $oUser->org_chart_job_id)
                                        ->orWhere('ss.company_id', $oUser->company_id);
                            })
                            ->where('ss.is_deleted', 0)
                            ->where('sst.is_deleted', 0)
                            ->select(
                                'ss.*',
                                'sst.name',
                                'sst.priority',
                                'sst.color',
                            )
                            ->get();

        return $lSpecialSeason;
    }

    /**
     * Obtiene las temporadas especiales de un usuario.
     * regresa un array con todos los dias comprendidos de cada temporada especial
     */
    public static function getEmpSpecialSeason($user_id){
        $oUser = \DB::table('users as u')
                    ->leftJoin('ext_jobs as j', 'u.job_id', '=', 'j.id_job')
                    ->where('u.id', $user_id)
                    ->where('u.is_delete', 0)
                    ->where('u.is_active', 1)
                    ->where('j.is_deleted', 0)
                    ->select(
                        'u.*',
                        'j.department_id'
                    )
                    ->first();

        $lSpecialSeason = \DB::table('special_season as ss')
                            ->leftJoin('special_season_types as sst', 'sst.id_special_season_type', '=', 'ss.special_season_type_id')
                            ->where(function($query) use($oUser) {
                                $query->where('ss.user_id', $oUser->id)
                                        ->orWhere('ss.depto_id', $oUser->department_id)
                                        ->orWhere('ss.org_chart_job_id', $oUser->org_chart_job_id)
                                        ->orWhere('ss.company_id', $oUser->company_id);
                            })
                            ->where('ss.is_deleted', 0)
                            ->where('sst.is_deleted', 0)
                            ->select(
                                'ss.*',
                                'sst.name',
                                'sst.priority',
                                'sst.color',
                            )
                            ->get();

        $lspecialSeasonUser = $lSpecialSeason->where('user_id', '!=', null);
        $lspecialSeasonDepto = $lSpecialSeason->where('depto_id', '!=', null);
        $lspecialSeasonArea = $lSpecialSeason->where('org_chart_job_id', '!=', null);
        $lspecialSeasonCompany = $lSpecialSeason->where('company_id', '!=', null);

        $arrSpecialSeason = [];
        foreach ($lspecialSeasonUser as $oSeasonUser) {
            $date = Carbon::parse($oSeasonUser->start_date);
            array_push($arrSpecialSeason, ['date' => $date->toDateString(), 'name' => $oSeasonUser->name, 'color' => $oSeasonUser->color]);
            $diff = Carbon::parse($oSeasonUser->start_date)->diffInDays(Carbon::parse($oSeasonUser->end_date));
            for($i = 0; $i < $diff; $i++){
                array_push($arrSpecialSeason, ['date' => $date->addDay()->toDateString(), 'name' => $oSeasonUser->name, 'color' => $oSeasonUser->color]);
            }
        }


        foreach ($lspecialSeasonDepto as $oSeasonDepto) {
            $date = Carbon::parse($oSeasonDepto->start_date);
            $result = array_filter($arrSpecialSeason, function($oSeason) use($date) {
                return $oSeason['date'] == $date->toDateString();
            });

            sizeof($result) == 0 ? array_push($arrSpecialSeason, ['date' => $date->toDateString(), 'name' => $oSeasonDepto->name, 'color' => $oSeasonDepto->color]) : '';

            $diff = Carbon::parse($oSeasonDepto->start_date)->diffInDays(Carbon::parse($oSeasonDepto->end_date));
            for($i = 0; $i < $diff; $i++){
                $date->addDay();
                $result = array_filter($arrSpecialSeason, function($oSeason) use($date) {
                    return $oSeason['date'] == $date->toDateString();
                });
                sizeof($result) == 0 ? array_push($arrSpecialSeason, ['date' => $date->toDateString(), 'name' => $oSeasonDepto->name, 'color' => $oSeasonDepto->color]) : '';
            }
        }

        foreach ($lspecialSeasonArea as $oSeasonArea) {
            $date = Carbon::parse($oSeasonArea->start_date);
            $result = array_filter($arrSpecialSeason, function($oSeason) use($date) {
                return $oSeason['date'] == $date->toDateString();
            });

            sizeof($result) == 0 ? array_push($arrSpecialSeason, ['date' => $date->toDateString(), 'name' => $oSeasonArea->name, 'color' => $oSeasonArea->color]) : '';

            $diff = Carbon::parse($oSeasonArea->start_date)->diffInDays(Carbon::parse($oSeasonArea->end_date));
            for($i = 0; $i < $diff; $i++){
                $date->addDay();
                $result = array_filter($arrSpecialSeason, function($oSeason) use($date) {
                    return $oSeason['date'] == $date->toDateString();
                });
                sizeof($result) == 0 ? array_push($arrSpecialSeason, ['date' => $date->toDateString(), 'name' => $oSeasonArea->name, 'color' => $oSeasonArea->color]) : '';
            }
        }

        foreach ($lspecialSeasonCompany as $oSeasonCompany) {
            $date = Carbon::parse($oSeasonCompany->start_date);
            $result = array_filter($arrSpecialSeason, function($oSeason) use($date) {
                return $oSeason['date'] == $date->toDateString();
            });

            sizeof($result) == 0 ? array_push($arrSpecialSeason, ['date' => $date->toDateString(), 'name' => $oSeasonCompany->name, 'color' => $oSeasonCompany->color]) : '';

            $diff = Carbon::parse($oSeasonCompany->start_date)->diffInDays(Carbon::parse($oSeasonCompany->end_date));
            for($i = 0; $i < $diff; $i++){
                $date->addDay();
                $result = array_filter($arrSpecialSeason, function($oSeason) use($date) {
                    return $oSeason['date'] == $date->toDateString();
                });
                sizeof($result) == 0 ? array_push($arrSpecialSeason, ['date' => $date->toDateString(), 'name' => $oSeasonCompany->name, 'color' => $oSeasonCompany->color]) : '';
            }
        }
        
        return $arrSpecialSeason;
    }

    /**
     * Metodo para obtener los datos necesarios para la vista mis vacaciones
     */
    public static function getEmployeeDataForMyVacation($employee_id){
        $user = EmployeeVacationUtils::getEmployeeVacationsData($employee_id);
            
        $from = Carbon::parse($user->benefits_date);
        $to = Carbon::today()->locale('es');

        $human = $to->diffForHumans($from, true, false, 6);

        $user->antiquity = $human;

        $user->applications = EmployeeVacationUtils::getTakedDays($user);

        return $user;
    }

    /**
     * Metodo para obtener las application de categoria especial asignadas al org_chart del usuario
     */
    public static function getApplicationsTypeSpecial($org_chart_id, $lStatus, $year){
        $lSpecial_vs_orgChart = \DB::table('cat_special_vs_org_chart as so')
                                    ->leftJoin('cat_special_type as st', 'st.id_special_type', '=', 'so.cat_special_id')
                                    ->where('so.revisor_id', $org_chart_id)
                                    ->where('so.is_deleted', 0)
                                    ->select(
                                        'so.*',
                                        'st.name',
                                        'st.situation'
                                    )
                                    ->get();

        $oSpecialVsOrg = clone $lSpecial_vs_orgChart;
        $lSituation = collect($oSpecialVsOrg)->unique('situation')->pluck('situation');

        $arrUsers = [];
        $arrOrgChart = [];
        $arrCompanies = [];
        foreach($lSpecial_vs_orgChart as $so){
            if(!is_null($so->user_id_n)){
                array_push($arrUsers, $so->user_id_n);
            }

            if(!is_null($so->org_chart_job_id_n)){
                array_push($arrOrgChart, $so->org_chart_job_id_n);
            }

            if(!is_null($so->company_id_n)){
                array_push($arrCompanies, $so->company_id_n);
            }
        }

        $lUsers = \DB::table('users')
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->whereIn('id', $arrUsers)
                    ->orWhere(function($query) use($arrOrgChart){
                        $query->whereIn('org_chart_job_id', $arrOrgChart);
                    })
                    ->orWhere(function($query) use($arrCompanies){
                        $query->whereIn('company_id', $arrCompanies);
                    })
                    ->get();
                    
        $arrUsers = clone $lUsers;
        $arrUsers = $arrUsers->pluck('id');

        $config = \App\Utils\Configuration::getConfigurations();
        $conflSituation = $config->lSituation;

        $lApplications = \DB::table('applications as ap')
                            ->leftJoin('applications_vs_types as at', 'at.application_id', '=', 'ap.id_application')
                            ->leftJoin('users as u', 'u.id', '=', 'ap.user_apr_rej_id')
                            ->leftJoin('sys_applications_sts as as', 'as.id_applications_st', '=', 'ap.request_status_id')
                            ->whereIn('ap.user_id', $arrUsers)
                            ->where('ap.type_incident_id', SysConst::TYPE_VACACIONES)
                            ->where('ap.is_deleted', 0);
        
        if(!is_null($year)){
            $lApplications = $lApplications->whereYear('ap.updated_at', $year);
        }

        $lApplications = $lApplications->whereIn('ap.request_status_id', $lStatus)
                            ->where(function($query) use($conflSituation, $lSituation){
                                foreach($lSituation as $s){
                                    $index = array_search($s, array_column($conflSituation, 'id'));
                                    $query = $query->orWhere($conflSituation[$index]->type, 1);
                                }
                            })
                            ->select(
                                'ap.*',
                                'at.*',
                                'as.applications_st_name',
                                'as.applications_st_code',
                                'u.full_name_ui as user_apr_rej_name',
                            );

        $lApplications = $lApplications->get();

        foreach($lApplications as $app){
            $app->is_normal = 1;
        }

        foreach($lUsers as $u){
            $u->employee = $u->full_name_ui;
            $arr = [];
            foreach($lApplications->where('user_id', $u->id) as $app){
                $arr[] = $app;
            }
            $u->applications = $arr; 
        }

        return $lUsers;
    }

    /**
     * Metodo que obtiene las applications especiales de los nodos superiores del organigrama
     */
    public static function getFatherApplicationsTypeSpecial($org_chart_id, $lStatus, $year){
        $lAllFatherBoss = orgChartUtils::getAllFatherBossOrgChartJob($org_chart_id);
        $lAllMyChilds = orgChartUtils::getAllChildsOrgChartJob($org_chart_id);
        $lMyUsers = \DB::table('users')
                        ->where('is_active', 1)
                        ->where('is_delete', 0)
                        ->whereIn('org_chart_job_id', $lAllMyChilds)
                        ->get();

        $lSpecial_vs_orgChart = \DB::table('cat_special_vs_org_chart as so')
                                    ->leftJoin('cat_special_type as st', 'st.id_special_type', '=', 'so.cat_special_id')
                                    ->whereIn('so.revisor_id', $lAllFatherBoss)
                                    ->where(function($query) use($lMyUsers, $lAllMyChilds){
                                        $query->whereIn('so.user_id_n', $lMyUsers)
                                            ->orWhereIn('so.org_chart_job_id_n', $lAllMyChilds);
                                    })
                                    ->where('so.is_deleted', 0)
                                    ->select(
                                        'so.*',
                                        'st.name',
                                        'st.situation'
                                    )
                                    ->get();


        /**---------------------------------------------------------------------------------------- */

        $lSpecial_vs_orgChart = \DB::table('cat_special_vs_org_chart as so')
                                    ->leftJoin('cat_special_type as st', 'st.id_special_type', '=', 'so.cat_special_id')
                                    ->whereIn('so.revisor_id', $lAllFatherBoss)
                                    ->where('so.is_deleted', 0)
                                    ->select(
                                        'so.*',
                                        'st.name',
                                        'st.situation'
                                    )
                                    ->get();

        $oSpecialVsOrg = clone $lSpecial_vs_orgChart;
        $lSituation = collect($oSpecialVsOrg)->unique('situation')->pluck('situation');

        $arrUsers = [];
        $arrOrgChart = [];
        $arrCompanies = [];
        foreach($lSpecial_vs_orgChart as $so){
            if(!is_null($so->user_id_n)){
                array_push($arrUsers, $so->user_id_n);
            }

            if(!is_null($so->org_chart_job_id_n)){
                array_push($arrOrgChart, $so->org_chart_job_id_n);
            }

            if(!is_null($so->company_id_n)){
                array_push($arrCompanies, $so->company_id_n);
            }
        }

        $lFathersUsers = \DB::table('users')
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->whereIn('id', $arrUsers)
                    ->orWhere(function($query) use($arrOrgChart){
                        $query->whereIn('org_chart_job_id', $arrOrgChart);
                    })
                    ->orWhere(function($query) use($arrCompanies){
                        $query->whereIn('company_id', $arrCompanies);
                    })
                    ->get();

        $arrUsers = clone $lFathersUsers;
        $arrUsers = $arrUsers->pluck('id');

        $lMyChilds = orgChartUtils::getAllChildsOrgChartJob($org_chart_id);
        $lMyDirectChilds = orgChartUtils::getDirectChildsOrgChartJob($org_chart_id);

        $lMyUsers = \DB::table('users')
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->whereIn('org_chart_job_id', $lMyChilds)
                    ->whereNotIn('org_chart_job_id', $lMyDirectChilds)
                    ->whereIn('id', $arrUsers)
                    ->get();

        $arrMyUser = clone $lMyUsers;
        $arrMyUser = $arrMyUser->pluck('id');

        $config = \App\Utils\Configuration::getConfigurations();
        $conflSituation = $config->lSituation;

        $lApplications = \DB::table('applications as ap')
                            ->leftJoin('applications_vs_types as at', 'at.application_id', '=', 'ap.id_application')
                            ->leftJoin('users as u', 'u.id', '=', 'ap.user_apr_rej_id')
                            ->leftJoin('sys_applications_sts as as', 'as.id_applications_st', '=', 'ap.request_status_id')
                            ->whereIn('ap.user_id', $arrMyUser)
                            ->where('ap.type_incident_id', SysConst::TYPE_VACACIONES)
                            ->where('ap.is_deleted', 0)
                            ->whereYear('ap.updated_at', $year)
                            ->whereIn('ap.request_status_id', $lStatus)
                            ->where(function($query) use($conflSituation, $lSituation){
                                foreach($lSituation as $s){
                                    $index = array_search($s, array_column($conflSituation, 'id'));
                                    $query = $query->orWhere($conflSituation[$index]->type, 1);
                                }
                            })
                            ->select(
                                'ap.*',
                                'at.*',
                                'as.applications_st_name',
                                'as.applications_st_code',
                                'u.full_name_ui as user_apr_rej_name',
                            );

        $lApplications = $lApplications->get();

        foreach($lMyUsers as $u){
            $u->employee = $u->full_name_ui;
            $u->applications = $lApplications->where('user_id', $u->id);
        }

        return $lMyUsers;
    }

    public static function getEmployeeTempSpecial($org_chart_job_id, $user_id, $job_id){
        $lTemp_special = \DB::table('special_season as ss')
                            ->leftJoin('special_season_types as sst', 'sst.id_special_season_type', '=', 'ss.special_season_type_id')
                            ->where(function($query) use($org_chart_job_id, $user_id, $job_id){
                                $query->where('org_chart_job_id', $org_chart_job_id)->orWhere('user_id', $user_id)->orWhere('job_id', $job_id);
                            })
                            ->where('ss.is_deleted', 0)
                            ->select(
                                'ss.start_date',
                                'ss.end_date',
                                'sst.priority',
                                'sst.name',
                                'sst.color',
                            )
                            ->get();

        foreach ($lTemp_special as $temp) {
            $lTemp = [];
            $oDate = Carbon::parse($temp->start_date);
            for($i = 0; $i < (Carbon::parse($temp->start_date)->diff(Carbon::parse($temp->end_date))->days + 1); $i++){
                array_push($lTemp, $oDate->toDateString());
                $oDate->addDay();
            }
            $temp->lDates = $lTemp;
        }

        return $lTemp_special;
    }

    public static function getLastYearNumberIncident($user_id){
        $lastAlloc = \DB::table('vacation_allocations')
                        ->where('user_id', $user_id)
                        ->where('is_deleted', 0)
                        ->max('id_anniversary');

        $lastProg = \DB::table('programed_aux')
                        ->where('employee_id', $user_id)
                        ->where('is_deleted', 0)
                        ->max('year');

        $lastAppBreak = \DB::table('applications as ap')
                            ->leftJoin('applications_breakdowns as apb', 'apb.application_id', '=', 'ap.id_application')
                            ->where('ap.user_id', $user_id)
                            ->whereIn('ap.request_status_id', [
                                                                SysConst::APPLICATION_ENVIADO,
                                                                SysConst::APPLICATION_APROBADO,
                                                                SysConst::APPLICATION_CONSUMIDO,
                                                            ])
                            ->where('ap.is_deleted', 0)
                            ->where('ap.type_incident_id', SysConst::TYPE_VACACIONES)
                            ->max('apb.application_year');

        $lastIncidentYear = $lastAlloc;
        if ($lastProg > $lastIncidentYear) {
            $lastIncidentYear = $lastProg;
        }
        if ($lastAppBreak > $lastIncidentYear) {
            $lastIncidentYear = $lastAppBreak;
        }

        $lastAniversary = \DB::table('vacation_users')
                        ->where('user_id', $user_id)
                        ->where('date_end', '<=', Carbon::now()->toDateString())
                        ->max('year');

        $customYear = $lastAniversary < $lastIncidentYear ? $lastIncidentYear - $lastAniversary : null;

        return $customYear;
    }

    public static function getEmployeeEvents($employee_id){
        try {
            $lGroups = \DB::table('groups_assigns as ga')
                            ->join('groups as g', 'g.id_group', '=', 'ga.group_id_n')
                            ->where('ga.user_id_n', $employee_id)
                            ->where('g.is_deleted', 0)
                            ->get()
                            ->pluck('id_group')
                            ->toArray();

            $lEventsByGroups = \DB::table('events_assigns as ea')
                                    ->join('cat_events as e', 'e.id_event', '=', 'ea.event_id')
                                    ->whereIn('group_id_n', $lGroups)
                                    ->where('e.is_deleted', 0)
                                    ->where('ea.is_deleted', 0)
                                    ->get();

            $lEventsByEmployee = \DB::table('events_assigns as ea')
                                    ->join('cat_events as e', 'e.id_event', '=', 'ea.event_id')
                                    ->where('user_id_n', $employee_id)
                                    ->where('e.is_deleted', 0)
                                    ->where('ea.is_deleted', 0)
                                    ->get();

            $lEvents = $lEventsByGroups->merge($lEventsByEmployee);
            
            foreach ($lEvents as $event) {
                $lDays = json_decode($event->ldays);
                foreach ($lDays as $day) {
                    if($day->taked){
                        $event->lDates[] = $day->date;
                    }
                }
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            return [];
        }

        return $lEvents;
    }
}