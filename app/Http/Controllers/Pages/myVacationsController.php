<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\User;
use App\Mail\requestVacationMail;
use App\Utils\EmployeeVacationUtils;
use App\Models\Vacations\Application;
use App\Models\Vacations\ApplicationsBreakdown;
use App\Models\Vacations\ApplicationLog;
use App\Models\Vacations\ApplicationVsTypes;
use App\Models\Vacations\MailLog;
use App\Constants\SysConst;
use App\Models\Adm\OrgChartJob;
use App\Utils\orgChartUtils;
use Spatie\Async\Pool;
use \App\Utils\delegationUtils;
use \App\Utils\folioUtils;
use App\Utils\recoveredVacationsUtils;
use App\Utils\notificationsUtils;

class myVacationsController extends Controller
{
    public $months_code = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    public function index(){
        $config = \App\Utils\Configuration::getConfigurations();

        $user = EmployeeVacationUtils::getEmployeeDataForMyVacation(delegationUtils::getIdUser());
        $now = Carbon::now();
        $initialCalendarDate = $now->subMonths(1)->toDateString();

        $holidays = \DB::table('holidays')
                        ->where('fecha', '>', Carbon::now()->subDays(30)->toDateString())
                        ->where('is_deleted', 0)
                        ->pluck('fecha');

        $lTemp_special = EmployeeVacationUtils::getEmployeeTempSpecial($user->org_chart_job_id, $user->id, $user->job_id);

        $constants = [
            'SEMANA' => SysConst::SEMANA,
            'QUINCENA' => SysConst::QUINCENA,
            'APPLICATION_CREADO' => SysConst::APPLICATION_CREADO,
            'APPLICATION_ENVIADO' => SysConst::APPLICATION_ENVIADO,
            'APPLICATION_APROBADO' => SysConst::APPLICATION_APROBADO,
            'APPLICATION_CONSUMIDO' => SysConst::APPLICATION_CONSUMIDO,
            'APPLICATION_RECHAZADO' => SysConst::APPLICATION_RECHAZADO
        ];

        $today = Carbon::now()->toDateString();

        $lSuperviser = orgChartUtils::getSupervisersToSend($user->org_chart_job_id);

        $lStatus = \DB::table('sys_applications_sts')
                        ->where('is_deleted', 0)
                        ->where('id_applications_st', '!=', SysConst::APPLICATION_CONSUMIDO)
                        ->select(
                            'id_applications_st as id',
                            'applications_st_name as name'
                        )
                        ->get();

        $lEvents = EmployeeVacationUtils::getEmployeeEvents(delegationUtils::getIdUser());

        $requested_client = $config->requested_client_web;
        $authorized_client = $config->authorized_client_web;

        return view('emp_vacations.my_vacations')->with('user', $user)
                                                ->with('initialCalendarDate', $initialCalendarDate)
                                                ->with('lHolidays', $holidays)
                                                ->with('year', Carbon::now()->year)
                                                ->with('constants', $constants)
                                                ->with('config', $config)
                                                ->with('today', $today)
                                                ->with('lTemp', $lTemp_special)
                                                ->with('lSuperviser', $lSuperviser)
                                                ->with('lStatus', $lStatus)
                                                ->with('lEvents', $lEvents)
                                                ->with('requested_client', $requested_client)
                                                ->with('authorized_client', $authorized_client);
    }

    public function getlDays(Request $request){
        try {
            $oApp = Application::find($request->id_application);
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento no se ha podido obtener la lista de días laborables. Por favor, cierre la solicitud e intente nuevamente', 'error']);
        }
        return json_encode(['success' => true, 'lDays' => $oApp->ldays]);
    }

