<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\orgChartUtils;
use App\Models\Adm\OrgChartJob;
use Carbon\Carbon;
use Carbon\Translator;

class EmployeesVacationsController extends Controller
{
    public $months_code = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    public function index(){
        $config = \App\Utils\Configuration::getConfigurations();
        $arrOrgJobs = orgChartUtils::getDirectChildsOrgChartJob(\Auth::user()->org_chart_job_id);
        // $arrOrgJobs = orgChartUtils::getDirectChildsOrgChartJob(30);
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

            $emp->vacation = \DB::table('vacation_users as vu')
                                ->where('vu.is_deleted', 0)
                                ->where('vu.user_id', $emp->id)
                                ->where('vu.date_end', '<', Carbon::now()->addYears($config->showVacation->years))
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
                                
            foreach($emp->vacation as $vac){
                $date_start = Carbon::parse($vac->date_start);
                $date_end = Carbon::parse($vac->date_end);
                
                $vac->date_start = $this->months_code[$date_start->month].'-'.$date_start->format('Y');
                $vac->date_end = $this->months_code[$date_end->month].'-'.$date_end->format('Y');

                $gozadas_byApplication = \DB::table('vacation_allocations as va')
                                            ->Join('applications_breakdowns as ab', 'ab.id_application_breakdown', '=', 'va.application_breakdown_id')
                                            ->where('va.user_id', $emp->id)
                                            ->where('va.is_deleted', 0)
                                            ->where('ab.application_year',  $vac->year)
                                            ->select('va.*')
                                            ->get();

                $gozadas_byAnniversary = \DB::table('vacation_allocations as va')
                                            ->where('va.user_id', $emp->id)
                                            ->where('va.anniversary_count', $vac->year)
                                            ->where('va.is_deleted', 0)
                                            ->get();

                $gozadas = collect($gozadas_byApplication)->merge(collect($gozadas_byAnniversary));

                if(!is_null($gozadas)){
                    $vac->gozadas = $gozadas;
                    $vac->num_vac_taken = collect($gozadas)->sum('day_consumption');
                    $vac->remaining = $vac->vacation_days - collect($gozadas)->sum('day_consumption');
                }else{
                    $vac->gozadas = null;
                    $vac->num_vac_taken = 0;
                    $vac->remaining = $vac->vacation_days;
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
            }else{
                $emp->tot_vacation_days = 0;
                $emp->tot_vacation_taken = 0;
                $emp->tot_vacation_remaining = 0;
                $emp->tot_vacation_expired = 0;
            }
        }

        return view('employees_vacations')->with('lEmployees', $lEmployees);
    }
}
