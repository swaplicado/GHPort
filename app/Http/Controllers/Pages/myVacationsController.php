<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\User;
use App\Mail\requestVacationMail;
use App\Utils\EmployeeVacationUtils;
use App\Models\Vacations\Application;
use App\Models\Vacations\ApplicationsBreakdown;
use App\Models\Vacations\ApplicationLog;
use App\Models\Vacations\MailLog;
use App\Constants\SysConst;
use App\Models\Adm\OrgChartJob;
use App\Utils\orgChartUtils;
use Spatie\Async\Pool;
class myVacationsController extends Controller
{
    public $months_code = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    public function index(){
        $config = \App\Utils\Configuration::getConfigurations();

        $user = EmployeeVacationUtils::getEmployeeDataForMyVacation(\Auth::user()->id);
        $now = Carbon::now();
        $initialCalendarDate = $now->addDays(1)->toDateString();

        $holidays = \DB::table('holidays')
                        ->where('fecha', '>', Carbon::now()->subDays(30)->toDateString())
                        ->where('is_deleted', 0)
                        ->pluck('fecha');

        $constants = [
            'SEMANA' => SysConst::SEMANA,
            'QUINCENA' => SysConst::QUINCENA,
            'APPLICATION_CREADO' => SysConst::APPLICATION_CREADO,
            'APPLICATION_ENVIADO' => SysConst::APPLICATION_ENVIADO,
            'APPLICATION_APROBADO' => SysConst::APPLICATION_APROBADO,
            'APPLICATION_RECHAZADO' => SysConst::APPLICATION_RECHAZADO
        ];

        return view('emp_vacations.my_vacations')->with('user', $user)
                                                ->with('initialCalendarDate', $initialCalendarDate)
                                                ->with('lHolidays', $holidays)
                                                ->with('year', Carbon::now()->year)
                                                ->with('constants', $constants)
                                                ->with('config', $config);
    }

    public function setRequestVac(Request $request){
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $comments = $request->comments;
        $takedDays = $request->takedDays;
        $returnDate = $request->returnDate;
        $tot_calendar_days = $request->tot_calendar_days;
        // $lDays = $request->lDays;
        $take_holidays = $request->take_holidays;
        $take_rest_days = $request->take_rest_days;
        $employee_id = $request->employee_id;
        
        try {

            $arrApplicationsEA = EmployeeVacationUtils::getEmpApplicationsEA($employee_id);

            foreach($arrApplicationsEA as $arr){
                $isBetWeen = Carbon::parse($arr)->between($startDate, $endDate);
                if($isBetWeen){
                    return json_encode(['success' => false, 'message' => 'Ya existe una solicitud de vacaciones para la fecha: '.Carbon::parse($arr)->locale('es-ES')->isoFormat('ddd D-MMM-YYYY'), 'icon' => 'warning']);
                }
            }

            // $user = $this->getUserVacationsData();
            $user = EmployeeVacationUtils::getEmployeeVacationsData($employee_id);

            foreach($user->applications as $ap){
                if($ap->request_status_id == 1){
                    return json_encode(['success' => false, 'message' => 'No puede crear otra solicitud de vacaciones si tiene solicitudes creadas pendientes de enviar', 'icon' => 'warning']);
                }
            }

            if($user->tot_vacation_remaining < $takedDays){
                return json_encode(['success' => false, 'message' => 'No cuentas con d??as disponibles', 'icon' => 'warning']);
            }

            $vacations = collect($user->vacation)->sortBy('year');

            \DB::beginTransaction();

            $application = new Application();
            $application->start_date = $startDate;
            $application->end_date = $endDate;
            $application->take_holidays = $take_holidays;
            $application->take_rest_days = $take_rest_days;
            $application->total_days = $takedDays;
            $application->tot_calendar_days = $tot_calendar_days;
            $application->return_date = $returnDate;
            $application->user_id = $employee_id;
            $application->request_status_id = SysConst::APPLICATION_CREADO;
            $application->type_incident_id = SysConst::TYPE_VACACIONES;
            $application->emp_comments_n = $comments;
            $application->is_deleted = false;
            $application->save();

            foreach($vacations as $vac){
                if($takedDays > 0){
                    $count = 0;
                    if($vac->remaining > 0){
                        for($i=0; $i<$vac->remaining; $i++){
                            $takedDays--;
                            $count++;
                            if($takedDays == 0 || $takedDays < 0){
                                break;
                            }
                        }
                        $vac->remaining = $vac->remaining - $count;
                        $appBreakdown = new ApplicationsBreakdown();
                        $appBreakdown->application_id = $application->id_application;
                        $appBreakdown->days_effective = $count;
                        $appBreakdown->application_year = $vac->year;
                        $appBreakdown->admition_count = 1;
                        $appBreakdown->save();
                    }
                }else{
                    break;
                }
            }

            $application_log = new ApplicationLog();
            $application_log->application_id = $application->id_application;
            $application_log->application_status_id = $application->request_status_id;
            $application_log->created_by = \Auth::user()->id;
            $application_log->updated_by = \Auth::user()->id;
            $application_log->save();

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al guardar la solicitud', 'icon' => 'error']);
        }

        // $user = $this->getUserVacationsData();
        $user = EmployeeVacationUtils::getEmployeeVacationsData($employee_id);
        $user->applications = EmployeeVacationUtils::getTakedDays($user);

        return json_encode(['success' => true, 'message' => 'Solicitud guardada con ??xito', 'oUser' => $user]);
    }

