<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\permissionsUtils;
use \App\Utils\EmployeeVacationUtils;
use \App\Constants\SysConst;
use \App\Utils\delegationUtils;
use Carbon\Carbon;
use \App\Models\Permissions\Permission;
use \App\Utils\folioUtils;
use App\Utils\orgChartUtils;
use App\Models\Vacations\MailLog;
use Spatie\Async\Pool;
use Illuminate\Support\Facades\Mail;
use App\Mail\requestPermissionMail;
use App\Mail\authorizePermissionMail;
use App\Utils\notificationsUtils;

class permissionController extends Controller
{
    public function index($id){
        $lPermissions = permissionsUtils::getUserPermissions(delegationUtils::getIdUser(),$id);
        $permiso_personal = SysConst::PERMISO_PERSONAL;
        $permiso_laboral = SysConst::PERMISO_LABORAL;

        $clase_permiso = 0;
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
        
        if( $id == $permiso_laboral ){
            $lClass = \DB::table('permission_cl')
                        ->where('is_deleted', 0)
                        ->where('is_active', 1)
                        ->where('id_permission_cl', $permiso_laboral)
                        ->get();
            $clase_permiso = $permiso_laboral;
        }else {
            $lClass = \DB::table('permission_cl')
                        ->where('is_deleted', 0)
                        ->where('is_active', 1)
                        ->where('id_permission_cl', $permiso_personal)
                        ->get();
            $clase_permiso = $permiso_personal;
        }

        $lHolidays = \DB::table('holidays')
                        ->where('fecha', '>', Carbon::now()->subDays(30)->toDateString())
                        ->where('is_deleted', 0)
                        ->pluck('fecha');

        $lTemp_special = EmployeeVacationUtils::getEmployeeTempSpecial(delegationUtils::getOrgChartJobIdUser(), delegationUtils::getIdUser(), delegationUtils::getJobIdUser());

        $config = \App\Utils\Configuration::getConfigurations();

        $superviser = orgChartUtils::getExistDirectSuperviserOrgChartJob(\Auth::user()->org_chart_job_id);
        $lSuperviser = [];
        if(!is_null($superviser)){
            $lSuperviser = orgChartUtils::getAllUsersByOrgChartJob($superviser->org_chart_job_id);
        }

        $now = Carbon::now();
        $initialCalendarDate = $now->subMonths(1)->toDateString();

        return view('permissions.permissions')->with('lPermissions', $lPermissions)
                                            ->with('constants', $constants)
                                            ->with('lTypes', $lTypes)
                                            ->with('lClass', $lClass)
                                            ->with('lHolidays', $lHolidays)
                                            ->with('lTemp', $lTemp_special)
                                            ->with('oPermission', null)
                                            ->with('oUser', \Auth::user())
                                            ->with('permission_time', $config->permission_time)
                                            ->with('lSuperviser', $lSuperviser)
                                            ->with('initialCalendarDate', $initialCalendarDate)
                                            ->with('clase_permiso', $clase_permiso);
    }

    public function createPermission(Request $request){
        try {
            $startDate = $request->startDate;
            $comments = $request->comments;
            $class_id = $request->class_id;
            $type_id = $request->type_id;
            $employee_id = $request->employee_id;
            $hours = $request->hours;
            $minutes = $request->minutes;
            $interOut = $request->interOut;
            $interReturn = $request->interReturn;

            \DB::beginTransaction();

            $permission = new Permission();
            $permission->folio_n = folioUtils::makeFolio(Carbon::now(), $employee_id, SysConst::TYPE_PERMISO_HORAS);
            $permission->start_date = $startDate;
            $permission->end_date = $startDate;
            $permission->total_days = 1;
            $permission->tot_calendar_days = 1;
            $permission->ldays = json_encode([$startDate]);
            $permission->minutes = permissionsUtils::getTime($hours, $minutes);
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
            $permission->save();

            $lPermissions = permissionsUtils::getUserPermissions($employee_id,$class_id);

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al crear el permiso', 'icon' => 'error']);
        }
        return json_encode(['success' => true, 'lPermissions' => $lPermissions]);
    }

    public function updatePermission(Request $request){
        try {
            $permission_id = $request->permission_id;
            $startDate = $request->startDate;
            $comments = $request->comments;
            $class_id = $request->class_id;
            $type_id = $request->type_id;
            $hours = $request->hours;
            $minutes = $request->minutes;
            $employee_id = $request->employee_id;
            $interOut = $request->interOut;
            $interReturn = $request->interReturn;

            \DB::beginTransaction();

            $permission = Permission::findOrFail($permission_id);
            $permission->start_date = $startDate;
            $permission->end_date = $startDate;
            $permission->ldays = json_encode([$startDate]);
            $permission->minutes = permissionsUtils::getTime($hours, $minutes);
            $permission->type_permission_id = $type_id;
            $permission->cl_permission_id = $class_id;
            $permission->emp_comments_n = $comments;
            $permission->updated_by = \Auth::user()->id;
            $permission->intermediate_out = $interOut;
            $permission->intermediate_return = $interReturn;
            $permission->update();

            $lPermissions = permissionsUtils::getUserPermissions($employee_id,$class_id);

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al crear el permiso', 'icon' => 'error']);
        }
        return json_encode(['success' => true, 'lPermissions' => $lPermissions]);
    }

