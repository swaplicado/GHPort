<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Mail\cancelIncidenceMail;
use App\Mail\cancelPermissionMail;
use App\Utils\CapLinkUtils;
use Illuminate\Http\Request;
use App\Utils\permissionsUtils;
use \App\Utils\EmployeeVacationUtils;
use \App\Constants\SysConst;
use \App\Utils\delegationUtils;
use Carbon\Carbon;
use \App\Models\Permissions\Permission;
use \App\Utils\folioUtils;
use App\Utils\orgChartUtils;
use Spatie\Async\Pool;
use Illuminate\Support\Facades\Mail;
use App\Mail\authorizePermissionMail;
use App\Models\Vacations\MailLog;
use \App\Utils\incidencesUtils;
use App\Utils\notificationsUtils;
use App\Utils\usersInSystemUtils;

class requestPermissionController extends Controller
{
    public function index($permission_id = null){
        delegationUtils::getAutorizeRolUser([SysConst::JEFE, SysConst::ADMINISTRADOR, SysConst::GH]);
        $myManagers = orgChartUtils::getMyManagers(delegationUtils::getOrgChartJobIdUser());
        $org_chart_job_id = delegationUtils::getOrgChartJobIdUser();
        $lPermissions = permissionsUtils::getMyEmployeeslPermissions(2);
        $clase_permiso = 2;

        $constants = [
            'SEMANA' => SysConst::SEMANA,
            'QUINCENA' => SysConst::QUINCENA,
            'APPLICATION_CREADO' => SysConst::APPLICATION_CREADO,
            'APPLICATION_ENVIADO' => SysConst::APPLICATION_ENVIADO,
            'APPLICATION_RECHAZADO' => SysConst::APPLICATION_RECHAZADO,
            'APPLICATION_APROBADO' => SysConst::APPLICATION_APROBADO,
        ];

        $lTypes = \DB::table('cat_permission_tp')
                        ->where('is_deleted', 0)
                        ->where('is_active', 1)
                        ->get();
        
        $lClass = \DB::table('permission_cl')
                        ->where('is_deleted', 0)
                        ->where('is_active', 1)
                        ->where('id_permission_cl',2)
                        ->get();

        $lHolidays = \DB::table('holidays')
                        ->where('fecha', '>', Carbon::now()->subDays(30)->toDateString())
                        ->where('is_deleted', 0)
                        ->pluck('fecha');

        $lTemp_special = [];
        $lEvents = [];

        $lChildAreas = orgChartUtils::getAllChildsOrgChartJob($org_chart_job_id);

        $lEmployees = EmployeeVacationUtils::getlEmployees($lChildAreas);

        $ids = $lEmployees->pluck('id');

        $oPermission = null;
        $oUser = null;
        if($permission_id != null){
            $oPermission = \DB::table('hours_leave')
                        ->where('id_hours_leave', $permission_id)
                        ->whereIn('user_id', $ids)
                        ->first();

            if($oPermission != null){
                $result = permissionsUtils::convertMinutesToHours($oPermission->minutes);
                $oPermission->hours = $result[0];
                $oPermission->min = $result[1];
                $oUser = $lEmployees->where('id', $oPermission->user_id)->first();
            }

            if($oPermission != null){
                $oUser = $lEmployees->where('id', $oPermission->user_id)->first();
            }
        }

        $config = \App\Utils\Configuration::getConfigurations();

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
        $lPermissions = usersInSystemUtils::FilterUsersInSystem($lPermissions, 'user_id');
        $myManagers = usersInSystemUtils::FilterUsersInSystem($myManagers, 'id');

        $authorized_client = $config->authorized_client_web;
        if($clase_permiso == SysConst::PERMISO_LABORAL){
            $permission_time = $config->permission_time_work;
        }else{
            $permission_time = $config->permission_time;
        }

        return view('permissions.requestPermissions')->with('lPermissions', $lPermissions)
                                            ->with('constants', $constants)
                                            ->with('lTypes', $lTypes)
                                            ->with('lClass', $lClass)
                                            ->with('lHolidays', $lHolidays)
                                            ->with('lTemp', $lTemp_special)
                                            ->with('lEvents', $lEvents)
                                            ->with('oPermission', $oPermission)
                                            ->with('oUser', $oUser)
                                            ->with('lEmployees', $lEmployees)
                                            ->with('permission_time', $permission_time)
                                            ->with('myManagers', $myManagers)
                                            ->with('clase_permiso', $clase_permiso)
                                            ->with('initialCalendarDate', $initialCalendarDate)
                                            ->with('lRequestStatus', $lRequestStatus)
                                            ->with('lGestionStatus', $lGestionStatus)
                                            ->with('authorized_client', $authorized_client);
    }