    public function updateRequestVac(Request $request){
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $comments = $request->comments;
        $takedDays = $request->takedDays;
        $returnDate = $request->returnDate;
        $tot_calendar_days = $request->tot_calendar_days;
        // $lDays = $request->lDays;
        $take_holidays = $request->take_holidays;
        $take_rest_days = $request->take_rest_days;
        $employee_id = $request->employee_id;

        try {
            $arrApplicationsEA = EmployeeVacationUtils::getEmpApplicationsEA($employee_id);

            foreach($arrApplicationsEA as $arr){
                $isBetWeen = Carbon::parse($arr)->between($startDate, $endDate);
                if($isBetWeen){
                    return json_encode(['success' => false, 'message' => 'Ya existe una solicitud de vacaciones para la fecha: '.Carbon::parse($arr)->locale('es-ES')->isoFormat('ddd D-MMM-YYYY'), 'icon' => 'warning']);
                }
            }

            $application = Application::findOrFail($request->id_application);

            if($application->request_status_id != SysConst::APPLICATION_CREADO){
                return json_encode(['success' => false, 'message' => 'Solo se pueden eliminar solicitudes con el estatus CREADO', 'icon' => 'warning']);
            }

            \DB::beginTransaction();
            
            $application->is_deleted = 1;
            $application->update();

            // $user = $this->getUserVacationsData();
            $user = EmployeeVacationUtils::getEmployeeVacationsData($employee_id);
    
            if($user->tot_vacation_remaining < $takedDays){
                return json_encode(['success' => false, 'message' => 'No cuentas con d??as disponibles', 'icon' => 'warning']);
            }
    
            $vacations = collect($user->vacation)->sortBy('year');

            $appBreakDowns = ApplicationsBreakdown::where('application_id', $request->id_application)->get();
            foreach($appBreakDowns as $ab){
                $ab->delete();
            }

            $application->start_date = $startDate;
            $application->end_date = $endDate;
            $application->take_holidays = $take_holidays;
            $application->take_rest_days = $take_rest_days;
            $application->total_days = $takedDays;
            $application->tot_calendar_days = $tot_calendar_days;
            $application->return_date = $returnDate;
            $application->emp_comments_n = $comments;
            $application->is_deleted = 0;
            $application->update();

            foreach($vacations as $vac){
                if($takedDays > 0){
                    $count = 0;
                    if($vac->remaining > 0){
                        for($i=0; $i<$vac->remaining; $i++){
                            $takedDays--;
                            $count++;
                            if($takedDays == 0 || $takedDays < 0){
                                break;
                            }
                        }
                        $vac->remaining = $vac->remaining - $count;
                        $appBreakdown = new ApplicationsBreakdown();
                        $appBreakdown->application_id = $application->id_application;
                        $appBreakdown->days_effective = $count;
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
        // $user = $this->getUserVacationsData();
        $user = EmployeeVacationUtils::getEmployeeVacationsData($employee_id);
        $user->applications = EmployeeVacationUtils::getTakedDays($user);
        return json_encode(['success' => true, 'message' => 'Registro editado con ??xito', 'icon' => 'success', 'oUser' => $user]);
    }

    public function filterYear(Request $request){
        try {
            $applications = EmployeeVacationUtils::getApplications($request->employee_id, $request->year);
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al cargar los registros', 'icon' => 'error']);    
        }

        return json_encode(['success' => true, 'applications' => $applications]);
    }

    public function deleteRequestVac(Request $request){
        try {
            $application = Application::findOrFail($request->id_application);

            if($application->request_status_id != SysConst::APPLICATION_CREADO){
                return json_encode(['success' => false, 'message' => 'Solo se pueden eliminar solicitudes con el estatus CREADO', 'icon' => 'warning']);
            }

            \DB::beginTransaction();
            
            $application->is_deleted = 1;
            $application->update();

            // $user = $this->getUserVacationsData();
            $user = EmployeeVacationUtils::getEmployeeVacationsData($request->employee_id);
            $user->applications = EmployeeVacationUtils::getApplications($request->employee_id, $request->year);
            $user->applications = EmployeeVacationUtils::getTakedDays($user);

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al eliminar el registro', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'message' => 'Registro eliminado con ??xito', 'icon' => 'success', 'oUser' => $user]);
    }

    public function sendRequestVac(Request $request){
        try {
            $application = Application::findOrFail($request->id_application);

            if($application->request_status_id != SysConst::APPLICATION_CREADO){
                return json_encode(['success' => false, 'message' => 'Solo se pueden enviar solicitudes con el estatus CREADO', 'icon' => 'warning']);
            }

            \DB::beginTransaction();
            $date = Carbon::now();
            $application->request_status_id = SysConst::APPLICATION_ENVIADO;
            $application->date_send_n = $date->toDateString();
            $application->folio_n = $this->makeFolio($date, $application->user_id);
            $application->update();

            $application_log = new ApplicationLog();
            $application_log->application_id = $application->id_application;
            $application_log->application_status_id = $application->request_status_id;
            $application_log->created_by = \Auth::user()->id;
            $application_log->updated_by = \Auth::user()->id;
            $application_log->save();

            // $user = $this->getUserVacationsData();
            $user = EmployeeVacationUtils::getEmployeeVacationsData($request->employee_id);
            $user->applications = EmployeeVacationUtils::getApplications($request->employee_id, $request->year);
            $user->applications = EmployeeVacationUtils::getTakedDays($user);

            $employee = User::find($request->employee_id);
            $arrOrgJobs = orgChartUtils::getDirectFatherOrgChartJob($employee->org_chart_job_id);

            $superviser = \DB::table('users')
                            ->where('is_delete', 0)
                            ->where('is_active', 1)
                            ->whereIn('org_chart_job_id', $arrOrgJobs)
                            ->first();

            $mailLog = new MailLog();
            $mailLog->date_log = Carbon::now()->toDateString();
            $mailLog->to_user_id = $superviser->id;
            $mailLog->application_id_n = $application->id_application;
            $mailLog->sys_mails_st_id = SysConst::MAIL_EN_PROCESO;
            $mailLog->type_mail_id = SysConst::MAIL_SOLICITUD_VACACIONES;
            $mailLog->is_deleted = 0;
            $mailLog->created_by = \Auth::user()->id;
            $mailLog->updated_by = \Auth::user()->id;
            $mailLog->save();
            
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al enviar el registro', 'icon' => 'error']);
        }

            $mypool = Pool::create();
            $mypool[] = async(function () use ($application, $request, $superviser, $mailLog){
                try {
                    Mail::to($superviser->email)->send(new requestVacationMail(
                                                            $application->id_application,
                                                            $request->employee_id,
                                                            $request->lDays,
                                                            $request->returnDate
                                                        )
                                                    );
                } catch (\Throwable $th) {
                    $mailLog->sys_mails_st_id = SysConst::MAIL_NO_ENVIADO;
                    $mailLog->update();   
                    return null; 
                }

                $mailLog->sys_mails_st_id = SysConst::MAIL_ENVIADO;
                $mailLog->update();
            })->then(function ($mailLog) {
                
            })->catch(function ($mailLog) {
                
            })->timeout(function ($mailLog) {
                
            });

        return json_encode(['success' => true, 'mail_log_id' => $mailLog->id_mail_log, 'message' => 'Registro enviado con ??xito', 'icon' => 'success', 'oUser' => $user]);
    }

    public function checkMail(Request $request){
        $mailLog = MailLog::find($request->mail_log_id);

        return json_encode(['sucess' => true, 'status' => $mailLog->sys_mails_st_id]);
    }

    public function makeFolio($date, $employee_id){
        $employee_num = \DB::table('users')
                        ->where('id', $employee_id)
                        ->value('employee_num');

        if(strlen($employee_num) < 4){
            for($i = 0; $i < 4-sizeof($employee_num); $i++ ){
                $employee_num = '0'.$employee_num;
            }
        }

        $folio = $date->format('Y').$date->format('m').$date->format('d').$employee_num;

        return $folio;
    }

    public function getEmpApplicationsEA(Request $request){
        try {
            $lApplicationsEA = EmployeeVacationUtils::getEmpApplicationsEA($request->user_id);
            $lSpecialSeason = EmployeeVacationUtils::getEmpSpecialSeason($request->user_id);
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'No se pudieron obtener registos de vacaciones solicitadas anteriormente', 'icon' => 'warning']);
        }

        return json_encode(['success' => true, 'arrAplications' => $lApplicationsEA, 'arrSpecialSeasons' => $lSpecialSeason]);
    }

    public function getMyVacationHistory(Request $request){
        try {
            $config = \App\Utils\Configuration::getConfigurations();
            $user = EmployeeVacationUtils::getEmployeeVacationsData($request->user_id, true);
        } catch (\Throwable $th) {
            return json_encode(['success' => true, 'message' => 'Error al obtener los registros', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'oUser' => $user]);
    }

    public function hiddeHistory(Request $request){
        try {
            $config = \App\Utils\Configuration::getConfigurations();
            $user = EmployeeVacationUtils::getEmployeeVacationsData($request->user_id);
        } catch (\Throwable $th) {
            return json_encode(['success' => true, 'message' => 'Error al obtener los registros', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'oUser' => $user]);
    }
}
