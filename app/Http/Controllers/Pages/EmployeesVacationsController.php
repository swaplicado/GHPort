<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\orgChartUtils;
use App\Models\Adm\OrgChartJob;
use Carbon\Carbon;
use Carbon\Translator;
use App\Constants\SysConst;

class EmployeesVacationsController extends Controller
{
    public $months_code = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    public function employeesDirectIndex(){
        $config = \App\Utils\Configuration::getConfigurations();
        $lEmployees = $this->getDirectEmployees(\Auth::user()->org_chart_job_id);

        return view('emp_vacations.my_emp_vacations')->with('lEmployees', $lEmployees);
    }

    public function allEmployeesIndex(){
        $config = \App\Utils\Configuration::getConfigurations();

        $lEmployees = $this->getAlllEmployees(\Auth::user()->org_chart_job_id, $config);

        return view('emp_vacations.all_emp_vacations')->with('lEmployees', $lEmployees);
    }

    public function getDirectEmployees($orgJobId){
        $config = \App\Utils\Configuration::getConfigurations();
        $arrOrgJobs = orgChartUtils::getDirectChildsOrgChartJob($orgJobId);
        $lEmployees = $this->getlEmployees($arrOrgJobs);

        foreach($lEmployees as $emp){
            $from = Carbon::parse($emp->last_admission_date);
            $to = Carbon::today()->locale('es');
            $human = $to->diffForHumans($from, true, false, 6);
            $emp->antiquity = $human;
            $orgJob = orgChartUtils::getDirectChildsOrgChartJob($emp->org_chart_job_id);

            if(count($orgJob) > 0){
                $emp->is_head_user = true;
            }else{
                $emp->is_head_user = false;
            }

            $emp->vacation = $this->getEmployeeVacations($emp->id, $config->showVacation->years);
            
            foreach($emp->vacation as $vac){
                $date_start = Carbon::parse($vac->date_start);
                $date_end = Carbon::parse($vac->date_end);
                
                $vac->date_start = $this->months_code[$date_start->month].'-'.$date_start->format('Y');
                $vac->date_end = $this->months_code[$date_end->month].'-'.$date_end->format('Y');

                $oVacConsumed = $this->getVacationConsumed($emp->id, $vac->year);
                $vac_request = $this->getVacationRequested($emp->id, $vac->year);
                
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

    public function getAlllEmployees($orgJobId, $config){
        $arrOrgJobs = orgChartUtils::getDirectChildsOrgChartJob($orgJobId);
        $lEmployees = $this->getlEmployees($arrOrgJobs);

        foreach($lEmployees as $emp){
            $from = Carbon::parse($emp->last_admission_date);
            $to = Carbon::today()->locale('es');
            $human = $to->diffForHumans($from, true, false, 6);
            $emp->antiquity = $human;
            $orgJob = orgChartUtils::getDirectChildsOrgChartJob($emp->org_chart_job_id);

            if(count($orgJob) > 0){
                $emp->is_head_user = true;
                $emp->subEmployees = $this->getAlllEmployees($emp->org_chart_job_id, $config);
            }else{
                $emp->is_head_user = false;
                $emp->subEmployees = [];
            }

            $emp->vacation = $this->getEmployeeVacations($emp->id, $config->showVacation->years);
            
            foreach($emp->vacation as $vac){
                $date_start = Carbon::parse($vac->date_start);
                $date_end = Carbon::parse($vac->date_end);
                
                $vac->date_start = $this->months_code[$date_start->month].'-'.$date_start->format('Y');
                $vac->date_end = $this->months_code[$date_end->month].'-'.$date_end->format('Y');

                $oVacConsumed = $this->getVacationConsumed($emp->id, $vac->year);
                $vac_request = $this->getVacationRequested($emp->id, $vac->year);
                
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

    public function getlEmployees($arrOrgJobs){
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

    public function getEmployeeVacations($id, $years){
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

    public function getVacationConsumed($id, $year){
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

    public function getVacationRequested($id, $year){
        $oRequested = \DB::table('applications as a')
                        ->join('applications_breakdowns as ab', 'ab.application_id', '=', 'a.id_application')
                        ->where('a.user_id', $id)
                        ->whereIn('a.request_status_id', [SysConst::APPLICATION_ENVIADO,SysConst::APPLICATION_APROBADO])
                        ->where('a.is_deleted', 0)
                        ->where('ab.application_year', $year)
                        ->select('ab.days_effective')
                        ->get();

        return $oRequested;
    }
}
