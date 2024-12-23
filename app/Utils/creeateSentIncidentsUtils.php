<?php
namespace App\Utils;

use App\Http\Controllers\Pages\myVacationsController;
use App\Utils\EmployeeVacationUtils;
use App\Models\Vacations\Application;
use App\Models\Vacations\ApplicationsBreakdown;
use App\Models\Vacations\ApplicationVsTypes;
use App\Models\Vacations\ApplicationLog;
use App\Models\Vacations\MailLog;
use App\Constants\SysConst;
use Carbon\Carbon;
use Spatie\Async\Pool;
use Illuminate\Support\Facades\Mail;
use App\Mail\requestVacationMail;
use GuzzleHttp\Client;
use App\Mail\requestIncidenceMail;
use \App\Models\Permissions\Permission;
use App\Mail\requestPermissionMail;
use App\User;
use App\Models\Adm\Holiday;
use Carbon\CarbonPeriod;

class creeateSentIncidentsUtils
{
    public static function createVacation($requestVacation, $user, $vacations) {
        $employee_id = $user->id;
        $startDate = Carbon::parse($requestVacation->startDate)->format('Y-m-d');
        $endDate = Carbon::parse($requestVacation->endDate)->format('Y-m-d');
        $comments = $requestVacation->comments;
        $takedDays = $requestVacation->takedDays;
        $returnDate = $requestVacation->returnDate;
        $tot_calendar_days = $requestVacation->tot_calendar_days;
        
        foreach ($requestVacation->selectedDays as $oDay) {
            $lDays[] = Carbon::parse($oDay)->format('Y-m-d');
        }

        $take_holidays = $requestVacation->take_holidays;
        $take_rest_days = $requestVacation->take_rest_days;
        $requested_client = $requestVacation->requested_client;

        $arrApplicationsEA = EmployeeVacationUtils::getEmpApplicationsEA($employee_id);
        foreach ($arrApplicationsEA as $arr) {
            $isBetWeen = Carbon::parse($arr)->between($startDate, $endDate);
            if ($isBetWeen) {
                // crear expecion
                throw new \Exception('En la fecha ' . 
                        Carbon::parse($arr)->locale('es-ES')->isoFormat('ddd D-MMM-YYYY') . 
                        ' ya hay una solicitud de vacaciones registrada. Por favor, ingrese una fecha distinta para poder proseguir', 1);
            }
        }
        foreach ($user->applications as $ap) {
            if ($ap->request_status_id == 1) {
                throw new \Exception('No es posible generar una nueva solicitud de vacaciones si existen solicitudes pendientes de envío. Por favor, envíe o elimine las solicitudes pendientes antes de continuar', 1);
            }
        }
        if ($user->tot_vacation_remaining < $takedDays) {
            throw new \Exception('Actualmente no tienes los días de vacaciones solicitados. Por favor, ingresa un número menor de días de vacaciones para continuar, o si necesitas aclaraciones, consulta con el área de gestión humana', 1);   
        }

        $comments = str_replace(['"', "\\", "\r", "\n"], "", $comments);
        
        if ($comments == null || $comments == '') {
            throw new \Exception('Para proseguir, se requiere incluir un comentario en la solicitud', 1);
        }

        $vacations = collect($user->vacation)->sortBy('year');

        $oDays = json_decode(creeateSentIncidentsUtils::calclDays($user->id, $lDays, $startDate, $endDate));
        $lDays = $oDays->lDays;
        $takedDays = $oDays->takedDays;
        $tot_calendar_days = $oDays->tot_calendar_days;
        $returnDate = $oDays->return_day;

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
        $application->date_send_n = Carbon::now()->toDateString();
        $application->requested_client = $requested_client;
        $application->save();

        $lTemp = EmployeeVacationUtils::getEmployeeTempSpecial($user->org_chart_job_id, $user->id, $user->job_id);
        $lEvents = EmployeeVacationUtils::getEmployeeEvents($user->id);
        $typeVacation = json_decode(creeateSentIncidentsUtils::checkSpecial(
            $takedDays,
            $startDate,
            $endDate,
            $user->tot_vacation_remaining,
            $user->prop_vac_days,
            $lTemp,
            $lEvents
        ));

        $applicationVsType = new ApplicationVsTypes();
        $applicationVsType->application_id = $application->id_application;
        $applicationVsType->is_past = $typeVacation->is_past;
        $applicationVsType->is_advanced = $typeVacation->is_advanced;
        $applicationVsType->is_proportional = $typeVacation->is_proportional;
        $applicationVsType->is_season_special = $typeVacation->is_season_special;
        $applicationVsType->is_event = $typeVacation->is_event;
        $applicationVsType->is_recover_vacation = 0;

        foreach ($vacations as $vac) {
            if ($takedDays > 0) {
                $count = 0;
                if ($vac->remaining > 0) {
                    for ($i = 0; $i < $vac->remaining; $i++) {
                        $takedDays--;
                        $count++;
                        if ($takedDays == 0 || $takedDays < 0) {
                            break;
                        }
                    }
                    if ($vac->is_recovered) {
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
            } else {
                break;
            }
        }

        $applicationVsType->is_normal = !($typeVacation->is_past ||
            $typeVacation->is_advanced ||
            $typeVacation->is_proportional ||
            $typeVacation->is_season_special ||
            $applicationVsType->is_recover_vacation
        );
        $applicationVsType->save();

        $application_log = new ApplicationLog();
        $application_log->application_id = $application->id_application;
        $application_log->application_status_id = $application->request_status_id;
        $application_log->created_by = delegationUtils::getIdUser();
        $application_log->updated_by = delegationUtils::getIdUser();
        $application_log->save();

        return json_encode([
            'success' => true,
            'message' => 'Solicitud de vacaciones generada correctamente',
            'application' => $application
        ]);
    }

    public static function checkSpecial($takedDays, $startDate, $endDate, $tot_vacation_remaining, $prop_vac_days, $lTemp, $lEvents)
    {
        $is_normal = true;
        $is_past = false;
        $is_advanced = false;
        $is_proportional = false;
        $is_season_special = false;
        $is_event = false;
        $today = Carbon::today();

        if ($takedDays > $tot_vacation_remaining && $takedDays <= ($tot_vacation_remaining + $prop_vac_days)) {
            $is_normal = false;
            $is_proportional = true;
        }

        if ($takedDays > ($tot_vacation_remaining + $prop_vac_days)) {
            $is_normal = false;
            $is_advanced = true;
        }

        $endDateParsed = Carbon::parse($endDate);
        $startDateParsed = Carbon::parse($startDate);
        if ($endDateParsed->lessThanOrEqualTo($today) || $startDateParsed->lessThanOrEqualTo($today)) {
            $is_normal = false;
            $is_past = true;
        }

        foreach ($lTemp as $oSeason) {
            for ($i = 0; $i < count($oSeason->lDates); $i++) {
                if (Carbon::parse($oSeason->lDates[$i])->isBetween($startDateParsed, $endDateParsed)) {
                    $is_normal = false;
                    $is_season_special = true;
                }
            }
        }

        foreach ($lEvents as $oEvent) {
            for ($i = 0; $i < count($oEvent->lDates); $i++) {
                if (Carbon::parse($oEvent->lDates[$i])->isBetween($startDateParsed, $endDateParsed)) {
                    $is_normal = false;
                    $is_event = true;
                }
            }
        }

        return json_encode(['is_normal' => $is_normal, 'is_past' => $is_past, 'is_advanced' => $is_advanced, 'is_proportional' => $is_proportional, 'is_season_special' => $is_season_special, 'is_event' => $is_event]);
    }

    public static function sendVacation($user, $application_id) {
        $application = Application::where('id_application', $application_id)->first();

        if($user->org_chart_job_id == 1){
            throw new \Exception('No estás asignado a un área funcional, por favor contacta con el área de gestión humana', 1);
        }

        $oController = app(myVacationsController::class);
        $data = $oController->checkExternalIncident($application, json_decode($application->ldays));

        if(!empty($data)){
            $data = json_decode($data);
            if($data->code == 500 || $data->code == 550){
                \DB::rollBack();
                throw new \Exception($data->message, 1);
            }
        }else{
            \DB::rollBack();
            throw new \Exception('No fue posible conectar con el sistema SIIE. Por favor, verifique su conexión a internet e inténtelo de nuevo', 1);
        }

        if($application->request_status_id != SysConst::APPLICATION_CREADO){
            throw new \Exception('La solicitud que deseas enviar no tiene el estatus de CREADO. Solo se pueden enviar solicitudes con dicho estatus', 1);
            
        }

        $lSuperviser = orgChartUtils::getSupervisersToSend($user->org_chart_job_id);

        if(count($lSuperviser) == 0){
            \DB::rollBack();
            throw new \Exception('No cuenta con un supervisor asignado en el sistema. Por favor, contacte con el área de gestion humana', 1);
        }

        $oType = \DB::table('applications_vs_types')
                    ->where('application_id', $application->id_application)
                    ->first();

        // \DB::beginTransaction();
        if($oType->is_recover_vacation){
            recoveredVacationsUtils::insertUsedDays($application);
        }

        $date = Carbon::now();
        $application->request_status_id = SysConst::APPLICATION_ENVIADO;
        $application->date_send_n = $date->toDateString();
        $application->send_default = isset($lSuperviser[0]->is_default);
        $application->update();

        $application_log = new ApplicationLog();
        $application_log->application_id = $application->id_application;
        $application_log->application_status_id = $application->request_status_id;
        $application_log->created_by = \Auth::user()->id;
        $application_log->updated_by = \Auth::user()->id;
        $application_log->save();

        foreach($lSuperviser as $superviser){
            $mailLog = new MailLog();
            $mailLog->date_log = Carbon::now()->toDateString();
            $mailLog->to_user_id = $superviser->id;
            $mailLog->application_id_n = $application->id_application;
            $mailLog->sys_mails_st_id = SysConst::MAIL_EN_PROCESO;
            $mailLog->type_mail_id = SysConst::MAIL_SOLICITUD_VACACIONES;
            $mailLog->is_deleted = 0;
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

        return json_encode(
            [
                'success' => true,
                'message' => 'Solicitud de vacaciones enviada correctamente',
                'application' => $application,
                'user' => $user,
                'toUsers' => $lSuperviser,
                'oMailLog' => $mailLog
            ]
        );
    }

    public static function sendMail($application, $toUsers, $mailType, $id_mail_log ) {
        $mypool = Pool::create();
        $mypool[] = async(function () use ($application, $toUsers, $mailType, $id_mail_log ){
            try {
                $oMailLog = MailLog::find($id_mail_log);

                $arrUsers = $toUsers->map(function ($item) {
                    return $item->institutional_mail;
                })->toArray();

                $arrUsers = array_unique($arrUsers);

                switch ($mailType) {
                    case 'VAC':
                        foreach($toUsers as $sup){
                            Mail::to($sup->institutional_mail)->send(new requestVacationMail(
                                                                    $application->id_application,
                                                                    $application->user_id,
                                                                    $application->ldays,
                                                                    $application->return_date
                                                                )
                                                            );
                        }
                        break;
                    case 'INC':
                        foreach($toUsers as $sup){
                            Mail::to($sup->institutional_mail)->send(new requestIncidenceMail(
                                                                    $application->id_application
                                                                )
                                                            );
                        }
                        break;
                    case 'PER':
                        Mail::to($arrUsers)->send(new requestPermissionMail(
                                                    $application->id_hours_leave
                                                )
                                            );
                        break;
                    
                    default:
                        break;
                }

            } catch (\Throwable $th) {
                $oMailLog->sys_mails_st_id = SysConst::MAIL_NO_ENVIADO;
                $oMailLog->update();   
                return null; 
            }

            $oMailLog->sys_mails_st_id = SysConst::MAIL_ENVIADO;
            $oMailLog->update();

        })->then(function ($oMailLog) {
            
        })->catch(function ($oMailLog) {
            
        })->timeout(function ($oMailLog) {
            
        });
    }

    public static function sendAppNotification($fromUser, $toUsers, $message) {
        $mypool = Pool::create();
        $mypool[] = async(function () use ($fromUser, $toUsers, $message){
            try {
                $config = \App\Utils\Configuration::getConfigurations();
                $arrUsers = $toUsers->map(function ($item) {
                    return $item->id;
                })->toArray();
    
                $arrUsers = array_unique($arrUsers);
    
                $headers = [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'X-API-Key' => $config->apiKeyPghMobile
                ];
    
                // $full_name = $fromUser->short_name . ' ' . $fromUser->first_name . ' ' . $fromUser->last_name;
    
                $body = '{
                    "title": "' . $fromUser->full_name .  '",
                    "body": "' . $message . '",
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
        })->then(function ($oMailLog) {
            
        })->catch(function ($oMailLog) {
            
        })->timeout(function ($oMailLog) {
            
        });
    }

    public static function createIncidence($requestIncidence, $oUser) {
        $start_date = Carbon::parse($requestIncidence->startDate)->format('Y-m-d');
        $end_date = Carbon::parse($requestIncidence->endDate)->format('Y-m-d');
        $comments = $requestIncidence->comments;
        $takedDays = $requestIncidence->takedDays;
        $return_date = $requestIncidence->returnDate;
        $tot_calendar_days = $requestIncidence->tot_calendar_days;
        
        foreach ($requestIncidence->selectedDays as $oDay) {
            $lDays[] = Carbon::parse($oDay)->format('Y-m-d');
        }

        $take_holidays = false;
        $take_rest_days = false;
        $type_incident_id = $requestIncidence->incident_type_id;
        $requested_client = $requestIncidence->requested_client;

        $comments = str_replace(['"', "\\", "\r", "\n"], "", $comments);
        if($comments == null || $comments == ""){
            throw new \Exception('Para proseguir, se requiere incluir un comentario en la solicitud', 1);
        }

        $arrApplicationsEA = EmployeeVacationUtils::getEmpApplicationsEA($oUser->id);

        foreach($arrApplicationsEA as $arr){
            $isBetWeen = Carbon::parse($arr)->between($start_date, $end_date);
            if($isBetWeen){
                throw new \Exception('En la fecha '.Carbon::parse($arr)->locale('es-ES')->isoFormat('ddd D-MMM-YYYY').' ya hay una solicitud de vacaciones registrada. Por favor, ingrese una fecha distinta para poder proseguir', 1);
            }
        }

        $lTemp = EmployeeVacationUtils::getEmployeeTempSpecial($oUser->org_chart_job_id, $oUser->id, $oUser->job_id);
        $lEvents = EmployeeVacationUtils::getEmployeeEvents($oUser->id);
        $typeVacation = json_decode(creeateSentIncidentsUtils::checkSpecial(
            $takedDays,
            $start_date,
            $end_date,
            0,
            0,
            $lTemp,
            $lEvents
        ));

        $is_past = $typeVacation->is_past;
        $is_season_special = $typeVacation->is_season_special;
        $is_event = $typeVacation->is_event;

        $oDays = json_decode(creeateSentIncidentsUtils::calclDays($oUser->id, $lDays, $start_date, $end_date));
        $lDays = $oDays->lDays;
        $takedDays = $oDays->takedDays;
        $tot_calendar_days = $oDays->tot_calendar_days;
        $return_date = $oDays->return_day;

        $application = new Application();
        $application->folio_n = folioUtils::makeFolio(Carbon::now(), $oUser->id, $type_incident_id);
        $application->start_date = $start_date;
        $application->end_date = $end_date;
        $application->take_holidays = $take_holidays;
        $application->take_rest_days = $take_rest_days;
        $application->total_days = $takedDays;
        $application->tot_calendar_days = $tot_calendar_days;
        $application->return_date = $return_date;
        $application->ldays = json_encode($lDays);
        $application->user_id = $oUser->id;
        $application->request_status_id = SysConst::APPLICATION_CREADO;
        $application->type_incident_id = $type_incident_id;
        $application->emp_comments_n = $comments;
        $application->is_deleted = false;
        $application->requested_client = $requested_client;
        $application->save();

        if($type_incident_id == SysConst::TYPE_CUMPLEAÑOS){
            $appBreakdown = new ApplicationsBreakdown();
            $appBreakdown->application_id = $application->id_application;
            $appBreakdown->days_effective = 1;
            $appBreakdown->application_year = $requestIncidence->birthDayYear;
            $appBreakdown->admition_count = 1;
            $appBreakdown->save();
        }

        $applicationVsType = new ApplicationVsTypes();
        $applicationVsType->application_id = $application->id_application;
        $applicationVsType->is_past = $is_past;
        $applicationVsType->is_season_special = $is_season_special;
        $applicationVsType->is_event = $is_event;
        $applicationVsType->is_recover_vacation = 0;
        $applicationVsType->is_normal = !($is_past || $is_season_special);
        $applicationVsType->save();

        $application_log = new ApplicationLog();
        $application_log->application_id = $application->id_application;
        $application_log->application_status_id = $application->request_status_id;
        $application_log->created_by = delegationUtils::getIdUser();
        $application_log->updated_by = delegationUtils::getIdUser();
        $application_log->save();

        return json_encode([
            'success' => true,
            'message' => 'Incidencia generada correctamente',
            'application' => $application
        ]);
    }

    public static function sendIncidence($oUser, $application_id) {
        if(delegationUtils::getOrgChartJobIdUser() == 1){
            throw new \Exception('No estás asignado a un área funcional, por favor contacta con el área de gestión humana', 1);
        }

        $application = Application::findOrFail($application_id);
        $lSuperviser = orgChartUtils::getSupervisersToSend($oUser->org_chart_job_id);
        if(count($lSuperviser) == 0){
            \DB::rollBack();
            throw new \Exception('No cuenta con un supervisor asignado en el sistema. Por favor, contacte con el área de gestion humana', 1);
        }

        $data = incidencesUtils::checkExternalIncident($application);
        if(!empty($data)){
            $data = json_decode($data);
            if($data->code == 500 || $data->code == 550){
                \DB::rollBack();
                throw new \Exception($data->message, 1);    
            }
        }else{
            \DB::rollBack();
            throw new \Exception('No fue posible conectar con el sistema SIIE. Por favor, verifique su conexión a internet e inténtelo de nuevo', 1);
        }

        $date = Carbon::now();
        $application->request_status_id = SysConst::APPLICATION_ENVIADO;
        $application->date_send_n = $date->toDateString();
        $application->send_default = isset($lSuperviser[0]->is_default);
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

        return json_encode(
            [
                'success' => true,
                'message' => 'Solicitud de vacaciones enviada correctamente',
                'application' => $application,
                'type_incident' => $type_incident,
                'toUsers' => $lSuperviser,
                'oMailLog' => $mailLog
            ]
        );
    }

    public static function createPermission($requestPermission, $oUser) {
        $start_date = Carbon::createFromFormat('d/m/Y', $requestPermission->startDate)->format('Y-m-d');
        $comments = $requestPermission->comments;
        $class_id = $requestPermission->id_permission_cl;
        $type_id = $requestPermission->id_permission_tp;
        $employee_id = $oUser->id;
        $timeStart = $requestPermission->timeStart;
        $timeEnd = $requestPermission->timeEnd;
        $interOut = null;
        $interReturn = null;
        $requested_client = $requestPermission->requested_client;

        $comments = str_replace(['"', "\\", "\r", "\n"], "", $comments);
        if($comments == null || $comments == ""){
            throw new \Exception('Para proseguir, se requiere incluir un comentario en la solicitud', 1);
        }

        if(!is_null($timeStart) && !is_null($timeEnd)){
            $interOut = Carbon::parse($timeStart)->format('H:i');
            $interReturn = Carbon::parse($timeEnd)->format('H:i');
        }

        $permission = new Permission();
        $permission->folio_n = folioUtils::makeFolio(Carbon::now(), $employee_id, SysConst::TYPE_PERMISO_HORAS);
        $permission->start_date = $start_date;
        $permission->end_date = $start_date;
        $permission->total_days = 1;
        $permission->tot_calendar_days = 1;
        $permission->ldays = json_encode([$start_date]);
        $permission->minutes = creeateSentIncidentsUtils::calcMinutesTime($timeStart, $timeEnd);
        $permission->user_id = $employee_id;
        $permission->request_status_id = SysConst::APPLICATION_CREADO;
        $permission->type_permission_id = $type_id;
        $permission->cl_permission_id = $class_id;
        $permission->emp_comments_n = $comments;
        $permission->is_deleted = false;
        $permission->created_by = \Auth::user()->id;
        $permission->updated_by = \Auth::user()->id;
        $permission->intermediate_out = $interOut;
        $permission->intermediate_return = $interReturn;
        $permission->requested_client = $requested_client;
        $permission->save();

        return json_encode(['success' => true, 'permission' => $permission]);
    }

    public static function sendPermission($oUser, $permission_id) {
        if(delegationUtils::getOrgChartJobIdUser() == 1){
            throw new \Exception('No estás asignado a un área funcional, por favor contacta con el área de gestión humana', 1);
        }
        $permission = Permission::findOrFail($permission_id);

        $date = Carbon::now();
        $permission->request_status_id = SysConst::APPLICATION_ENVIADO;
        $permission->date_send_n = $date->toDateString();
        $permission->update();

        $superviser = orgChartUtils::getExistDirectSuperviserOrgChartJob($oUser->org_chart_job_id);
        $lSuperviser = orgChartUtils::getAllUsersByOrgChartJob($superviser->org_chart_job_id);

        $mailLog = new MailLog();
        $mailLog->date_log = Carbon::now()->toDateString();
        $mailLog->to_user_id = $superviser->id;
        $mailLog->hours_leave_id_n = $permission->id_hours_leave;
        $mailLog->sys_mails_st_id = SysConst::MAIL_EN_PROCESO;
        $mailLog->type_mail_id = SysConst::MAIL_SOLICITUD_PERMISO;
        $mailLog->is_deleted = 0;
        $mailLog->created_by = delegationUtils::getIdUser();
        $mailLog->updated_by = delegationUtils::getIdUser();
        $mailLog->save();

        $data = new \stdClass;
        $data->user_id = null;
        $data->org_chart_job_id_n = $superviser->org_chart_job_id;
        $data->message = delegationUtils::getFullNameUI().' Tiene una solicitud de permiso de horas';
        $data->url = route('requestPermission_index', ['id' => $permission->id_hours_leave]);
        $data->type_id = SysConst::NOTIFICATION_TYPE_PERMISO;
        $data->priority = SysConst::NOTIFICATION_PRIORITY_PERMISO;
        $data->icon = SysConst::NOTIFICATION_ICON_PERMISO;
        $data->row_type_id = SysConst::TYPE_PERMISO_HORAS;
        $data->row_id = $permission->id_hours_leave;
        $data->end_date = null;

        notificationsUtils::createNotification($data);

        return json_encode(
            [
                'success' => true,
                'message' => 'Solicitud de vacaciones enviada correctamente',
                'permission' => $permission,
                'toUsers' => $lSuperviser,
                'oMailLog' => $mailLog
            ]
        );
    }

    public static function calclDays($user_id, $arrDays, $start_date, $end_date){
        $oUser = User::findOrFail($user_id);

        // Validar formato de las fechas
        if (!Carbon::hasFormat($start_date, 'Y-m-d') || !Carbon::hasFormat($end_date, 'Y-m-d')) {
            throw new \Exception("Las fechas start_date y end_date deben estar en el formato Y-m-d.");
        }

        // Asegurarse de que las fechas sean válidas
        if (Carbon::parse($start_date)->gt(Carbon::parse($end_date))) {
            throw new \Exception("La fecha start_date no puede ser posterior a end_date.");
        }

        // Crear el rango de fechas
        $period = CarbonPeriod::create($start_date, $end_date);

        // Verificar que el rango no esté vacío
        if (iterator_count($period) === 0) {
            throw new \Exception("El rango de fechas está vacío.");
        }

        // Continuar con la lógica previa
        $lHolidays = Holiday::where('is_deleted', false)
                            ->where('fecha', '>=', $arrDays[0])
                            ->get();

        $holidayDates = $lHolidays->pluck('fecha')->toArray();
        $is_week = $oUser->payment_frec_id == 1;

        $days = [];
        foreach ($period as $date) {
            $isHoliday = in_array($date->format('Y-m-d'), $holidayDates);
            $bussinesDay = $is_week
                ? ($date->dayOfWeek != 0 && !$isHoliday)
                : ($date->dayOfWeek != 0 && $date->dayOfWeek != 6 && !$isHoliday);

            $days[] = [
                'date' => $date->format('Y-m-d'),
                'bussinesDay' => $bussinesDay,
                'taked' => in_array($date->format('Y-m-d'), $arrDays),
                'is_optional' => !$bussinesDay,
            ];
        }

        $takedDays = count($arrDays);
        $total_calendar_days = iterator_count($period);

        $return_day = Carbon::parse($end_date);
        for ($i = 0; $i < 30; $i++) {
            $return_day->addDay();
            if ($is_week && $return_day->dayOfWeek == 0) {
                continue;
            }
            if (!in_array($return_day->format('Y-m-d'), $holidayDates)) {
                break;
            }
        }

        return json_encode([
            'lDays' => $days,
            'takedDays' => $takedDays,
            'tot_calendar_days' => $total_calendar_days,
            'return_day' => $return_day->format('Y-m-d'),
        ]);
    }

    public static function calcMinutesTime ($timeStart, $timeEnd) {
        if (!$timeEnd) {
            $hora1 = Carbon::createFromFormat('H:i', '00:00');
            $hora2 = Carbon::createFromFormat('H:i', $timeStart);

            $minutos = $hora1->diffInMinutes($hora2);
        } else {
            $minutos = 0;
        }
        
        return $minutos;
    }
}