    public function deletePermission(Request $request){
        try {
            $permission_id = $request->permission_id;
            $employee_id = $request->employee_id;

            \DB::beginTransaction();

            $permission = Permission::findOrFail($permission_id);
            $permission->is_deleted = true;
            $permission->update();

            $lPermissions = permissionsUtils::getUserPermissions($employee_id,$permission->cl_permission_id);
            
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al eliminar el permiso', 'icon' => 'error']);
        }
        return json_encode(['success' => true, 'lPermissions' => $lPermissions]);
    }

    public function getPermission(Request $request){
        try {
            $oPermission = permissionsUtils::getPermission($request->permission_id);
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al obtener el registro', 'icon' => 'error']);
        }
        return json_encode(['success' => true, 'oPermission' => $oPermission]);
    }

    public function gestionSendIncidence(Request $request){
        try {
            $oPermission = Permission::findOrFail($request->permission_id);
            $needAuth = \DB::table('cat_permission_tp')
                            ->where('id_permission_tp', $oPermission->type_permission_id)
                            ->value('need_authorization');

        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al enviar el registro', 'icon' => 'error']);
        }

        if($needAuth == null || $needAuth == 1){
            $result = $this->sendPermission($request);
        }else{
            $result = $this->sendAndAuthorize($request);
        }

        return $result;
    }

    public function sendPermission(Request $request){
        $permission_id = $request->permission_id;
        $employee_id = $request->employee_id;
        try {
            if(delegationUtils::getOrgChartJobIdUser() == 1){
                return json_encode(['success' => false, 'message' => 'No tienes area funcional, favor de comunicarte con el administrador del sistema', 'icon' => 'warning']);
            }
            \DB::beginTransaction();
            $permission = Permission::findOrFail($permission_id);

            $date = Carbon::now();
            $permission->request_status_id = SysConst::APPLICATION_ENVIADO;
            $permission->date_send_n = $date->toDateString();
            $permission->update();

            $user = \DB::table('users')
                        ->where('id', $permission->user_id)
                        ->first();

            $superviser = orgChartUtils::getExistDirectSuperviserOrgChartJob($user->org_chart_job_id);

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

            $lPermissions = permissionsUtils::getUserPermissions($employee_id,$permission->cl_permission_id);

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

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al enviar el permiso']);
        }

        $mypool = Pool::create();
        $mypool[] = async(function () use ($permission, $superviser, $mailLog){
            try {
                $lUsers = orgChartUtils::getAllUsersByOrgChartJob($superviser->org_chart_job_id);
                $arrUsers = $lUsers->map(function ($item) {
                    return $item->institutional_mail;
                })->toArray();

                $arrUsers = array_unique($arrUsers);

                Mail::to($arrUsers)->send(new requestPermissionMail(
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

    public function sendAndAuthorize(Request $request){
        try {
            $permission_id = $request->permission_id;
            $employee_id = $request->employee_id;

            \DB::beginTransaction();

            $permission = Permission::findOrFail($permission_id);

            $date = Carbon::now();
            $permission->request_status_id = SysConst::APPLICATION_APROBADO;
            $permission->date_send_n = $date->toDateString();
            $permission->user_apr_rej_id = delegationUtils::getIdUser();
            $permission->approved_date_n = Carbon::now()->toDateString();
            $permission->update();

            $config = \App\Utils\Configuration::getConfigurations();
            $lPermissionConfig = collect($config->hours_leave_interact_sys);

            $oPerConfig = $lPermissionConfig->where('type_id', $permission->type_permission_id)->first();
            if($oPerConfig->sys_id == SysConst::CAP){
                $data = permissionsUtils::sendPermissionToCAP($permission);
                if($data->status != 'Success'){
                    \DB::rollBack();
                    return json_encode(['sucess' => false, 'message' => 'Error al aprobar la incidencia', 'icon' => 'error']);
                }
            }


            $lPermissions = permissionsUtils::getUserPermissions($employee_id,$permission->cl_permission_id);

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
            
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al enviar y autorizar la solicitud', 'icon' => 'error']);
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

    public function checkMail(Request $request){
        $mailLog = MailLog::find($request->mail_log_id);

        return json_encode(['sucess' => true, 'status' => $mailLog->sys_mails_st_id]);
    }
}
