<?php

namespace App\Http\Controllers\Pages;

use \App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \App\Constants\SysConst;
use \App\Utils\EmployeeVacationUtils;
use \App\Utils\folioUtils;
use \App\Models\Vacations\Application;
use \App\Models\Vacations\ApplicationVsTypes;
use \App\Models\Vacations\ApplicationLog;
use App\Models\Vacations\ApplicationsBreakdown;
use \App\Utils\delegationUtils;
use \App\Utils\incidencesUtils;
use App\Utils\orgChartUtils;
use App\Models\Vacations\MailLog;
use Spatie\Async\Pool;
use Illuminate\Support\Facades\Mail;
use App\Mail\requestIncidenceMail;
use Carbon\Carbon;
use App\Mail\authorizeIncidenceMail;
use App\Utils\notificationsUtils;
use GuzzleHttp\Client;

class incidencesController extends Controller
{
    public function getIncidences($user_id){
        $lIncidences = \DB::table('applications as ap')
                        ->leftJoin('cat_incidence_tps as tp', 'tp.id_incidence_tp', '=', 'ap.type_incident_id')
                        ->leftJoin('cat_incidence_cls as cl', 'cl.id_incidence_cl', '=', 'tp.incidence_cl_id')
                        ->leftJoin('applications_vs_types as at', 'at.application_id', '=', 'ap.id_application')
                        ->leftJoin('sys_applications_sts as st', 'st.id_applications_st', '=', 'ap.request_status_id')
                        ->leftJoin('users as u', 'u.id', '=', 'ap.user_apr_rej_id')
                        ->leftJoin('users as emp', 'emp.id', '=', 'ap.user_id')
                        ->where('type_incident_id', '!=', SysConst::TYPE_VACACIONES)
                        ->where('ap.is_deleted', 0)
                        ->where('ap.user_id', $user_id)
                        ->select(
                            'ap.*',
                            'at.is_normal',
                            'at.is_past',
                            'at.is_season_special',
                            'at.is_event',
                            'tp.id_incidence_tp',
                            'tp.incidence_tp_name',
                            'tp.limit_days_n',
                            'cl.id_incidence_cl',
                            'cl.incidence_cl_name',
                            'st.applications_st_name',
                            'u.full_name_ui as user_apr_rej_name',
                            'emp.full_name_ui as employee',
                        )
                        ->get();

        return $lIncidences;
    }