    public function setRequestVac(Request $request){
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $comments = $request->comments;
        $takedDays = $request->takedDays;
        $returnDate = $request->returnDate;
        $tot_calendar_days = $request->tot_calendar_days;
        $lDays = $request->lDays;
        $take_holidays = $request->take_holidays;
        $take_rest_days = $request->take_rest_days;
        $employee_id = $request->employee_id;
        
        try {

            $arrApplicationsEA = EmployeeVacationUtils::getEmpApplicationsEA($employee_id);
            $arrDaysBetween = [];
            foreach ($arrApplicationsEA as $arr) {
                $isBetWeen = Carbon::parse($arr)->between($startDate, $endDate);
                if ($isBetWeen) {
                    $arrDaysBetween[] = Carbon::parse($arr)->locale('es-ES')->isoFormat('ddd D-MMM-YYYY');
                }
            }
            
            if (count($arrDaysBetween) > 0) {
                $message = 'En ' . (count($arrDaysBetween) > 1 ? 'las fechas ' : 'la fecha ') . 
                        implode(', ', $arrDaysBetween) .
                        ' ya hay una incidencia registrada. Por favor, ingrese una fecha o periodo distinto para crear una nueva incidencia';
                return json_encode(['success' => false, 'message' => $message, 'icon' => 'warning']);
            }

            // $user = $this->getUserVacationsData();
            $user = EmployeeVacationUtils::getEmployeeVacationsData($employee_id, true, 1);

            foreach($user->applications as $ap){
                if($ap->request_status_id == 1){
                    return json_encode(['success' => false, 'message' => 'No es posible generar una nueva solicitud de vacaciones si existen solicitudes pendientes de envío. Por favor, envíe o elimine las solicitudes pendientes antes de continuar', 'icon' => 'warning']);
                }
            }

            if($user->tot_vacation_remaining < $takedDays){
                return json_encode(['success' => false, 'message' => 'Actualmente no tienes los días de vacaciones solicitados. Por favor, ingresa un número menor de días de vacaciones para continuar, o si necesitas aclaraciones, consulta con el área de gestión humana', 'icon' => 'warning']);
            }

            if($comments == null || $comments == ''){
                return json_encode(['success' => false, 'message' => 'Para proseguir, se requiere incluir un comentario en la solicitud', 'icon' => 'warning']);
            }

            $vacations = collect($user->vacation)->sortBy('year');

            \DB::beginTransaction();

            $comments = str_replace(['"', "\\", "\r", "\n"], "", $comments);

            $application = new Application();
            $application->folio_n = folioUtils::makeFolio(Carbon::now(), $employee_id);
            $application->start_date = $startDate;
            $application->end_date = $endDate;
            $application->take_holidays = $take_holidays;
            $application->take_rest_days = $take_rest_days;
            $application->total_days = $takedDays;
            $application->tot_calendar_days = $tot_calendar_days;
            $application->return_date = $returnDate;
            $application->ldays = json_encode($lDays);
            $application->user_id = $employee_id;
            $application->request_status_id = SysConst::APPLICATION_CREADO;
            $application->type_incident_id = SysConst::TYPE_VACACIONES;
            $application->emp_comments_n = $comments;
            $application->is_deleted = false;
            $application->save();

            $applicationVsType = new ApplicationVsTypes();
            $applicationVsType->application_id = $application->id_application;
            $applicationVsType->is_past = $request->is_past;
            $applicationVsType->is_advanced = $request->is_advanced;
            $applicationVsType->is_proportional = $request->is_proportional;
            $applicationVsType->is_season_special = $request->is_season_special;
            $applicationVsType->is_event = $request->is_event;
            $applicationVsType->is_recover_vacation = 0;

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
                        if($vac->is_recovered){
                            $applicationVsType->is_recover_vacation = $vac->is_recovered;
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

            $applicationVsType->is_normal = !($request->is_past ||
                                                $request->is_advanced ||
                                                $request->is_proportional ||
                                                $request->is_season_special ||
                                                $applicationVsType->is_recover_vacation
                                            );
            $applicationVsType->save();

            $application_log = new ApplicationLog();
            $application_log->application_id = $application->id_application;
            $application_log->application_status_id = $application->request_status_id;
            // $application_log->created_by = \Auth::user()->id;
            // $application_log->updated_by = \Auth::user()->id;
            $application_log->created_by = delegationUtils::getIdUser();
            $application_log->updated_by = delegationUtils::getIdUser();
            $application_log->save();

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible almacenar la solicitud debido a un error inesperado. Por favor, verifique su conexión a internet, cierre la solicitud e inténtelo de nuevo', 'icon' => 'error']);
        }

        // $user = $this->getUserVacationsData();
        $user = EmployeeVacationUtils::getEmployeeVacationsData($employee_id);
        // $user->applications = EmployeeVacationUtils::getTakedDays($user);

        return json_encode(['success' => true, 'message' => 'Solicitud guardada con éxito', 'oUser' => $user]);
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
        $lDays = $request->lDays;

        try {
            if($comments == null || $comments == ''){
                return json_encode(['success' => false, 'message' => 'Para proseguir, se requiere incluir un comentario en la solicitud', 'icon' => 'warning']);
            }

            $arrApplicationsEA = EmployeeVacationUtils::getEmpApplicationsEA($employee_id);

            foreach($arrApplicationsEA as $arr){
                $isBetWeen = Carbon::parse($arr)->between($startDate, $endDate);
                if($isBetWeen){
                    return json_encode(['success' => false, 'message' => 'En la fecha '.Carbon::parse($arr)->locale('es-ES')->isoFormat('ddd D-MMM-YYYY').' ya hay una solicitud de vacaciones registrada. Por favor, ingrese una fecha distinta para poder proseguir', 'icon' => 'warning']);
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
            $user = EmployeeVacationUtils::getEmployeeVacationsData($employee_id, true, 1);

            if(($user->tot_vacation_remaining + $user->prox_vac_days) < $takedDays){
                return json_encode(['success' => false, 'message' => 'Actualmente no tienes los días de vacaciones solicitados. Por favor, ingresa un número menor de días de vacaciones para continuar, o si necesitas aclaraciones, consulta con el área de gestión humana', 'icon' => 'warning']);
            }
    
            $vacations = collect($user->vacation)->sortBy('year');

            $appBreakDowns = ApplicationsBreakdown::where('application_id', $request->id_application)->get();
            foreach($appBreakDowns as $ab){
                $ab->delete();
            }

            $comments = str_replace(['"', "\\", "\r", "\n"], "", $comments);

            $application->start_date = $startDate;
            $application->end_date = $endDate;
            $application->take_holidays = $take_holidays;
            $application->take_rest_days = $take_rest_days;
            $application->total_days = $takedDays;
            $application->tot_calendar_days = $tot_calendar_days;
            $application->return_date = $returnDate;
            $application->ldays = $lDays;
            $application->emp_comments_n = $comments;
            $application->is_deleted = 0;
            $application->update();

            $applicationVsType = ApplicationVsTypes::where('application_id', $application->id_application)->first();
            $applicationVsType->application_id = $application->id_application;
            $applicationVsType->is_past = $request->is_past;
            $applicationVsType->is_advanced = $request->is_advanced;
            $applicationVsType->is_proportional = $request->is_proportional;
            $applicationVsType->is_season_special = $request->is_season_special;
            $applicationVsType->is_recover_vacation = 0;

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

                        if($vac->is_recovered){
                            $applicationVsType->is_recover_vacation = $vac->is_recovered;
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
            $applicationVsType->is_normal = !($request->is_past ||
                                                $request->is_advanced ||
                                                $request->is_proportional ||
                                                $request->is_season_special ||
                                                $applicationVsType->is_recover_vacation
                                            );
            $applicationVsType->update();

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible almacenar los cambios en la solicitud debido a un error inesperado. Por favor, verifique su conexión a internet, cierre la solicitud e inténtelo de nuevo', 'icon' => 'error']);
        }
        // $user = $this->getUserVacationsData();
        $user = EmployeeVacationUtils::getEmployeeVacationsData($employee_id);
        $user->applications = EmployeeVacationUtils::getTakedDays($user);
        return json_encode(['success' => true, 'message' => 'Registro editado con éxito', 'icon' => 'success', 'oUser' => $user]);
    }

    public function filterYear(Request $request){
        try {
            $applications = EmployeeVacationUtils::getApplications($request->employee_id, $request->year);
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento no es posible obtener los registros. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);    
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
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible eliminar la solicitud debido a un error inesperado. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'message' => 'Registro eliminado con éxito', 'icon' => 'success', 'oUser' => $user]);
    }

    public function sendRequestVac(Request $request){
        try {
            if(delegationUtils::getOrgChartJobIdUser() == 1){
                return json_encode(['success' => false, 'message' => 'No estás asignado a un área funcional, por favor contacta con el área de gestión humana', 'icon' => 'warning']);
            }

            $application = Application::findOrFail($request->id_application);

            $data = $this->checkExternalIncident($application, json_decode($application->ldays));

            if(!empty($data)){
                $data = json_decode($data);
                if($data->code == 500 || $data->code == 550){
                    \DB::rollBack();
                    return json_encode(['success' => false, 'message' => $data->message, 'icon' => 'error']);
                }
            }else{
                \DB::rollBack();
                return json_encode(['success' => false, 'message' => 'No fue posible conectar con el sistema SIIE. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
            }

            if($application->request_status_id != SysConst::APPLICATION_CREADO){
                return json_encode(['success' => false, 'message' => 'La solicitud que deseas enviar no tiene el estatus de CREADO. Solo se pueden enviar solicitudes con dicho estatus', 'icon' => 'warning']);
            }

            $employee = User::find($request->employee_id);
            $lSuperviser = orgChartUtils::getSupervisersToSend($employee->org_chart_job_id);

            if(count($lSuperviser) == 0){
                \DB::rollBack();
                return json_encode(['success' => false, 'message' => 'No cuenta con un supervisor asignado en el sistema. Por favor, contacte con el área de gestion humana', 'icon' => 'error']);
            }

            $oType = \DB::table('applications_vs_types')
                        ->where('application_id', $application->id_application)
                        ->first();

            \DB::beginTransaction();
            if($oType->is_recover_vacation){
                recoveredVacationsUtils::insertUsedDays($application);
            }
            $date = Carbon::now();
            $application->request_status_id = SysConst::APPLICATION_ENVIADO;
            $application->date_send_n = $date->toDateString();
            $application->send_default = isset($lSuperviser[0]->is_default);
            // $application->folio_n = $this->makeFolio($date, $application->user_id);
            $application->requested_client = $request->requested_client;
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

            foreach($lSuperviser as $superviser){
                $mailLog = new MailLog();
                $mailLog->date_log = Carbon::now()->toDateString();
                $mailLog->to_user_id = $superviser->id;
                $mailLog->application_id_n = $application->id_application;
                $mailLog->sys_mails_st_id = SysConst::MAIL_EN_PROCESO;
                $mailLog->type_mail_id = SysConst::MAIL_SOLICITUD_VACACIONES;
                $mailLog->is_deleted = 0;
                // $mailLog->created_by = \Auth::user()->id;
                // $mailLog->updated_by = \Auth::user()->id;
                $mailLog->created_by = delegationUtils::getIdUser();
                $mailLog->updated_by = delegationUtils::getIdUser();
                $mailLog->save();
    
                $data = new \stdClass;
                $data->user_id = null;
                $data->org_chart_job_id_n = $superviser->org_chart_job_id;
                $data->message = delegationUtils::getFullNameUI().' Tiene una solicitud de vacaciones';
                $data->url = route('requestVacations', ['id' => $application->id_application]);
                $data->type_id = SysConst::NOTIFICATION_TYPE_VACACIONES;
                $data->priority = SysConst::NOTIFICATION_PRIORITY_VACACIONES;
                $data->icon = SysConst::NOTIFICATION_ICON_VACACIONES;
                $data->row_type_id = $application->type_incident_id;
                $data->row_id = $application->id_application;
                $data->end_date = null;
    
                notificationsUtils::createNotification($data);
            }
            
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible enviar la solicitud debido a un error inesperado. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
        }

            $mypool = Pool::create();
            $mypool[] = async(function () use ($application, $request, $lSuperviser, $mailLog){
                try {
                    $config = \App\Utils\Configuration::getConfigurations();
                    $arrUsers = $lSuperviser->map(function ($item) {
                        return $item->id;
                    })->toArray();

                    $arrUsers = array_unique($arrUsers);

                    $headers = [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'X-API-Key' => $config->apiKeyPghMobile
                    ];

                    $oUser = delegationUtils::getUser();
                    $full_name = $oUser->short_name . ' ' . $oUser->first_name . ' ' . $oUser->last_name;

                    $body = '{
                        "title": "' . $full_name .  '",
                        "body": "Envió solicitud de vacaciones",
                        "data": {
                            "isNewToBadge": 1,
                            "countBadge": 1
                        },
                        "sound": "default",
                        "badge": 1,
                        "user_ids": [],
                        "external_ids":  ' . json_encode($arrUsers) . '
                    }';

                    $client = new Client([
                        'base_uri' => $config->urlNotificationAppMobile,
                        'connect_timeout' => 10,
                        'timeout' => 30.0,
                        'headers' => $headers,
                        'verify' => false
                    ]);

                    $requestNotification = new \GuzzleHttp\Psr7\Request('POST', '', $headers, $body);
                    $response = $client->sendAsync($requestNotification)->wait();
                    $jsonString = $response->getBody()->getContents();
                    $data = json_decode($jsonString);

                } catch (\Throwable $th) {
                    \Log::error($th);
                }

                try {
                    // $lUsers = orgChartUtils::getAllUsersByOrgChartJob($superviser->org_chart_job_id);
                    $arrUsers = $lSuperviser->map(function ($item) {
                        return $item->institutional_mail;
                    })->toArray();

                    $arrUsers = array_unique($arrUsers);
                    foreach($lSuperviser as $sup){
                        $is_delegation = isset($sup->is_delegation);
                        Mail::to($sup->institutional_mail)->send(new requestVacationMail(
                                                                $application->id_application,
                                                                $request->employee_id,
                                                                $application->ldays,
                                                                $request->returnDate,
                                                                $sup
                                                            )
                                                        );
                    }

                } catch (\Throwable $th) {
                    $mailLog->sys_mails_st_id = SysConst::MAIL_NO_ENVIADO;
                    $mailLog->update();   
                    \Log::error($th);
                    return null; 
                }

                $mailLog->sys_mails_st_id = SysConst::MAIL_ENVIADO;
                $mailLog->update();

            })->then(function ($mailLog) {
                
            })->catch(function ($mailLog) {
                
            })->timeout(function ($mailLog) {
                
            });

        return json_encode(['success' => true, 'mail_log_id' => $mailLog->id_mail_log, 'message' => 'Registro enviado con éxito', 'icon' => 'success', 'oUser' => $user]);
    }

    public function checkMail(Request $request){
        $mailLog = MailLog::find($request->mail_log_id);

        $message = '';
        if($mailLog->sys_mails_st_id == SysConst::MAIL_NO_ENVIADO){
            $user = \DB::table('users')
                        ->where('id', $mailLog->to_user_id)
                        ->first();

            if(is_null($user->institutional_mail)){
                $message = 'En este momento no es posible enviar el correo electrónico porque el solicitante no cuenta con una dirección registrada en el sistema. Solicita una dirección de correo electrónico a GH para fines de comunicación.';
            }else{
                $message = 'El correo electrónico no pudo ser enviado. Te recomendamos verificar tu conexión a internet para resolver el problema (de ser necesario comunícate con el área de sistemas).';
            }
        }

        return json_encode(['success' => true, 'status' => $mailLog->sys_mails_st_id, 'message' => $message]);
    }

    public function getEmpApplicationsEA(Request $request){
        try {
            $lApplicationsEA = EmployeeVacationUtils::getEmpApplicationsEA($request->user_id);
            $lSpecialSeason = EmployeeVacationUtils::getEmpSpecialSeason($request->user_id);
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento no fue posible obtener los registros de vacaciones solicitadas anteriormente. Por favor, verifique su conexión a internet, cierre la solicitud e inténtelo de nuevo', 'icon' => 'warning']);
        }

        return json_encode(['success' => true, 'arrAplications' => $lApplicationsEA, 'arrSpecialSeasons' => $lSpecialSeason]);
    }

    public function getMyVacationHistory(Request $request){
        try {
            $config = \App\Utils\Configuration::getConfigurations();
            $user = EmployeeVacationUtils::getEmployeeVacationsData($request->user_id, true);
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => true, 'message' => 'En este momento no fue posible obtener los registros de vacaciones pasadas. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'oUser' => $user]);
    }

    public function hiddeHistory(Request $request){
        try {
            $config = \App\Utils\Configuration::getConfigurations();
            $user = EmployeeVacationUtils::getEmployeeVacationsData($request->user_id);
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => true, 'message' => 'En este momento no fue posible obtener los registros de vacaciones. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'oUser' => $user]);
    }

    public function checkExternalIncident($oApplication, $lDays){
        $employee = \DB::table('users')
                        ->where('id', $oApplication->user_id)
                        ->first();

        $ext_company_id = \DB::table('ext_company')
                            ->where('id_company', $employee->company_id)
                            ->value('external_id');

        $appBreakDowns = ApplicationsBreakdown::where('application_id', $oApplication->id_application)->get();

        $typeIncident = \DB::table('cat_incidence_tps')
                            ->where('id_incidence_tp', $oApplication->type_incident_id)
                            ->first();

        $userVacation = \DB::table('vacation_users')
                            ->where('user_id', $employee->id)
                            ->where('is_deleted', 0)
                            ->get();
        $count = 0;
        
        $arrJson = [
            'to_insert' => false,
            'application_id' => $oApplication->id_application,
            'folio' => $oApplication->folio_n,
            'employee_id' => $employee->external_id_n,
            'company_id' => $ext_company_id,
            'type_pay_id' => $employee->payment_frec_id,
            'type_incident_id' => $typeIncident->id_incidence_tp,
            'class_incident_id' => $typeIncident->incidence_cl_id,
            'date_send' => $oApplication->date_send_n,
            'date_ini' => $oApplication->start_date,
            'date_end' => $oApplication->end_date,
            'total_days' => $oApplication->total_days
        ];
        $config = \App\Utils\Configuration::getConfigurations();
        $client = new Client([
            'base_uri' => $config->urlSync,
            'timeout' => 30.0,
        ]);

        $str = json_encode($arrJson);

        $response = $client->request('GET', 'postIncidents/' . json_encode($arrJson));
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        return json_encode(['code' => $data->response->code, 'message' => $data->response->message]);
    } 

    public function calcReturnDate(Request $request){
        try {
            $star_date = $request->start_date;
            $end_date = $request->end_date;
            $user_id = $request->user_id;
            $application_id = $request->application_id;
            $oUser = User::findOrFail($user_id);
            $lApplications = Application::where('user_id', $user_id)
                                        ->where('is_deleted', 0)
                                        ->where('end_date', '>=', $end_date);

            if(!is_null($application_id)){
                $lApplications = $lApplications->where('id_application', '!=', $application_id);
            }

            $lApplications = $lApplications->get();

            $arrlDays = $lApplications->map(function($item){
                $lDays = collect(json_decode($item->ldays));
                $lDays = $lDays->map(function($day){
                            if($day->taked){
                                return $day->date;
                            }
                        });
                return $lDays;
            });

            $arrlDays = \Arr::collapse($arrlDays);

            $lHolidays = \DB::table('holidays')
                        ->where('fecha', '>=', $end_date)
                        ->where('is_deleted', 0)
                        ->pluck('fecha')
                        ->toArray();

            $arrlDays = array_merge($arrlDays, $lHolidays);

            $invalidDays = $oUser->payment_frec_id == SysConst::SEMANA ? [Carbon::SUNDAY] : [Carbon::SATURDAY, Carbon::SUNDAY];
            $oReturnDate = Carbon::parse($end_date)->addDay();
            for($i = 0; $i<365; $i++){
                if(in_array($oReturnDate->dayOfWeek, $invalidDays)){
                    $oReturnDate->addDay();
                }else if(in_array($oReturnDate->toDateString(), $arrlDays)){
                    $oReturnDate->addDay();
                }else{
                    break;
                }
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento no se ha podido obtener la fecha de regreso. Por favor, cierre la solicitud e intente nuevamente', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'returnDate' => $oReturnDate->toDateString()]);
    }
}
