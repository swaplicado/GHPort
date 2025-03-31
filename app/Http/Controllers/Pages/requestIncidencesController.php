<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Mail\cancelIncidenceMail;
use Illuminate\Http\Request;
use \App\Utils\incidencesUtils;
use \App\Utils\delegationUtils;
use \App\Models\Vacations\Application;
use App\Models\Vacations\ApplicationLog;
use App\Utils\orgChartUtils;
use App\Constants\SysConst;
use App\Utils\EmployeeVacationUtils;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use App\Models\Vacations\MailLog;
use Spatie\Async\Pool;
use Illuminate\Support\Facades\Mail;
use App\Mail\authorizeIncidenceMail;
use App\Utils\notificationsUtils;
use App\Utils\CapLinkUtils;
use App\Utils\usersInSystemUtils;

class requestIncidencesController extends Controller
{
    public function index($idApplication = null){
        delegationUtils::getAutorizeRolUser([SysConst::JEFE, SysConst::ADMINISTRADOR, SysConst::GH]);
        $myManagers = orgChartUtils::getMyManagers(delegationUtils::getOrgChartJobIdUser());
        $org_chart_job_id = delegationUtils::getOrgChartJobIdUser();
        $lIncidences = incidencesUtils::getMyEmployeeslIncidences();

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

        // $lTemp_special = EmployeeVacationUtils::getEmployeeTempSpecial(delegationUtils::getOrgChartJobIdUser(), delegationUtils::getIdUser(), delegationUtils::getJobIdUser());
        $lTemp_special = [];
        $lEvents = [];

        $lHolidays = \DB::table('holidays')
                        ->where('fecha', '>', Carbon::now()->subDays(30)->toDateString())
                        ->where('is_deleted', 0)
                        ->pluck('fecha');

        // $lChildAreas = orgChartUtils::getAllChildsOrgChartJob($org_chart_job_id);
        $lChildAreas = orgChartUtils::getAllChildsToRevice($org_chart_job_id);
        //$lChildAreas = orgChartUtils::getAllChildsOrgChartJob($org_chart_job_id);

        $lEmployees = EmployeeVacationUtils::getlEmployees($lChildAreas);

        $ids = $lEmployees->pluck('id');

        $oApplication = null;
        $oUser = null;
        if($idApplication != null){
            $oApplication = \DB::table('applications as a')
                                ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
                                ->leftJoin('sys_applications_sts as ap_st', 'ap_st.id_applications_st', '=', 'a.request_status_id')
                                ->leftJoin('users as u_rev', 'u_rev.id', '=', 'a.user_apr_rej_id')
                                ->leftJoin('applications_vs_types as at', 'at.application_id', '=', 'a.id_application')
                                ->where('a.id_application', $idApplication)
                                ->where('a.is_deleted', 0)
                                ->where('a.type_incident_id', '!=', SysConst::TYPE_VACACIONES)
                                ->whereIn('a.request_status_id', [
                                    SysConst::APPLICATION_ENVIADO,
                                    SysConst::APPLICATION_APROBADO,
                                    SysConst::APPLICATION_RECHAZADO,
                                ])
                                ->whereIn('a.user_id', $ids)
                                ->select(
                                    'a.*',
                                    'at.is_normal',
                                    'at.is_past',
                                    'at.is_advanced',
                                    'at.is_proportional',
                                    'at.is_season_special',
                                    'at.is_recover_vacation',
                                    'u.birthday_n',
                                    'u.benefits_date',
                                    'u.payment_frec_id',
                                    'ap_st.applications_st_name',
                                    'u_rev.full_name_ui as revisor',
                                )
                                ->first();

            if($oApplication != null){
                $oUser = $lEmployees->where('id', $oApplication->user_id)->first();
                $lEvents = EmployeeVacationUtils::getEmployeeEvents($oApplication->user_id);
            }
        }

        $now = Carbon::now();
        $initialCalendarDate = $now->subMonths(1)->toDateString();

        $lRequestStatus = \DB::table('sys_applications_sts')
                        ->where('is_deleted', 0)
                        ->whereNotIn('id_applications_st', [SysConst::APPLICATION_CONSUMIDO, SysConst::APPLICATION_CREADO])
                        ->select(
                            'id_applications_st as id',
                            'applications_st_name as name'
                        )
                        ->get();

        $lGestionStatus = \DB::table('sys_applications_sts')
                        ->where('is_deleted', 0)
                        ->where('id_applications_st', '!=', SysConst::APPLICATION_CONSUMIDO)
                        ->select(
                            'id_applications_st as id',
                            'applications_st_name as name'
                        )
                        ->get();

        $lEmployees = usersInSystemUtils::FilterUsersInSystem($lEmployees, 'id');
        $lIncidences = usersInSystemUtils::FilterUsersInSystem($lIncidences, 'user_id');
        $myManagers = usersInSystemUtils::FilterUsersInSystem($myManagers, 'id');

        $config = \App\Utils\Configuration::getConfigurations();
        $authorized_client = $config->authorized_client_web;

        return view('Incidences.requestIncidences')->with('constants', $constants)
                                                    ->with('myManagers', $myManagers)
                                                    ->with('lIncidences', $lIncidences)
                                                    ->with('lClass', $lClass)
                                                    ->with('lTypes', $lTypes)
                                                    ->with('lTemp', $lTemp_special)
                                                    ->with('lHolidays', $lHolidays)
                                                    ->with('lEmployees', $lEmployees)
                                                    ->with('oApplication', $oApplication)
                                                    ->with('oUser', $oUser)
                                                    ->with('initialCalendarDate', $initialCalendarDate)
                                                    ->with('lRequestStatus', $lRequestStatus)
                                                    ->with('lGestionStatus', $lGestionStatus)
                                                    ->with('lEvents', $lEvents)
                                                    ->with('authorized_client', $authorized_client);
    }