    public function index(){
        // $lIncidences = $this->getIncidences(delegationUtils::getIdUser());
        $lIncidences = incidencesUtils::getUserIncidences(delegationUtils::getIdUser());

        $constants = [
            'SEMANA' => SysConst::SEMANA,
            'QUINCENA' => SysConst::QUINCENA,
            'APPLICATION_CREADO' => SysConst::APPLICATION_CREADO,
            'APPLICATION_ENVIADO' => SysConst::APPLICATION_ENVIADO,
            'APPLICATION_RECHAZADO' => SysConst::APPLICATION_RECHAZADO,
            'APPLICATION_APROBADO' => SysConst::APPLICATION_APROBADO,
            'TYPE_VACACIONES' => SysConst::TYPE_VACACIONES,
            'TYPE_INASISTENCIA' => SysConst::TYPE_INASISTENCIA,
            'TYPE_INASISTENCIA_ADMINISTRATIVA' => SysConst::TYPE_INASISTENCIA_ADMINISTRATIVA,
            'TYPE_PERMISO_SIN_GOCE' => SysConst::TYPE_PERMISO_SIN_GOCE,
            'TYPE_PERMISO_CON_GOCE' => SysConst::TYPE_PERMISO_CON_GOCE,
            'TYPE_PERMISO_PATERNIDAD' => SysConst::TYPE_PERMISO_PATERNIDAD,
            'TYPE_PRESCRIPCIÓN_MEDICA' => SysConst::TYPE_PRESCRIPCIÓN_MEDICA,
            'TYPE_TEMA_LABORAL' => SysConst::TYPE_TEMA_LABORAL,
            'TYPE_CUMPLEAÑOS' => SysConst::TYPE_CUMPLEAÑOS,
            'TYPE_HOMEOFFICE' => SysConst::TYPE_HOMEOFFICE,
        ];

        $lClass = \DB::table('cat_incidence_cls')
                        ->where('id_incidence_cl', '!=', SysConst::TYPE_VACACIONES)
                        ->where('is_deleted', 0)
                        ->where('is_active', 1)
                        ->get();

        $lTypes = \DB::table('cat_incidence_tps')
                        ->where('incidence_cl_id', '!=', SysConst::TYPE_VACACIONES)
                        ->where('is_deleted', 0)
                        ->where('is_active', 1)
                        ->get();

        $lTemp_special = EmployeeVacationUtils::getEmployeeTempSpecial(delegationUtils::getOrgChartJobIdUser(), delegationUtils::getIdUser(), delegationUtils::getJobIdUser());

        $lEvents = EmployeeVacationUtils::getEmployeeEvents(delegationUtils::getIdUser());

        $lHolidays = \DB::table('holidays')
                        ->where('fecha', '>', Carbon::now()->subDays(30)->toDateString())
                        ->where('is_deleted', 0)
                        ->pluck('fecha');

        $lSuperviser = orgChartUtils::getSupervisersToSend(delegationUtils::getOrgChartJobIdUser());

        $now = Carbon::now();
        $initialCalendarDate = $now->subMonths(1)->toDateString();

        $lStatus = \DB::table('sys_applications_sts')
                        ->where('is_deleted', 0)
                        ->whereNotIn('id_applications_st', [SysConst::APPLICATION_CONSUMIDO])
                        ->select(
                            'id_applications_st as id',
                            'applications_st_name as name'
                        )
                        ->get();

        $config = \App\Utils\Configuration::getConfigurations();
        $requested_client = $config->requested_client_web;

        return view('Incidences.incidences')->with('lIncidences', $lIncidences)
                                            ->with('constants', $constants)
                                            ->with('lClass', $lClass)
                                            ->with('lTypes', $lTypes)
                                            ->with('lTemp', $lTemp_special)
                                            ->with('lHolidays', $lHolidays)
                                            ->with('oUser', \Auth::user())
                                            ->with('lSuperviser', $lSuperviser)
                                            ->with('initialCalendarDate', $initialCalendarDate)
                                            ->with('lStatus', $lStatus)
                                            ->with('lEvents', $lEvents)
                                            ->with('requested_client', $requested_client);
    }

