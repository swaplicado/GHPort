<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
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

class requestIncidencesController extends Controller
{
    public function index($idApplication = null){
        delegationUtils::getAutorizeRolUser([SysConst::JEFE, SysConst::ADMINISTRADOR]);
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

        $lHolidays = \DB::table('holidays')
                        ->where('fecha', '>', Carbon::now()->subDays(30)->toDateString())
                        ->where('is_deleted', 0)
                        ->pluck('fecha');

        $lChildAreas = orgChartUtils::getAllChildsOrgChartJob($org_chart_job_id);

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
            }
        }

        $now = Carbon::now();
        $initialCalendarDate = $now->subMonths(1)->toDateString();

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
                                                    ->with('myManagers', $myManagers)
                                                    ->with('initialCalendarDate', $initialCalendarDate);
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

            $lIncidences = incidencesUtils::getUserIncidences($oUser->id);
        } catch (\Throwable $th) {
            return json_encode(['sucess' => false, 'message' => 'Error al obtener al colaborador', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'oUser' => $oUser, 'lTemp' => $lTemp_special, 'lIncidences' => $lIncidences]);
    }

    public function approbeIncidence(Request $request){
        try {
            \DB::beginTransaction();
            $application = Application::findOrFail($request->application_id);

            if($application->request_status_id != SysConst::APPLICATION_ENVIADO){
                return json_encode(['success' => false, 'message' => 'Solo se pueden aprobar solicitudes nuevas', 'icon' => 'warning']);
            }

            $application->request_status_id = SysConst::APPLICATION_APROBADO;
            $application->user_apr_rej_id = delegationUtils::getIdUser();
            $application->approved_date_n = Carbon::now()->toDateString();
            $application->sup_comments_n = $request->comments;
            $application->return_date = $request->returnDate;
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
                if($data->code == 500 || $data->code == 550){
                    \DB::rollBack();
                    return json_encode(['success' => false, 'message' => $data->message, 'icon' => 'error']);
                }
            }else{
                \DB::rollBack();
                return json_encode(['success' => false, 'message' => 'Error al revisar la incidencia con siie', 'icon' => 'error']);
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
            return json_encode(['sucess' => false, 'message' => 'Error al aprobar la incidencia', 'icon' => 'error']);
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

    public function rejectIncidence(Request $request){
        try {
            \DB::beginTransaction();
            $application = Application::findOrFail($request->application_id);

            if($application->request_status_id != SysConst::APPLICATION_ENVIADO){
                return json_encode(['success' => false, 'message' => 'Solo se pueden aprobar solicitudes nuevas', 'icon' => 'warning']);
            }

            $application->request_status_id = SysConst::APPLICATION_RECHAZADO;
            $application->user_apr_rej_id = delegationUtils::getIdUser();
            $application->rejected_date_n = Carbon::now()->toDateString();
            $application->sup_comments_n = $request->comments;
            $application->return_date = $request->returnDate;
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
            return json_encode(['success' => false, 'message' => 'Error al rechazar la incidencia', 'icon' => 'error']);
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

    public function checkMail(Request $request){
        $mailLog = MailLog::find($request->mail_log_id);

        return json_encode(['sucess' => true, 'status' => $mailLog->sys_mails_st_id]);
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
                                'full_name_ui as text',
                            )
                            ->get();

        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al obtener a los colaboradores', 'icon' => 'error']);
        }

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
                    return json_encode(['success' => false, 'message' => 'No se encontro al supervisor '.$request->manager_name, 'icon' => 'error']);
                }

                $lIncidences = incidencesUtils::getMyManagerlIncidences($oManager->org_chart_job_id);
            }else{
                $lIncidences = incidencesUtils::getMyEmployeeslIncidences();
            }

        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al obtener las incidencias', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lIncidences' => $lIncidences]);
    }
}