    public function getEmployee(Request $request){
        try {
            $oUser = \DB::table('users as u')
                        ->leftJoin('users_vs_photos as up', 'up.user_id', '=', 'u.id')
                        ->where('u.id', $request->user_id)
                        ->select(
                            'u.*',
                            'up.photo_base64_n as photo64',
                        )
                        ->first();

            $from = Carbon::parse($oUser->benefits_date);
            $to = Carbon::today()->locale('es');
    
            $human = $to->diffForHumans($from, true, false, 6);
    
            $oUser->antiquity = $human;

            $lTemp_special = EmployeeVacationUtils::getEmployeeTempSpecial($oUser->org_chart_job_id, $oUser->id, $oUser->job_id);
            $lEvents = EmployeeVacationUtils::getEmployeeEvents($request->user_id);
            $lIncidences = incidencesUtils::getUserIncidences($oUser->id);
            foreach ($lIncidences as &$info) {
                // Verificar si el org_chart_job_id está en el array de directEmployeeIds
                
                    $info->is_direct = 0; // Si no está, no es empleado directo
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible obtener los datos del colaborador debido a un error inesperado. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'oUser' => $oUser, 'lTemp' => $lTemp_special, 'lIncidences' => $lIncidences, 'lEvents' => $lEvents]);
    }