    public function PersonalTheme($permission_id = null){
        delegationUtils::getAutorizeRolUser([SysConst::JEFE, SysConst::ADMINISTRADOR, SysConst::GH]);
        $myManagers = orgChartUtils::getMyManagers(delegationUtils::getOrgChartJobIdUser());
        $org_chart_job_id = delegationUtils::getOrgChartJobIdUser();
        $lPermissions = permissionsUtils::getMyEmployeeslPermissions(1);
        $clase_permiso = 1;

        $constants = [
            'SEMANA' => SysConst::SEMANA,
            'QUINCENA' => SysConst::QUINCENA,
            'APPLICATION_CREADO' => SysConst::APPLICATION_CREADO,
            'APPLICATION_ENVIADO' => SysConst::APPLICATION_ENVIADO,
            'APPLICATION_RECHAZADO' => SysConst::APPLICATION_RECHAZADO,
            'APPLICATION_APROBADO' => SysConst::APPLICATION_APROBADO,
        ];

        $lTypes = \DB::table('cat_permission_tp')
                        ->where('is_deleted', 0)
                        ->where('is_active', 1)
                        ->get();
        
        $lClass = \DB::table('permission_cl')
                        ->where('is_deleted', 0)
                        ->where('is_active', 1)
                        ->where('id_permission_cl',1)
                        ->get();

        $lHolidays = \DB::table('holidays')
                        ->where('fecha', '>', Carbon::now()->subDays(30)->toDateString())
                        ->where('is_deleted', 0)
                        ->pluck('fecha');

        $lTemp_special = [];
        $lEvents = [];

        $lChildAreas = orgChartUtils::getAllChildsOrgChartJob($org_chart_job_id);

        $lEmployees = EmployeeVacationUtils::getlEmployees($lChildAreas);

        $ids = $lEmployees->pluck('id');

        $oPermission = null;
        $oUser = null;
        if($permission_id != null){
            $oPermission = \DB::table('hours_leave')
                        ->where('id_hours_leave', $permission_id)
                        ->whereIn('user_id', $ids)
                        ->first();

            if($oPermission != null){
                $result = permissionsUtils::convertMinutesToHours($oPermission->minutes);
                $oPermission->hours = $result[0];
                $oPermission->min = $result[1];
                $oUser = $lEmployees->where('id', $oPermission->user_id)->first();
            }

            if($oPermission != null){
                $oUser = $lEmployees->where('id', $oPermission->user_id)->first();
            }
        }

        $config = \App\Utils\Configuration::getConfigurations();

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
        $lPermissions = usersInSystemUtils::FilterUsersInSystem($lPermissions, 'user_id');
        $myManagers = usersInSystemUtils::FilterUsersInSystem($myManagers, 'id');

        $authorized_client = $config->authorized_client_web;
        if($clase_permiso == SysConst::PERMISO_LABORAL){
            $permission_time = $config->permission_time_work;
        }else{
            $permission_time = $config->permission_time;
        }

        return view('permissions.requestPermissions')->with('lPermissions', $lPermissions)
                                            ->with('constants', $constants)
                                            ->with('lTypes', $lTypes)
                                            ->with('lClass', $lClass)
                                            ->with('lHolidays', $lHolidays)
                                            ->with('lTemp', $lTemp_special)
                                            ->with('lEvents', $lEvents)
                                            ->with('oPermission', $oPermission)
                                            ->with('oUser', $oUser)
                                            ->with('lEmployees', $lEmployees)
                                            ->with('permission_time', $permission_time)
                                            ->with('myManagers', $myManagers)
                                            ->with('clase_permiso', $clase_permiso)
                                            ->with('initialCalendarDate', $initialCalendarDate)
                                            ->with('lRequestStatus', $lRequestStatus)
                                            ->with('lGestionStatus', $lGestionStatus)
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
            $lEvents = EmployeeVacationUtils::getEmployeeEvents($oUser->id);
            if( isset($request->cl) ){
                $lPermissions = permissionsUtils::getUserPermissions($oUser->id,$request->cl);    
            }else{
                $lPermissions = permissionsUtils::getUserPermissions($oUser->id);
            }

            foreach ($lPermissions as &$info) {
                // Verificar si el org_chart_job_id está en el array de directEmployeeIds
                
                $info->is_direct = 1; // Si no está, no es empleado directo
            }
            
            $lSchedule = \DB::table('schedule_template as st')
                        ->join('schedule_day as sd', 'sd.schedule_template_id', '=', 'st.id')
                        ->where('st.id', $oUser->schedule_template_id)
                        ->where('sd.is_working', 1)
                        ->where('sd.is_deleted', 0)
                        ->select(
                            'st.name',
                            'sd.day_name',
                            'sd.day_num',
                            \DB::raw("DATE_FORMAT(sd.entry, '%H:%i') as entry"),
                            \DB::raw("DATE_FORMAT(sd.departure, '%H:%i') as departure")
                        )
                        ->get();

            foreach($lSchedule as $sc){
                $sc->entry = Carbon::parse($sc->entry)->format('g:i A');
                $sc->departure = Carbon::parse($sc->departure)->format('g:i A');
            }
            
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 
            'oUser' => $oUser, 
            'lTemp' => $lTemp_special, 
            'lEvents' => $lEvents, 
            'lPermissions' => $lPermissions, 
            'lSchedule' => $lSchedule
        ]);
    }

    public function approbePermission(Request $request){
        try {
            \DB::beginTransaction();
            $permission = Permission::findOrFail($request->permission_id);

            // $result = incidencesUtils::checkVoboIsOpen($permission->user_id, $permission->start_date, $permission->end_date);
            // if($result->result == false){
            //     return json_encode(['success' => false, 'message' => $result->message, 'icon' => 'warning']);
            // }

            if($permission->request_status_id != SysConst::APPLICATION_ENVIADO){
                return json_encode(['success' => false, 'message' => 'La solicitud que deseas aprobar no tiene el estatus de "Por aprobar". Solo se pueden aprobar solicitudes con dicho estatus', 'icon' => 'warning']);
            }

            $comments = str_replace(['"', "\\", "\r", "\n"], "", $request->comments);

            $permission->request_status_id = SysConst::APPLICATION_APROBADO;
            $permission->user_apr_rej_id = delegationUtils::getIdUser();
            $permission->approved_date_n = Carbon::now()->toDateString();
            $permission->sup_comments_n = $comments;
            $permission->authorized_client = $request->authorized_client;
            $permission->update();

            $config = \App\Utils\Configuration::getConfigurations();
            $lPermissionConfig = collect($config->hours_leave_interact_sys);

            $oPerConfig = $lPermissionConfig->where('type_id', $permission->type_permission_id)->first();

            if($oPerConfig->sys_id == SysConst::CAP){
                $data = permissionsUtils::sendPermissionToCAP($permission);
                if($data->status != 'Success'){
                    \DB::rollBack();
                    return json_encode(['success' => false, 'message' => $data->message, 'icon' => 'error']);
                }
            }

            $employee = \DB::table('users')
                            ->where('id', $permission->user_id)
                            ->first();

            $mailLog = new MailLog();
            $mailLog->date_log = Carbon::now()->toDateString();
            $mailLog->to_user_id = $employee->id;
            $mailLog->hours_leave_id_n = $permission->id_hours_leave;
            $mailLog->sys_mails_st_id = SysConst::MAIL_EN_PROCESO;
            $mailLog->type_mail_id = SysConst::MAIL_REVISION_PERMISO;
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
                $lPermissions = permissionsUtils::getMyEmployeeslPermissions($permission->cl_permission_id);
            }else{
                $lPermissions = permissionsUtils::getMyManagerlPermissions($oManager->org_chart_job_id, $permission->cl_permission_id);
            }
            
            notificationsUtils::revisedNotificationFromAction(SysConst::TYPE_PERMISO_HORAS, $permission->id_hours_leave);

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error']);
        }

        $mypool = Pool::create();
        $mypool[] = async(function () use ($permission, $employee, $mailLog){
            try {
                Mail::to($employee->institutional_mail)->send(new authorizePermissionMail(
                                                                    $permission->id_hours_leave
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
        })->then(function () {
            
        })->catch(function () {
            
        })->timeout(function () {
            
        });

        $lPermissions = usersInSystemUtils::FilterUsersInSystem($lPermissions, 'user_id');
        return json_encode(['success' => true, 'message' => 'Permiso autorizado con éxito', 'lPermissions' => $lPermissions, 'mailLog_id' => $mailLog->id_mail_log]);
    }

    public function rejectPermission(Request $request){
        try {
            \DB::beginTransaction();
            $permission = Permission::findOrFail($request->permission_id);

            if($permission->request_status_id != SysConst::APPLICATION_ENVIADO){
                return json_encode(['success' => false, 'message' => 'La solicitud que deseas rechazar no tiene el estatus de "Por aprobar". Solo se pueden rechazar solicitudes con dicho estatus', 'icon' => 'warning']);
            }

            $comments = str_replace(['"', "\\", "\r", "\n"], "", $request->comments);

            $permission->request_status_id = SysConst::APPLICATION_RECHAZADO;
            $permission->user_apr_rej_id = delegationUtils::getIdUser();
            $permission->rejected_date_n = Carbon::now()->toDateString();
            $permission->sup_comments_n = $comments;
            $permission->authorized_client = $request->authorized_client;
            $permission->update();

            // $data = incidencesUtils::sendToCAP($application);
            
            $employee = \DB::table('users')
                            ->where('id', $permission->user_id)
                            ->first();

            $mailLog = new MailLog();
            $mailLog->date_log = Carbon::now()->toDateString();
            $mailLog->to_user_id = $employee->id;
            $mailLog->hours_leave_id_n = $permission->id_hours_leave;
            $mailLog->sys_mails_st_id = SysConst::MAIL_EN_PROCESO;
            $mailLog->type_mail_id = SysConst::MAIL_REVISION_PERMISO;
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
                $lPermissions = permissionsUtils::getMyEmployeeslPermissions($permission->cl_permission_id);
            }else{
                $lPermissions = permissionsUtils::getMyManagerlPermissions($oManager->org_chart_job_id, $permission->cl_permission_id);
            }

            notificationsUtils::revisedNotificationFromAction(SysConst::TYPE_PERMISO_HORAS, $permission->id_hours_leave);

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error']);
        }

        $mypool = Pool::create();
        $mypool[] = async(function () use ($permission, $employee, $mailLog){
            try {
                Mail::to($employee->institutional_mail)->send(new authorizePermissionMail(
                                                                    $permission->id_hours_leave
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
        })->then(function () {
            
        })->catch(function () {
            
        })->timeout(function () {
            
        });

        $lPermissions = usersInSystemUtils::FilterUsersInSystem($lPermissions, 'user_id');
        return json_encode(['success' => true, 'message' => 'Permiso rechazado', 'lPermissions' => $lPermissions, 'mailLog_id' => $mailLog->id_mail_log]);
    }

    public function getDirectEmployees(Request $request){
        try {
            if(\Auth::user()->rol_id == SysConst::ADMINISTRADOR){
                $org_chart_job_id = 2;
            }else{
                $org_chart_job_id = delegationUtils::getOrgChartJobIdUser();
            }
            $arrOrgJobs = orgChartUtils::getDirectChildsOrgChartJob($org_chart_job_id);
            $lEmployees = EmployeeVacationUtils::getlEmployees($arrOrgJobs);
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error']);
        }

        $lEmployees = usersInSystemUtils::FilterUsersInSystem($lEmployees, 'id');
        return json_encode(['success' => true, 'lEmployees' => $lEmployees ]);
    }

    public function getAllEmployees(Request $request){
        try {
            if(\Auth::user()->rol_id == SysConst::ADMINISTRADOR){
                $org_chart_job_id = 2;
            }else{
                $org_chart_job_id = delegationUtils::getOrgChartJobIdUser();
            }

            $lChildAreas = orgChartUtils::getAllChildsOrgChartJob($org_chart_job_id);

            $lEmployees = EmployeeVacationUtils::getlEmployees($lChildAreas);

        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error']);
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

                $lPermissions = permissionsUtils::getMyManagerlPermissions($oManager->org_chart_job_id, $request->cl);
            }else{
                $lPermissions = permissionsUtils::getMyEmployeeslPermissions($request->cl);
            }

        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error']);
        }

        $lPermissions = usersInSystemUtils::FilterUsersInSystem($lPermissions, 'user_id');
        return json_encode(['success' => true, 'lPermissions' => $lPermissions]);
    }

    public function cancelPermission(Request $request){
        try {
            $incidence_id = $request->application_id;
            $oIncidence = Permission::findOrFail($incidence_id);

            $employee = \DB::table('users')
                            ->where('id', $oIncidence->user_id)
                            ->first();
    
            \DB::beginTransaction();
            $system =  \DB::table('cat_incidence_tps')
                                ->where('id_incidence_tp', $oIncidence->type_incident_id)
                                ->first();
    
            $data = json_decode(CapLinkUtils::cancelIncidenceCAP($oIncidence, 'PERMISO'));
    
            if($data->code == 500 || $data->code == 550){
                \DB::rollBack();
                return json_encode(['success' => false, 'message' => $data->message, 'icon' => 'error']);
            }
    
            \DB::table('hours_leave')
                ->where('id_hours_leave', $oIncidence->id_hours_leave)
                ->update(['request_status_id' => SysConst::APPLICATION_CANCELADO, 'user_apr_rej_id' => \Auth::user()->id ]);

            // $oIncidence->request_status_id = SysConst::APPLICATION_CANCELADO;
            // $oIncidence->user_apr_rej_id = \Auth::user()->id;
            // $oIncidence->update();

            $mailLog = new MailLog();
            $mailLog->date_log = Carbon::now()->toDateString();
            $mailLog->to_user_id = $employee->id;
            $mailLog->hours_leave_id_n = $oIncidence->id_hours_leave;
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
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error']);
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
            $lPermissions = permissionsUtils::getMyEmployeeslPermissions($oIncidence->cl_permission_id);
        }else{
            $lPermissions = permissionsUtils::getMyManagerlPermissions($oManager->org_chart_job_id,$oIncidence->cl_permission_id);
        }

        $mypool = Pool::create();
        $mypool[] = async(function () use ($oIncidence, $employee, $mailLog){
            try {
                Mail::to($employee->institutional_mail)->send(new cancelPermissionMail(
                                                        $oIncidence->id_hours_leave,
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

        $lPermissions = usersInSystemUtils::FilterUsersInSystem($lPermissions, 'user_id');
        return json_encode(['success' => true, 'lPermissions' => $lPermissions, 'mailLog_id' => $mailLog->id_mail_log]);
    }

    public function deletePermission(Request $request){
        try {
            $permission_id = $request->permission_id;
            $oPermission = Permission::findOrFail($permission_id);

            \DB::beginTransaction();
    
            if($oPermission->type_permission_id != SysConst::PERMISO_INTERMEDIO){
                $oPer = clone $oPermission;
                $result = json_decode(permissionsUtils::checkExistPermission($oPer));
        
                if($result->success){
                    $data = $result->data;
                    if($data->code == 550){
                        $oPermission->is_deleted = 1;
                        $oPermission->update();
                    }else{
                        return json_encode(['success' => false, 'message' => 'No se encontró el permiso en el sistema CAP, 
                        el proceso siguiente es rechazar o aprobar la solicitud, no eliminarla', 'icon' => 'info']);
                    }
                }
            }else{
                $oPermission->is_deleted = 1;
                $oPermission->update();
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
                $lPermissions = permissionsUtils::getMyEmployeeslPermissions($oPermission->cl_permission_id);
            }else{
                $lPermissions = permissionsUtils::getMyManagerlPermissions($oManager->org_chart_job_id,$oPermission->cl_permission_id);
            }

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }

        $lPermissions = usersInSystemUtils::FilterUsersInSystem($lPermissions, 'user_id');
        return json_encode(['success' => true, 'lPermissions' => $lPermissions]);
    }
}
