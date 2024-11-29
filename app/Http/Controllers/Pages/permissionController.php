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
use GuzzleHttp\Client;

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
        $lEvents = EmployeeVacationUtils::getEmployeeEvents(delegationUtils::getIdUser());
        $config = \App\Utils\Configuration::getConfigurations();

        $superviser = orgChartUtils::getExistDirectSuperviserOrgChartJob(\Auth::user()->org_chart_job_id);
        $lSuperviser = [];
        if(!is_null($superviser)){
            $lSuperviser = orgChartUtils::getAllUsersByOrgChartJob($superviser->org_chart_job_id);
        }

        $now = Carbon::now();
        $initialCalendarDate = $now->subMonths(1)->toDateString();
        $time_restriction = 0;
        if($clase_permiso == SysConst::PERMISO_LABORAL){
            $time_restriction = $config->permission_time_work;
        }else{
            $time_restriction = $config->permission_time;
        }

        $lSchedule = \DB::table('schedule_template as st')
                        ->join('schedule_day as sd', 'sd.schedule_template_id', '=', 'st.id')
                        ->where('st.id', \Auth::user()->schedule_template_id)
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

        $lStatus = \DB::table('sys_applications_sts')
                        ->where('is_deleted', 0)
                        ->where('id_applications_st', '!=', SysConst::APPLICATION_CONSUMIDO)
                        ->select(
                            'id_applications_st as id',
                            'applications_st_name as name'
                        )
                        ->get();

        return view('permissions.permissions')->with('lPermissions', $lPermissions)
                                            ->with('constants', $constants)
                                            ->with('lTypes', $lTypes)
                                            ->with('lClass', $lClass)
                                            ->with('lHolidays', $lHolidays)
                                            ->with('lTemp', $lTemp_special)
                                            ->with('oPermission', null)
                                            ->with('oUser', \Auth::user())
                                            ->with('permission_time', $time_restriction)
                                            ->with('lSuperviser', $lSuperviser)
                                            ->with('initialCalendarDate', $initialCalendarDate)
                                            ->with('clase_permiso', $clase_permiso)
                                            ->with('lSchedule', $lSchedule)
                                            ->with('lStatus', $lStatus)
                                            ->with('lEvents', $lEvents);
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
            // $interOut = $request->interOut;
            // $interReturn = $request->interReturn;

            $interOut = null;
            $interReturn = null;

            if($comments == null || $comments == ""){
                return json_encode(['success' => false, 'message' => 'Para proseguir, se requiere incluir un comentario en la solicitud', 'icon' => 'error']);
            }

            if(!is_null($request->interOut) && !is_null($request->interReturn)){
                $interOut = Carbon::createFromFormat('g:i A', $request->interOut)->format('H:i');
                $interReturn = Carbon::createFromFormat('g:i A', $request->interReturn)->format('H:i');
            }

            \DB::beginTransaction();

            $comments = str_replace(['"', "\\", "\r", "\n"], "", $comments);

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
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible almacenar la solicitud debido a un error inesperado. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
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
            // $interOut = $request->interOut;
            // $interReturn = $request->interReturn;

            $interOut = null;
            $interReturn = null;

            if($comments == null || $comments == ""){
                return json_encode(['success' => false, 'message' => 'Para proseguir, se requiere incluir un comentario en la solicitud', 'icon' => 'error']);
            }

            if(!is_null($request->interOut) && !is_null($request->interReturn)){
                $interOut = Carbon::createFromFormat('g:i A', $request->interOut)->format('H:i');
                $interReturn = Carbon::createFromFormat('g:i A', $request->interReturn)->format('H:i');
            }

            \DB::beginTransaction();

            $comments = str_replace(['"', "\\", "\r", "\n"], "", $comments);

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
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible actualizar la solicitud debido a un error inesperado. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
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
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible eliminar la solicitud debido a un error inesperado. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
        }
        return json_encode(['success' => true, 'lPermissions' => $lPermissions]);
    }

    public function getPermission(Request $request){
        try {
            $oPermission = permissionsUtils::getPermission($request->permission_id);

            $numDay = Carbon::parse($oPermission->start_date)->dayOfWeek;

            $schedule = \DB::table('users as u')
                        ->join('schedule_template as st', 'st.id', '=', 'u.schedule_template_id')
                        ->join('schedule_day as sd', 'sd.schedule_template_id', '=', 'st.id')
                        ->where('u.id', $oPermission->user_id)
                        ->where('sd.is_working', 1)
                        ->where('sd.is_deleted', 0)
                        // ->where('sd.day_num', $numDay)
                        ->select(
                            'st.name',
                            'sd.day_name',
                            'sd.day_num',
                            \DB::raw("DATE_FORMAT(sd.entry, '%H:%i') as entry"),
                            \DB::raw("DATE_FORMAT(sd.departure, '%H:%i') as departure")
                        )
                        ->get();

            foreach($schedule as $sc){
                $sc->entry = Carbon::parse($sc->entry)->format('g:i A');
                $sc->departure = Carbon::parse($sc->departure)->format('g:i A');
            }

            $permission = "";
            if(count($schedule) > 0){
                if($oPermission->type_permission_id == SysConst::PERMISO_ENTRADA){
                    $permission = Carbon::parse($schedule[0]->entry)->addMinutes($oPermission->minutes)->format('g:i A');
                }else if($oPermission->type_permission_id == SysConst::PERMISO_SALIDA){
                    $permission = Carbon::parse($schedule[0]->departure)->subMinutes($oPermission->minutes)->format('g:i A');
                }
            }

            if($oPermission->type_permission_id == SysConst::PERMISO_INTERMEDIO){
                $permission = new \stdClass;
                $permission->inter_out = Carbon::parse($oPermission->intermediate_out)->format('g:i A');
                $permission->inter_ret = Carbon::parse($oPermission->intermediate_return)->format('g:i A');

                $hora1_24 = Carbon::parse($oPermission->intermediate_out)->format('H:i');
                $hora2_24 = Carbon::parse($oPermission->intermediate_return)->format('H:i');

                // Calcular la diferencia en minutos
                $diferencia_minutos = Carbon::parse($hora1_24)->diffInMinutes(Carbon::parse($hora2_24));

                // Convertir la diferencia de vuelta a formato de 12 horas si es necesario
                $diferencia_horas = floor($diferencia_minutos / 60);
                $diferencia_minutos_restantes = $diferencia_minutos % 60;
                $oPermission->time = $diferencia_horas.' hrs. '.$diferencia_minutos_restantes.' minutos';
            }

        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible obtener la solicitud debido a un error inesperado. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
        }
        return json_encode(['success' => true, 'oPermission' => $oPermission, 'schedule' => $schedule, 'permission' => $permission]);
    }

    public function gestionSendIncidence(Request $request){
        try {
            $oPermission = Permission::findOrFail($request->permission_id);
            $needAuth = \DB::table('cat_permission_tp')
                            ->where('id_permission_tp', $oPermission->type_permission_id)
                            ->value('need_authorization');

        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible enviar la solicitud debido a un error inesperado. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
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
                return json_encode(['success' => false, 'message' => 'No estás asignado a un área funcional, por favor contacta con el área de gestión humana', 'icon' => 'warning']);
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
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible enviar la solicitud debido a un error inesperado. Por favor, verifique su conexión a internet e inténtelo de nuevo']);
        }

        $mypool = Pool::create();
        $mypool[] = async(function () use ($permission, $superviser, $mailLog){

            try {
                $config = \App\Utils\Configuration::getConfigurations();
                $lUsers = orgChartUtils::getAllUsersByOrgChartJob($superviser->org_chart_job_id);
                $arrUsers = $lUsers->map(function ($item) {
                    return $item->id;
                })->toArray();

                $arrUsers = array_unique($arrUsers);

                $headers = [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'X-API-Key' => $config->apiKeyPghMobile
                ];

                $class_permission = \DB::table('permission_cl')
                                    ->where('id_permission_cl', $permission->cl_permission_id)
                                    ->value('permission_cl_name');
                
                $type_permission = \DB::table('cat_permission_tp')
                                    ->where('id_permission_tp', $permission->type_permission_id)
                                    ->value('permission_tp_name');

                $oUser = delegationUtils::getUser();
                $full_name = $oUser->short_name . ' ' . $oUser->first_name . ' ' . $oUser->last_name;
                
                $body = '{
                    "title": "' . $full_name . '",
                    "body": "Envió solicitud de ' . mb_strtolower($class_permission, 'UTF-8') . '",
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
                    return json_encode(['sucess' => false, 'message' => $data->message, 'icon' => 'error']);
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
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible enviar y autorizar la solicitud debido a un error inesperado. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
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
        
        return json_encode(['sucess' => true, 'status' => $mailLog->sys_mails_st_id, 'message' => $message]);
    }
}