    public function approbeIncidence(Request $request){
        try {
            \DB::beginTransaction();
            $application = Application::findOrFail($request->application_id);

            $result = incidencesUtils::checkVoboIsOpen($application->user_id, $application->start_date, $application->end_date);
            if($result->result == false){
                return json_encode(['success' => false, 'message' => $result->message, 'icon' => 'warning']);
            }

            if($application->request_status_id != SysConst::APPLICATION_ENVIADO){
                return json_encode(['success' => false, 'message' => 'La solicitud que deseas aprobar no tiene el estatus de "Por aprobar". Solo se pueden aprobar solicitudes con dicho estatus', 'icon' => 'warning']);
            }

            $comments = str_replace(['"', "\\", "\r", "\n"], "", $request->comments);

            $application->request_status_id = SysConst::APPLICATION_APROBADO;
            $application->user_apr_rej_id = delegationUtils::getIdUser();
            $application->approved_date_n = Carbon::now()->toDateString();
            $application->sup_comments_n = $comments;
            if($request->returnDate){
                $application->return_date = $request->returnDate;
            }

            // quitar caracteres especiales de los comentarios de empleado

            $application->emp_comments_n = str_replace(['"', "\\"], "", $application->emp_comments_n);
            $application->authorized_client = $request->authorized_client;
            $application->update();

            $application_log = new ApplicationLog();
            $application_log->application_id = $application->id_application;
            $application_log->application_status_id = $application->request_status_id;
            $application_log->created_by = delegationUtils::getIdUser();
            $application_log->updated_by = delegationUtils::getIdUser();
            $application_log->save();

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
                if($data->code == 500 || $data->code == 550 || $data->code == 400){
                    \DB::rollBack();
                    return json_encode(['success' => false, 'message' => $data->message, 'icon' => 'error']);
                }
            }else{
                \DB::rollBack();
                if($system->interact_system_id == 3){
                    return json_encode(['success' => false, 'message' => 'No fue posible conectar con el sistema CAP. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
                }else{
                    return json_encode(['success' => false, 'message' => 'No fue posible conectar con el sistema SIIE. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
                }
            }
            
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

            $org_chart_job_id = null;
            if(!is_null($request->manager_id)){
                $oManager = \DB::table('users')
                                ->where('id', $request->manager_id)
                                ->where('is_delete', 0)
                                ->where('is_active', 1)
                                ->first();

                $org_chart_job_id = !is_null($oManager) ? $oManager->org_chart_job_id : null;
            }

            if(is_null($org_chart_job_id)){
                $lIncidences = incidencesUtils::getMyEmployeeslIncidences();
            }else{
                $lIncidences = incidencesUtils::getMyManagerlIncidences($oManager->org_chart_job_id);
            }

            notificationsUtils::revisedNotificationFromAction($application->type_incident_id, $application->id_application);

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible aprobar la solicitud debido a un error inesperado. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
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

        $lIncidences = usersInSystemUtils::FilterUsersInSystem($lIncidences, 'user_id');
        return json_encode(['success' => true, 'message' => 'Incidencia autorizada con éxito', 'lIncidences' => $lIncidences, 'mailLog_id' => $mailLog->id_mail_log]);
    }

    public function rejectIncidence(Request $request){
        try {
            \DB::beginTransaction();
            $application = Application::findOrFail($request->application_id);

            if($application->request_status_id != SysConst::APPLICATION_ENVIADO){
                return json_encode(['success' => false, 'message' => 'La solicitud que deseas rechazar no tiene el estatus de "Por aprobar". Solo se pueden rechazar solicitudes con dicho estatus', 'icon' => 'warning']);
            }

            $comments = str_replace(['"', "\\", "\r", "\n"], "", $request->comments);

            $application->request_status_id = SysConst::APPLICATION_RECHAZADO;
            $application->user_apr_rej_id = delegationUtils::getIdUser();
            $application->rejected_date_n = Carbon::now()->toDateString();
            $application->sup_comments_n = $comments;
            if($request->returnDate){
                $application->return_date = $request->returnDate;
            }
            $application->authorized_client = $request->authorized_client;
            $application->update();

            $application_log = new ApplicationLog();
            $application_log->application_id = $application->id_application;
            $application_log->application_status_id = $application->request_status_id;
            $application_log->created_by = delegationUtils::getIdUser();
            $application_log->updated_by = delegationUtils::getIdUser();
            $application_log->save();

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

            $org_chart_job_id = null;
            if(!is_null($request->manager_id)){
                $oManager = \DB::table('users')
                                ->where('id', $request->manager_id)
                                ->where('is_delete', 0)
                                ->where('is_active', 1)
                                ->first();

                $org_chart_job_id = !is_null($oManager) ? $oManager->org_chart_job_id : null;
            }

            if(is_null($org_chart_job_id)){
                $lIncidences = incidencesUtils::getMyEmployeeslIncidences();
            }else{
                $lIncidences = incidencesUtils::getMyManagerlIncidences($oManager->org_chart_job_id);
            }

            notificationsUtils::revisedNotificationFromAction($application->type_incident_id, $application->id_application);

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible rechazar la solicitud debido a un error inesperado. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
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

        $lIncidences = usersInSystemUtils::FilterUsersInSystem($lIncidences, 'user_id');
        return json_encode(['success' => true, 'message' => 'Incidencia rechazada', 'lIncidences' => $lIncidences, 'mailLog_id' => $mailLog->id_mail_log]);
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

    public function getAllEmployees(){
        try {
            if(\Auth::user()->rol_id == SysConst::ADMINISTRADOR){
                $org_chart_job_id = 2;
            }else{
                $org_chart_job_id = delegationUtils::getOrgChartJobIdUser();
            }

            $lChildAreas = orgChartUtils::getAllChildsOrgChartJob($org_chart_job_id);

            $lEmployees = \DB::table('users')
                            ->where('is_active', 1)
                            ->where('is_delete', 0)
                            ->whereIn('org_chart_job_id', $lChildAreas)
                            ->select(
                                'id',
                                'full_name_ui as employee',
                            )
                            ->get();

        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible obtener los datos de los colaboradores debido a un error inesperado. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
        }

        $lEmployees = usersInSystemUtils::FilterUsersInSystem($lEmployees, 'id');
        return json_encode(['success' => true, 'lEmployees' => $lEmployees]);
    }

    public function seeLikeManager(Request $request){
        try {
            if(!is_null($request->manager_id)){
                $oManager = \DB::table('users')
                                ->where('id', $request->manager_id)
                                ->where('is_delete', 0)
                                ->where('is_active', 1)
                                ->first();
                            
                if(is_null($oManager)){
                    return json_encode(['success' => false, 'message' => 'En este momento no es posible encontrar al supervisor '.$request->manager_name.' en el sistema. Por favor verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
                }

                $lIncidences = incidencesUtils::getMyManagerlIncidences($oManager->org_chart_job_id);
            }else{
                $lIncidences = incidencesUtils::getMyEmployeeslIncidences();
            }

        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible obtener las solicitudes debido a un error inesperado. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
        }

        $lIncidences = usersInSystemUtils::FilterUsersInSystem($lIncidences, 'user_id');
        return json_encode(['success' => true, 'lIncidences' => $lIncidences]);
    }

    public function cancelIncidence(Request $request){
        try {
            $incidence_id = $request->application_id;
            $oIncidence = Application::findOrFail($incidence_id);

            $employee = \DB::table('users')
                            ->where('id', $oIncidence->user_id)
                            ->first();
    
            \DB::beginTransaction();
            $system =  \DB::table('cat_incidence_tps')
                                ->where('id_incidence_tp', $oIncidence->type_incident_id)
                                ->first();
    
            if($system->interact_system_id == 3){
                $data = json_decode(CapLinkUtils::cancelIncidenceCAP($oIncidence, 'INCIDENCE'));
            }else{
                $data = json_decode(CapLinkUtils::cancelIncidence($oIncidence));
            }
    
            if($data->code == 500 || $data->code == 550){
                \DB::rollBack();
                return json_encode(['success' => false, 'message' => $data->message, 'icon' => 'error']);
            }

            \DB::table('applications')
                ->where('id_application', $oIncidence->id_application)
                ->update(['request_status_id' => SysConst::APPLICATION_CANCELADO, 'user_apr_rej_id' => \Auth::user()->id ]);
    
            // $oIncidence->request_status_id = SysConst::APPLICATION_CANCELADO;
            // $oIncidence->user_apr_rej_id = \Auth::user()->id;
            // $oIncidence->update();

            $mailLog = new MailLog();
            $mailLog->date_log = Carbon::now()->toDateString();
            $mailLog->to_user_id = $employee->id;
            $mailLog->application_id_n = $oIncidence->id_application;
            $mailLog->sys_mails_st_id = SysConst::MAIL_EN_PROCESO;
            $mailLog->type_mail_id = SysConst::MAIL_CANCELACION_INCIDENCIA;
            $mailLog->is_deleted = 0;
            $mailLog->created_by = delegationUtils::getIdUser();
            $mailLog->updated_by = delegationUtils::getIdUser();
            $mailLog->save();
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }

        $org_chart_job_id = null;
        if(!is_null($request->manager_id)){
            $oManager = \DB::table('users')
                            ->where('id', $request->manager_id)
                            ->where('is_delete', 0)
                            ->where('is_active', 1)
                            ->first();

            $org_chart_job_id = !is_null($oManager) ? $oManager->org_chart_job_id : null;
        }

        if(is_null($org_chart_job_id)){
            $lIncidences = incidencesUtils::getMyEmployeeslIncidences();
        }else{
            $lIncidences = incidencesUtils::getMyManagerlIncidences($oManager->org_chart_job_id);
        }

        $mypool = Pool::create();
        $mypool[] = async(function () use ($oIncidence, $employee, $mailLog){
            try {
                Mail::to($employee->institutional_mail)->send(new cancelIncidenceMail(
                                                        $oIncidence->id_application,
                                                        $oIncidence->user_id,
                                                        \Auth::user()->id
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

        $lIncidences = usersInSystemUtils::FilterUsersInSystem($lIncidences, 'user_id');
        return json_encode(['success' => true, 'lIncidences' => $lIncidences, 'mailLog_id' => $mailLog->id_mail_log]);
    }

    public function deleteRequest(Request $request){
        delegationUtils::getAutorizeRolUser([SysConst::JEFE, SysConst::ADMINISTRADOR, SysConst::GH]);
        $application = Application::findOrFail($request->id_application);
        if(!$application->send_default){
            delegationUtils::getIsMyEmployeeUser($application->user_id);
        }

        try {
            \DB::beginTransaction();
            if($application->request_status_id != SysConst::APPLICATION_ENVIADO){
                return json_encode(['success' => false, 'message' => 'La solicitud que deseas rechazar no tiene el estatus de "Por aprobar". Solo se pueden rechazar solicitudes con dicho estatus', 'icon' => 'warning']);
            }

            $system = \DB::table('cat_incidence_tps')
                                ->where('id_incidence_tp', $application->type_incident_id)
                                ->first();
    
            $message = '';
            $oApp = clone $application;
            if($system->interact_system_id == 3){
                $result = incidencesUtils::checkIncidenceCAP($oApp);
                $message = 'No se encontró la incidencia en sistema CAP, 
                                el proceso siguiente es rechazar o aprobar la incidencia, no eliminarla';
            }else{
                $result = json_decode(incidencesUtils::checkExternalIncident($oApp));
                $message = 'No se encontró la incidencia en sistema SIIE, 
                                el proceso siguiente es rechazar o aprobar la incidencia, no eliminarla';
            }
            
            if($result->code == 550){
                $application->is_deleted = 1;
                $application->update();
            }else{
                return json_encode(['success' => false, 'message' => $message, 'icon' => 'warning']);
            }
            
            $org_chart_job_id = null;
            if(!is_null($request->manager_id)){
                $oManager = \DB::table('users')
                                ->where('id', $request->manager_id)
                                ->where('is_delete', 0)
                                ->where('is_active', 1)
                                ->first();

                $org_chart_job_id = !is_null($oManager) ? $oManager->org_chart_job_id : null;
            }

            if(is_null($org_chart_job_id)){
                $lIncidences = incidencesUtils::getMyEmployeeslIncidences();
            }else{
                $lIncidences = incidencesUtils::getMyManagerlIncidences($oManager->org_chart_job_id);
            }

            \DB::commit();
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }

        $lIncidences = usersInSystemUtils::FilterUsersInSystem($lIncidences, 'user_id');
        return json_encode(['success' => true, 'lIncidences' => $lIncidences]);
    }
}
