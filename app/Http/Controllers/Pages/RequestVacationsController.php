<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\User;
use App\Utils\EmployeeVacationUtils;

class RequestVacationsController extends Controller
{
    public $months_code = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    public function index(){
        $config = \App\Utils\Configuration::getConfigurations();

        // $user = \DB::table('users')->where('id', \Auth::user()->id)->first();
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
                    ->where('u.id', 2)
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
                    ->first();

        $from = Carbon::parse($user->last_admission_date);
        $to = Carbon::today()->locale('es');

        $human = $to->diffForHumans($from, true, false, 6);

        $user->antiquity = $human;

        $user->vacation = EmployeeVacationUtils::getEmployeeVacations(2, $config->showVacation->years);

        foreach($user->vacation as $vac){
            $date_start = Carbon::parse($vac->date_start);
            $date_end = Carbon::parse($vac->date_end);
            
            $vac->date_start = $this->months_code[$date_start->month].'-'.$date_start->format('Y');
            $vac->date_end = $this->months_code[$date_end->month].'-'.$date_end->format('Y');

            $oVacConsumed = EmployeeVacationUtils::getVacationConsumed(2, $vac->year);
            $vac_request = EmployeeVacationUtils::getVacationRequested(2, $vac->year);

            if(!is_null($vac_request)){
                $vac->request = collect($vac_request)->sum('days_effective');
                $vac->oRequest = $vac_request;
            }else{
                $vac->request = 0;
                $vac->oRequest = null;
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
// dd($user);
        return view('emp_vacations.my_vacations')->with('user', $user);
    }
}
