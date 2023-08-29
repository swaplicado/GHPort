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

class requestPermissionController extends Controller
{
    public function index($permission_id = null){
        delegationUtils::getAutorizeRolUser([SysConst::JEFE, SysConst::ADMINISTRADOR]);
        $myManagers = orgChartUtils::getMyManagers(delegationUtils::getOrgChartJobIdUser());
        $org_chart_job_id = delegationUtils::getOrgChartJobIdUser();
        $lPermissions = permissionsUtils::getMyEmployeeslPermissions();

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
                        ->get();

        $lHolidays = \DB::table('holidays')
                        ->where('fecha', '>', Carbon::now()->subDays(30)->toDateString())
                        ->where('is_deleted', 0)
                        ->pluck('fecha');

        $lTemp_special = [];

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

        return view('permissions.requestPermissions')->with('lPermissions', $lPermissions)
                                            ->with('constants', $constants)
                                            ->with('lTypes', $lTypes)
                                            ->with('lClass', $lClass)
                                            ->with('lHolidays', $lHolidays)
                                            ->with('lTemp', $lTemp_special)
                                            ->with('oPermission', $oPermission)
                                            ->with('oUser', $oUser)
                                            ->with('lEmployees', $lEmployees)
                                            ->with('permission_time', $config->permission_time)
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

            $lPermissions = permissionsUtils::getUserPermissions($oUser->id);
        } catch (\Throwable $th) {
            return json_encode(['sucess' => false, 'message' => 'Error al obtener al colaborador', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'oUser' => $oUser, 'lTemp' => $lTemp_special, 'lPermissions' => $lPermissions]);
    }

    public function approbePermission(Request $request){
        try {
            \DB::beginTransaction();
            $permission = Permission::findOrFail($request->permission_id);

            if($permission->request_status_id != SysConst::APPLICATION_ENVIADO){
                return json_encode(['success' => false, 'message' => 'Solo se pueden aprobar solicitudes nuevas', 'icon' => 'warning']);
            }

            $permission->request_status_id = SysConst::APPLICATION_APROBADO;
            $permission->user_apr_rej_id = delegationUtils::getIdUser();
            $permission->approved_date_n = Carbon::now()->toDateString();
            $permission->sup_comments_n = $request->comments;
            $permission->update();

            $data = permissionsUtils::sendPermissionToCAP($permission);

            if($data->status != 'Success'){
                \DB::rollBack();
                return json_encode(['sucess' => false, 'message' => 'Error al aprobar la incidencia', 'icon' => 'error']);
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
                $lPermissions = permissionsUtils::getMyEmployeeslPermissions();
            }else{
                $lPermissions = permissionsUtils::getMyManagerlPermissions($oManager->org_chart_job_id);
            }
            
            notificationsUtils::revisedNotificationFromAction(SysConst::TYPE_PERMISO_HORAS, $permission->id_hours_leave);

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['sucess' => false, 'message' => 'Error al aprobar la incidencia', 'icon' => 'error']);
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
                return null; 
            }

            $mailLog->sys_mails_st_id = SysConst::MAIL_ENVIADO;
            $mailLog->update();
        })->then(function () {
            
        })->catch(function () {
            
        })->timeout(function () {
            
        });

        return json_encode(['success' => true, 'lPermissions' => $lPermissions, 'mailLog_id' => $mailLog->id_mail_log]);
    }

    public function rejectPermission(Request $request){
        try {
            \DB::beginTransaction();
            $permission = Permission::findOrFail($request->permission_id);

            if($permission->request_status_id != SysConst::APPLICATION_ENVIADO){
                return json_encode(['success' => false, 'message' => 'Solo se pueden aprobar solicitudes nuevas', 'icon' => 'warning']);
            }

            $permission->request_status_id = SysConst::APPLICATION_RECHAZADO;
            $permission->user_apr_rej_id = delegationUtils::getIdUser();
            $permission->rejected_date_n = Carbon::now()->toDateString();
            $permission->sup_comments_n = $request->comments;
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
                $lPermissions = permissionsUtils::getMyEmployeeslPermissions();
            }else{
                $lPermissions = permissionsUtils::getMyManagerlPermissions($oManager->org_chart_job_id);
            }

            notificationsUtils::revisedNotificationFromAction(SysConst::TYPE_PERMISO_HORAS, $permission->id_hours_leave);

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['sucess' => false, 'message' => 'Error al aprobar la incidencia', 'icon' => 'error']);
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
                return null; 
            }

            $mailLog->sys_mails_st_id = SysConst::MAIL_ENVIADO;
            $mailLog->update();
        })->then(function () {
            
        })->catch(function () {
            
        })->timeout(function () {
            
        });

        return json_encode(['success' => true, 'lPermissions' => $lPermissions, 'mailLog_id' => $mailLog->id_mail_log]);
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
            return json_encode(['success' => false, 'message' => 'Error al obtener la lista de colaboradores directos', 'icon' => 'error']);
        }
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

                $lPermissions = permissionsUtils::getMyManagerlPermissions($oManager->org_chart_job_id);
            }else{
                $lPermissions = permissionsUtils::getMyEmployeeslPermissions();
            }

        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al obtener los permisos', 'icon' => 'error']);
        }

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
            $lPermissions = permissionsUtils::getMyEmployeeslPermissions();
        }else{
            $lPermissions = permissionsUtils::getMyManagerlPermissions($oManager->org_chart_job_id);
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
                return null; 
            }

            $mailLog->sys_mails_st_id = SysConst::MAIL_ENVIADO;
            $mailLog->update();
        })->then(function ($mailLog) {
            
        })->catch(function ($mailLog) {
            
        })->timeout(function ($mailLog) {
            
        });

        return json_encode(['success' => true, 'lPermissions' => $lPermissions, 'mailLog_id' => $mailLog->id_mail_log]);
    }
}