    public function getEmpIncidencesEA(Request $request){
        try {
            // $lVacationsEA = EmployeeVacationUtils::getEmpApplicationsEA($request->user_id);
            $lVacationsEA = EmployeeVacationUtils::getEmpApplicationsVacEAWithComments($request->user_id);
            $lIncidencesEA = incidencesUtils::getEmpIncidencesEA($request->user_id);
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento no fue posible obtener los registros de incidencias solicitadas anteriormente. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'warning']);
        }

        return json_encode(['success' => true, 'lIncidences' => $lIncidencesEA, 'lVacations' => $lVacationsEA]);
    }

    public function createIncidence(Request $request){
        $start_date = $request->startDate;
        $end_date = $request->endDate;
        $comments = $request->comments;
        $takedDays = $request->takedDays;
        $return_date = $request->returnDate;
        $tot_calendar_days = $request->tot_calendar_days;
        $lDays = $request->lDays;
        $take_holidays = false;
        $take_rest_days = false;
        $employee_id = $request->employee_id;
        $type_incident_id = $request->incident_type_id;
        $class_incident_id = $request->incident_class_id;
        $is_normal = $request->is_normal;
        $is_past = $request->is_past;
        $is_season_special  = $request->is_season_special;
        $is_event  = $request->is_event;
        try {
            if($comments == null || $comments == ""){
                return json_encode(['success' => false, 'message' => 'Para proseguir, se requiere incluir un comentario en la solicitud', 'icon' => 'warning']);
            }

            $arrApplicationsEA = EmployeeVacationUtils::getEmpApplicationsEA($employee_id);
            $arrDaysBetween = [];
            foreach ($arrApplicationsEA as $arr) {
                $isBetWeen = Carbon::parse($arr)->between($start_date, $end_date);
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

            \DB::beginTransaction();

            $comments = str_replace(['"', "\\", "\r", "\n"], "", $comments);

            $application = new Application();
            $application->folio_n = folioUtils::makeFolio(Carbon::now(), $employee_id, $type_incident_id);
            $application->start_date = $start_date;
            $application->end_date = $end_date;
            $application->take_holidays = $take_holidays;
            $application->take_rest_days = $take_rest_days;
            $application->total_days = $takedDays;
            $application->tot_calendar_days = $tot_calendar_days;
            $application->return_date = $return_date;
            $application->ldays = json_encode($lDays);
            $application->user_id = $employee_id;
            $application->request_status_id = SysConst::APPLICATION_CREADO;
            $application->type_incident_id = $type_incident_id;
            $application->emp_comments_n = $comments;
            $application->is_deleted = false;
            $application->save();

            if($type_incident_id == SysConst::TYPE_CUMPLEAÑOS){
                $appBreakdown = new ApplicationsBreakdown();
                $appBreakdown->application_id = $application->id_application;
                $appBreakdown->days_effective = 1;
                $appBreakdown->application_year = $request->birthDayYear;
                $appBreakdown->admition_count = 1;
                $appBreakdown->save();
            }

            $applicationVsType = new ApplicationVsTypes();
            $applicationVsType->application_id = $application->id_application;
            $applicationVsType->is_past = $is_past;
            $applicationVsType->is_season_special = $is_season_special;
            $applicationVsType->is_event = $is_event;
            $applicationVsType->is_recover_vacation = 0;
            $applicationVsType->is_normal = !($request->is_past || $request->is_season_special);
            $applicationVsType->save();

            $application_log = new ApplicationLog();
            $application_log->application_id = $application->id_application;
            $application_log->application_status_id = $application->request_status_id;
            $application_log->created_by = delegationUtils::getIdUser();
            $application_log->updated_by = delegationUtils::getIdUser();
            $application_log->save();

            $lIncidences = incidencesUtils::getUserIncidences(delegationUtils::getIdUser());

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible almacenar la solicitud debido a un error inesperado. Por favor, verifique su conexión a internet, cierre la solicitud e inténtelo de nuevo', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lIncidences' => $lIncidences]);
    }

    public function updateIncidence(Request $request){
        $id_application = $request->id_application;
        $start_date = $request->startDate;
        $end_date = $request->endDate;
        $comments = $request->comments;
        $takedDays = $request->takedDays;
        $return_date = $request->returnDate;
        $tot_calendar_days = $request->tot_calendar_days;
        $lDays = $request->lDays;
        $take_holidays = false;
        $take_rest_days = false;
        $employee_id = $request->employee_id;
        $type_incident_id = $request->incident_type_id;
        $class_incident_id = $request->incident_class_id;
        $is_normal = $request->is_normal;
        $is_past = $request->is_past;
        $is_season_special  = $request->is_season_special;

        try {

            if($comments == null || $comments == ""){
                return json_encode(['success' => false, 'message' => 'Para proseguir, se requiere incluir un comentario en la solicitud', 'icon' => 'warning']);
            }
            
            $application = Application::findOrFail($id_application);

            \DB::beginTransaction();

            $comments = str_replace(['"', "\\", "\r", "\n"], "", $comments);

            $application->start_date = $start_date;
            $application->end_date = $end_date;
            $application->total_days = $takedDays;
            $application->tot_calendar_days = $tot_calendar_days;
            $application->return_date = $return_date;
            $application->ldays = json_encode($lDays);
            $application->emp_comments_n = $comments;
            $application->update();

            $applicationVsType = ApplicationVsTypes::where('application_id', $application->id_application)->first();
            $applicationVsType->is_past = $is_past;
            $applicationVsType->is_season_special = $is_season_special;
            $applicationVsType->is_normal = !($request->is_past || $request->is_season_special);
            $applicationVsType->update();

            $lIncidences = $this->getIncidences($application->user_id);
            
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible actualizar la solicitud debido a un error inesperado. Por favor, verifique su conexión a internet, cierre la solicitud e inténtelo de nuevo', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lIncidences' => $lIncidences]);
    }

    public function getApplication(Request $request){
        $application_id = $request->application_id;
        try {
            $oApplication = \DB::table('applications as ap')
                                ->leftJoin('cat_incidence_tps as tp', 'tp.id_incidence_tp', '=', 'ap.type_incident_id')                    
                                ->leftJoin('cat_incidence_cls as cl', 'cl.id_incidence_cl', '=', 'tp.incidence_cl_id')
                                ->leftJoin('applications_vs_types as at', 'at.application_id', '=', 'ap.id_application')
                                ->leftJoin('users as u_rev', 'u_rev.id', '=', 'ap.user_apr_rej_id')
                                ->where('id_application', $application_id)
                                ->select(
                                    'ap.*',
                                    'at.is_normal',
                                    'at.is_past',
                                    'at.is_season_special',
                                    'tp.id_incidence_tp',
                                    'tp.incidence_tp_name',
                                    'cl.id_incidence_cl',
                                    'cl.incidence_cl_name',
                                    'u_rev.full_name_ui as revisor',
                                )
                                ->first();
        $lEvents = EmployeeVacationUtils::getEmployeeEvents(delegationUtils::getIdUser());
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento no es posible obtene el registro. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'oApplication' => $oApplication, 'lEvents' => $lEvents]);
    }

    public function deleteIncidence(Request $request){
        $application_id = $request->application_id;
        try {
            $application = Application::findOrFail($application_id);

            \DB::beginTransaction();

            $application->is_deleted = 1;
            $application->update();

            $lIncidences = $this->getIncidences($application->user_id);
            
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento no es posible eliminar el registro. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lIncidences' => $lIncidences]);
    }

    public function gestionSendIncidence(Request $request){
        try {
            $oApplication = Application::findOrFail($request->application_id);
            $confAuth = \DB::table('config_authorization as ca')
                            ->where('is_deleted', 0)
                            ->where('tp_incidence_id', $oApplication->type_incident_id)
                            ->get();

            $needAuth = null;
            if(count($confAuth) > 0){
                $oAuth = collect($confAuth);
                $confUser =  $oAuth->where('user_id', $oApplication->user_id)->first();
                $confOrgChart = $oAuth->where('org_chart_id', $oApplication->user_id)->first();
                $confCompany = $oAuth->where('company_id', $oApplication->user_id)->first();
                $needAuth = null;
                if($confUser != null){
                    $needAuth = $confUser->need_auth;
                }else if($confOrgChart != null){
                    $needAuth = $confOrgChart->need_auth;
                }else if($confCompany != null){
                    $needAuth = $confCompany->need_auth;
                }
            }else{
                $oType = \DB::table('cat_incidence_tps')
                            ->where('id_incidence_tp', $oApplication->type_incident_id)
                            ->first();

                $needAuth = null;
                if($oType->need_auth != 0){
                    $needAuth = 1;
                }
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento no es posible enviar el registro. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
        }

        if( is_null($needAuth) || $needAuth == 1){
            $result = $this->sendIncident($request);
        }else{
            $result = $this->sendAndAuthorize($request);
        }

        return $result;
    }

    public function sendIncident(Request $request){
        $application_id = $request->application_id;
        try {
            if(delegationUtils::getOrgChartJobIdUser() == 1){
                return json_encode(['success' => false, 'message' => 'No estás asignado a un área funcional, por favor contacta con el área de gestión humana', 'icon' => 'warning']);
            }
            \DB::beginTransaction();
            $application = Application::findOrFail($application_id);

            $result = incidencesUtils::checkVoboIsOpen($application->user_id, $application->start_date, $application->end_date);
            if($result->result == false){
                return json_encode(['success' => false, 'message' => $result->message, 'icon' => 'warning']);
            }

            $user = \DB::table('users')
                        ->where('id', $application->user_id)
                        ->first();

            // $superviser = orgChartUtils::getExistDirectSuperviserOrgChartJob($user->org_chart_job_id);
            $lSuperviser = orgChartUtils::getSupervisersToSend($user->org_chart_job_id);

            if(count($lSuperviser) == 0){
                \DB::rollBack();
                return json_encode(['success' => false, 'message' => 'No cuenta con un supervisor asignado en el sistema. Por favor, contacte con el área de gestion humana', 'icon' => 'error']);
            }

            $data = incidencesUtils::checkExternalIncident($application);
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

            $date = Carbon::now();
            $application->request_status_id = SysConst::APPLICATION_ENVIADO;
            $application->date_send_n = $date->toDateString();
            $application->send_default = isset($lSuperviser[0]->is_default);
            $application->requested_client = $request->requested_client;
            $application->update();

            foreach($lSuperviser as $superviser){
                $mailLog = new MailLog();
                $mailLog->date_log = Carbon::now()->toDateString();
                $mailLog->to_user_id = $superviser->id;
                $mailLog->application_id_n = $application->id_application;
                $mailLog->sys_mails_st_id = SysConst::MAIL_EN_PROCESO;
                $mailLog->type_mail_id = SysConst::MAIL_SOLICITUD_INCIDENCIA;
                $mailLog->is_deleted = 0;
                $mailLog->created_by = delegationUtils::getIdUser();
                $mailLog->updated_by = delegationUtils::getIdUser();
                $mailLog->save();
    
                $lIncidences = $this->getIncidences(delegationUtils::getIdUser());

                $type_incident = \DB::table('cat_incidence_tps')
                                    ->where('id_incidence_tp', $application->type_incident_id)
                                    ->value('incidence_tp_name');
    
                $data = new \stdClass;
                $data->user_id = null;
                $data->org_chart_job_id_n = $superviser->org_chart_job_id;
                $data->message = delegationUtils::getFullNameUI().' Tiene una solicitud de '.$type_incident;
                $data->url = route('requestIncidences_index', ['id' => $application->id_application]);
                $data->type_id = SysConst::NOTIFICATION_TYPE_INCIDENCIA;
                $data->priority = SysConst::NOTIFICATION_PRIORITY_INCIDENCIA;
                $data->icon = SysConst::NOTIFICATION_ICON_INCIDENCIA;
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
        $mypool[] = async(function () use ($application, $lSuperviser, $mailLog, $type_incident){

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
                    "title": "' . $full_name . '",
                    "body": "Envió solicitud de ' . mb_strtolower($type_incident, 'UTF-8') . '",
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
                // $arrUsers = $lUsers->map(function ($item) {
                //     return $item->institutional_mail;
                // })->toArray();

                foreach($lSuperviser as $sup){
                    Mail::to($sup->institutional_mail)->send(new requestIncidenceMail(
                                                            $application->id_application
                                                        )
                                                    );
                }

            } catch (\Throwable $th) {
				\Log::error($th); 
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

        return json_encode(['success' => true, 'lIncidences' => $lIncidences, 'mailLog_id' => $mailLog->id_mail_log]);
    }

    public function sendAndAuthorize(Request $request) {
        $application_id = $request->application_id;
        $authorized_client = $request->authorized_client;
        return $this->sendAndAuthorizeById($application_id, $authorized_client);
    }

    public function sendAndAuthorizeById($application_id, $authorized_client = null) {
        try {
            \DB::beginTransaction();

            $application = Application::findOrFail($application_id);

            $result = incidencesUtils::checkVoboIsOpen($application->user_id, $application->start_date, $application->end_date);
            if($result->result == false){
                return json_encode(['success' => false, 'message' => $result->message, 'icon' => 'warning']);
            }

            $date = Carbon::now();
            $application->request_status_id = SysConst::APPLICATION_APROBADO;
            $application->date_send_n = $date->toDateString();

            $application->user_apr_rej_id = delegationUtils::getIdUser();
            $application->approved_date_n = Carbon::now()->toDateString();
            $application->authorized_client = $authorized_client;
            $application->update();

            $system =  \DB::table('cat_incidence_tps')
                            ->where('id_incidence_tp', $application->type_incident_id)
                            ->first();

            if($system->interact_system_id == 3){
                $data = incidencesUtils::sendToCAP($application);
            }else{
                $data = incidencesUtils::sendIncidence($application);
            }

            if(!empty($data)){
                $data = json_decode($data);
                if($data->code == 500 || $data->code == 550){
                    \DB::rollBack();
                    return json_encode(['success' => false, 'message' => $data->message, 'icon' => 'error']);
                }
            }else{
                \DB::rollBack();
                if($system->interact_system_id == 3){
                    return json_encode(['success' => false, 'message' => 'No fue posible conectar con el sistema SIIE. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
                }else{
                    return json_encode(['success' => false, 'message' => 'No fue posible conectar con el sistema SIIE. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
                }
            }

            $lIncidences = $this->getIncidences($application->user_id);

            $employee = \DB::table('users')
                            ->where('id', $application->user_id)
                            ->first();

            $mailLog = new MailLog();
            $mailLog->date_log = Carbon::now()->toDateString();
            $mailLog->to_user_id = $employee->id;
            $mailLog->application_id_n = $application->id_application;
            $mailLog->sys_mails_st_id = SysConst::MAIL_EN_PROCESO;
            $mailLog->type_mail_id = SysConst::MAIL_REVISION_INCIDENCIA;
            $mailLog->is_deleted = 0;
            $mailLog->created_by = delegationUtils::getIdUser();
            $mailLog->updated_by = delegationUtils::getIdUser();
            $mailLog->save();
            
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible enviar y autorizar la solicitud debido a un error inesperado. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
        }

        $mypool = Pool::create();
        $mypool[] = async(function () use ($application, $employee, $mailLog){
            try {
                Mail::to($employee->institutional_mail)->send(new authorizeIncidenceMail(
                                                        $application->id_application
                                                    )
                                                );
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

        return json_encode(['success' => true, 'lIncidences' => $lIncidences, 'mailLog_id' => $mailLog->id_mail_log]);
    }

    public function getBirdthDayIncidences(Request $request){
        try {
            if(is_null($request->application_id)){
                $oUser = \DB::table('users')
                            ->where('id', $request->user_id)
                            ->first();
    
                $minYear = Carbon::parse($oUser->benefits_date)->format('Y');
                
                $lBirthDay = \DB::table('applications as a')
                                ->join('applications_breakdowns as ab', 'ab.application_id', '=', 'a.id_application')
                                ->where('a.user_id', $request->user_id)
                                ->where('a.type_incident_id', SysConst::TYPE_CUMPLEAÑOS)
                                ->whereIn('a.request_status_id', [SysConst::APPLICATION_APROBADO, SysConst::APPLICATION_CONSUMIDO])
                                ->where('a.is_deleted', 0)
                                ->orderBy('ab.application_year')
                                ->pluck('ab.application_year');
    
                $now = Carbon::createFromFormat('d-m', Carbon::now()->format('d-m'));
                $birthDay = Carbon::createFromFormat('d-m', Carbon::parse($oUser->birthday_n)->format('d-m'));
    
                if($now->gte($birthDay)){
                    $year = Carbon::now()->format('Y');
                }else{
                    $year = Carbon::now()->subYears(1)->format('Y');
                }
            }else{
                $oUser = \DB::table('users')
                            ->where('id', $request->user_id)
                            ->first();

                $minYear = Carbon::parse($oUser->benefits_date)->format('Y');
                
                $year = \DB::table('applications as a')
                            ->join('applications_breakdowns as ab', 'ab.application_id', '=', 'a.id_application')
                            ->where('a.id_application', $request->application_id)
                            ->value('ab.application_year');

                $lBirthDay = \DB::table('applications as a')
                            ->join('applications_breakdowns as ab', 'ab.application_id', '=', 'a.id_application')
                            ->where('a.user_id', $request->user_id)
                            ->where('a.type_incident_id', SysConst::TYPE_CUMPLEAÑOS)
                            ->where('a.is_deleted', 0)
                            ->where('a.id_application', '!=', $request->application_id)
                            ->orderBy('ab.application_year')
                            ->pluck('ab.application_year');
            }

        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible obtener el año de aplicación debido a un error inesperado. Por favor, verifique su conexión a internet e inténtelo de nuevo']);
        }

        return json_encode(['success' => true, 'lBirthDay' => $lBirthDay, 'birthDayYear' => $year, 'minYear' => $minYear]);
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
}
