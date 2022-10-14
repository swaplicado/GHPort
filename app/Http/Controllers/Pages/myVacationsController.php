<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\User;
use App\Utils\EmployeeVacationUtils;
use App\Models\Vacations\Application;
use App\Models\Vacations\ApplicationsBreakdown;
class myVacationsController extends Controller
{
    public $months_code = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    public function index(){
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
                    ->where('u.id', \Auth::user()->id)
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

        $from = Carbon::parse($user->last_admission_date);
        $to = Carbon::today()->locale('es');

        $human = $to->diffForHumans($from, true, false, 6);

        $user->antiquity = $human;

        $user->vacation = EmployeeVacationUtils::getEmployeeVacations(\Auth::user()->id, $config->showVacation->years);
        $user->actual_vac_days = 0;
        $user->prox_vac_days = 0;
        $user->prop_vac_days = 0;

        foreach($user->vacation as $vac){
            $date_start = Carbon::parse($vac->date_start);
            $date_end = Carbon::parse($vac->date_end);
            
            $vac->date_start = $this->months_code[$date_start->month].'-'.$date_start->format('Y');
            $vac->date_end = $this->months_code[$date_end->month].'-'.$date_end->format('Y');

            $oVacConsumed = EmployeeVacationUtils::getVacationConsumed(\Auth::user()->id, $vac->year);
            $vac_request = EmployeeVacationUtils::getVacationRequested(\Auth::user()->id, $vac->year);

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

        $now = Carbon::now();
        $initialCalendarDate = $now->subDays(30)->toDateString();

        $holidays = \DB::table('holidays')
                        ->where('fecha', '>', Carbon::now()->toDateString())
                        ->where('is_deleted', 0)
                        ->pluck('fecha');

        $user->applications = EmployeeVacationUtils::getApplications(\Auth::user()->id, Carbon::now()->year);
// dd($user);
        return view('emp_vacations.my_vacations')->with('user', $user)
                                                ->with('initialCalendarDate', $initialCalendarDate)
                                                ->with('lHolidays', $holidays)
                                                ->with('year', Carbon::now()->year);
    }

    public function setRequestVac(Request $request){
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $comments = $request->comments;
        $takedDays = $request->takedDays;
        $lDays = $request->lDays;
        
        try {
            $user = $this->getUserVacationsData();

            if($user->tot_vacation_remaining < $takedDays){
                return json_encode(['success' => false, 'message' => 'No cuentas con días disponibles', 'icon' => 'warning']);
            }

            $vacations = collect($user->vacation)->sortBy('year');

            \DB::beginTransaction();

            $application = new Application();
            $application->start_date = $startDate;
            $application->end_date = $endDate;
            $application->total_days = $takedDays;
            $application->user_id = \Auth::user()->id;
            $application->request_status_id = 1;
            $application->type_incident_id = 1;
            $application->emp_comments_n = $comments;
            $application->is_deleted = false;
            $application->save();

            foreach($vacations as $vac){
                if($takedDays > 0){
                    if($vac->remaining > 0){
                        $antTakedDays = $takedDays;
                        $takedDays = $takedDays - $vac->remaining;
                        $appBreakdown = new ApplicationsBreakdown();
                        $appBreakdown->application_id = $application->id_application;
                        $appBreakdown->days_effective = $takedDays > 0 ? ($antTakedDays - $takedDays) : $antTakedDays;
                        $appBreakdown->application_year = $vac->year;
                        $appBreakdown->admition_count = 1;
                        $appBreakdown->save();
                    }
                }else{
                    break;
                }
            }
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al guardar la solicitud', 'icon' => 'error']);
        }

        $user = $this->getUserVacationsData();

        return json_encode(['success' => true, 'message' => 'Solicitud guardada con éxito', 'oUser' => $user]);
    }

    public function updateRequestVac(Request $request){
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $comments = $request->comments;
        $takedDays = $request->takedDays;
        $lDays = $request->lDays;
        
        try {
            $application = Application::findOrFail($request->id_application);

            if($application->request_status_id != 1){
                return json_encode(['success' => false, 'message' => 'Solo se pueden eliminar solicitudes con el estatus CREADO', 'icon' => 'warning']);
            }

            \DB::beginTransaction();
            $application->is_deleted = 1;
            $application->update();
            \DB::commit();

            $user = $this->getUserVacationsData();
    
            if($user->tot_vacation_remaining < $takedDays){
                return json_encode(['success' => false, 'message' => 'No cuentas con días disponibles', 'icon' => 'warning']);
            }
    
            $vacations = collect($user->vacation)->sortBy('year');

            \DB::beginTransaction();

            $appBreakDowns = ApplicationsBreakdown::where('application_id', $request->id_application)->get();
            foreach($appBreakDowns as $ab){
                $ab->delete();
            }

            $application->start_date = $startDate;
            $application->end_date = $endDate;
            $application->total_days = $takedDays;
            $application->emp_comments_n = $comments;
            $application->is_deleted = 0;
            $application->update();

            foreach($vacations as $vac){
                if($takedDays > 0){
                    if($vac->remaining > 0){
                        $antTakedDays = $takedDays;
                        $takedDays = $takedDays - $vac->remaining;
                        $appBreakdown = new ApplicationsBreakdown();
                        $appBreakdown->application_id = $application->id_application;
                        $appBreakdown->days_effective = $takedDays > 0 ? ($antTakedDays - $takedDays) : $antTakedDays;
                        $appBreakdown->application_year = $vac->year;
                        $appBreakdown->admition_count = 1;
                        $appBreakdown->save();
                    }
                }else{
                    break;
                }
            }

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al editar el registro', 'icon' => 'error']);
        }
        $user = $this->getUserVacationsData();
        return json_encode(['success' => true, 'message' => 'Registro editado con éxito', 'icon' => 'success', 'oUser' => $user]);
    }

    public function getUserVacationsData(){
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
                    ->where('u.id', \Auth::user()->id)
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

        $user->vacation = EmployeeVacationUtils::getEmployeeVacations(\Auth::user()->id, $config->showVacation->years);
        $user->actual_vac_days = 0;
        $user->prox_vac_days = 0;

        foreach($user->vacation as $vac){
            $date_start = Carbon::parse($vac->date_start);
            $date_end = Carbon::parse($vac->date_end);

            $oVacConsumed = EmployeeVacationUtils::getVacationConsumed(\Auth::user()->id, $vac->year);
            $vac_request = EmployeeVacationUtils::getVacationRequested(\Auth::user()->id, $vac->year);

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
        $user->applications = EmployeeVacationUtils::getApplications(\Auth::user()->id, Carbon::now()->year);
        return $user;
    }

    public function filterYear(Request $request){
        try {
            $applications = EmployeeVacationUtils::getApplications(\Auth::user()->id, $request->year);
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al cargar los registros', 'icon' => 'error']);    
        }

        return json_encode(['success' => true, 'applications' => $applications]);
    }

    public function deleteRequestVac(Request $request){
        try {
            $application = Application::findOrFail($request->id_application);

            if($application->request_status_id != 1){
                return json_encode(['success' => false, 'message' => 'Solo se pueden eliminar solicitudes con el estatus CREADO', 'icon' => 'warning']);
            }

            \DB::beginTransaction();
            
            $application->is_deleted = 1;
            $application->update();

            $user = $this->getUserVacationsData();
            $user->applications = EmployeeVacationUtils::getApplications(\Auth::user()->id, $request->year);

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al eliminar el registro', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'message' => 'Registro eliminado con éxito', 'icon' => 'success', 'oUser' => $user]);
    }
}
