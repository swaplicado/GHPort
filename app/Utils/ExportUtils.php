<?php namespace App\Utils;

use App\Http\Controllers\Pages\incidencesController;
use App\Http\Controllers\Pages\myVacationsController;
use App\Http\Controllers\Pages\permissionController;
use \App\Models\Vacations\Application;
use \App\Models\Permissions\Permission;
use \App\Http\Controllers\Pages\requestVacationsController;
use \App\Http\Controllers\Pages\requestIncidencesController;
use \App\Http\Controllers\Pages\requestPermissionController;
use Illuminate\Http\Request;
use \App\User;
use \App\Models\Adm\Holiday;
use Carbon\Carbon;
use App\Constants\SysConst;
use App\Utils\usersInSystemUtils;

class ExportUtils {

    /**
     * Index de usuarios que regresa un json con el resultado de la
     * consulta de la base de datos a los registros.
     * Puede filtrarse por rango de fechas y/o usuario(s).
     *
     * @param  string $startDate
     * @param  string $endDate
     * @param  array $userIds
     */
    public static function getEvents($startDate, $endDate, $userIds, $last_sync_date)
    {
        // Construimos la consulta base
        $query = 'SELECT 
                t.*,
                u.full_name,
                u.employee_num,
                "calendar-event" AS event_type
            FROM
                (
                    (SELECT 
                        ea1.user_id_n AS user_id, 
                        e1.*
                    FROM
                        events_assigns AS ea1
                    INNER JOIN 
                        cat_events AS e1 
                        ON ea1.event_id = e1.id_event
                    WHERE
                        NOT ea1.is_deleted 
                        AND NOT ea1.is_closed
                        AND NOT e1.is_deleted
                        AND ea1.user_id_n IS NOT NULL)
                    UNION 
                    (SELECT 
                        ga.user_id_n AS user_id, 
                        e2.*
                    FROM
                        cat_events AS e2
                    INNER JOIN 
                        events_assigns AS ea2 
                        ON e2.id_event = ea2.event_id
                    INNER JOIN 
                        groups AS g 
                        ON ea2.group_id_n = g.id_group
                    INNER JOIN 
                        groups_assigns AS ga 
                        ON g.id_group = ga.group_id_n
                    WHERE
                        NOT ea2.is_deleted 
                        AND NOT ea2.is_closed
                        AND NOT e2.is_deleted)
                ) AS t
            INNER JOIN users AS u ON t.user_id = u.id 
            WHERE 1 = 1' // Condición dummy para simplificar agregar filtros condicionales
            // Filtros condicionales
            . ($userIds ? ' AND t.user_id IN (' . implode(',', $userIds) . ')' : '')
            . ($last_sync_date ? ' AND t.updated_at >= ?' : '')
            . ($startDate ? ' AND t.start_date >= ?' : '')
            . ($endDate ? ' AND t.end_date <= ?' : '') .
            ' ORDER BY 
                t.start_date ASC, 
                t.end_date ASC, 
                t.name ASC, 
                t.user_id ASC;';

        // Crear un array con los valores de parámetros a pasar a la consulta
        $bindings = [];

        if ($last_sync_date) {
            $bindings[] = $last_sync_date; // Agregar fecha de inicio
        }
        if ($startDate) {
            $bindings[] = $startDate; // Agregar fecha de inicio
        }
        if ($endDate) {
            $bindings[] = $endDate; // Agregar fecha de fin
        }

        // Ejecutar la consulta con los parámetros
        $results = \DB::select($query, $bindings);

        // Retornar los resultados como JSON
        return $results;
    }


    /**
     *  Index de incidencias que regresa un json con el resultado de la
     * consulta de la base de datos a los registros.
     * Puede filtrarse por rango de fechas y/o usuario(s).
     * 
     * @param  string $startDate
     * @param  string $endDate
     * @param  array $userIds
     * @return array
     */
    public static function getIncidents($startDate, $endDate, $userIds, $last_sync_date, $lStatus)
    {
        // Construimos la consulta base        
        $query = \DB::table('applications AS a')
            ->select(
                'u.full_name',
                'u.employee_num',
                'st.applications_st_name',
                'tp.incidence_tp_name',
                'tp.id_incidence_tp',
                'a.*'
            )
            ->join('sys_applications_sts AS st', 'a.request_status_id', '=', 'st.id_applications_st')
            ->join('users AS u', 'a.user_id', '=', 'u.id')
            ->join('cat_incidence_tps AS tp', 'a.type_incident_id', '=', 'tp.id_incidence_tp')
            ->join('cat_incidence_cls as cl', 'tp.incidence_cl_id', '=', 'cl.id_incidence_cl')
            ->where('a.is_deleted', 0);

        if (!empty($userIds)) {
            $query->whereIn('a.user_id', $userIds);
        }

        if (!empty($startDate)) {
            $query->where('a.start_date', '>=', $startDate);
        }

        if (!empty($endDate)) {
            $query->where('a.end_date', '<=', $endDate);
        }

        if (!empty($last_sync_date)) {
            $query->where('a.updated_at', '>=', $last_sync_date);
        }

        if (!empty($lStatus)) {
            $query->whereIn('a.request_status_id', $lStatus);
        } else {
            $query->whereIn('a.request_status_id', [
                SysConst::APPLICATION_ENVIADO,
                SysConst::APPLICATION_APROBADO,
                SysConst::APPLICATION_RECHAZADO,
                SysConst::APPLICATION_CANCELADO,
                SysConst::APPLICATION_CONSUMIDO
            ]);
        }

        $query->orderBy('a.updated_at', 'DESC');
        $results = $query->get()->toArray();

        // Retornar los resultados como JSON
        return $results;
    }

    /**
     * Index de permisos de entrada o salida que regresa un json con el resultado
     * de la consulta a la base de datos a los registros.
     * Puede filtrarse por rango de fechas y/o usuario(s).
     *
     * @param  string $startDate
     * @param  string $endDate
     * @param  array $userIds
     * @return array
     */
    public static function getPermissions($startDate, $endDate, $userIds, $last_sync_date, $lStatus) {
        $query = \DB::table('hours_leave AS hl')
                    ->select('u.full_name', 'u.employee_num', 'st.applications_st_name', 'tp.permission_tp_name', 'tp.id_permission_tp', 'hl.*')
                    ->join('sys_applications_sts AS st', 'hl.request_status_id', '=', 'st.id_applications_st')
                    ->join('cat_permission_tp AS tp', 'hl.type_permission_id', '=', 'tp.id_permission_tp')
                    ->join('permission_cl AS cl', 'hl.cl_permission_id', '=', 'cl.id_permission_cl')
                    ->join('users AS u', 'hl.user_id', '=', 'u.id')
                    ->where('hl.is_deleted', false);

                if ($userIds) {
                    $query->whereIn('hl.user_id', $userIds);
                }

                if ($last_sync_date) {
                    $query->where('hl.updated_at', '>=', $last_sync_date);
                }

                if ($startDate) {
                    $query->where('hl.start_date', '>=', $startDate);
                }

                if ($endDate) {
                    $query->where('hl.end_date', '<=', $endDate);
                }

                if (!empty($lStatus)) {
                    $query->whereIn('hl.request_status_id', $lStatus);
                } else {
                    $query->whereIn('hl.request_status_id', [
                        SysConst::APPLICATION_ENVIADO,
                        SysConst::APPLICATION_APROBADO,
                        SysConst::APPLICATION_RECHAZADO,
                        SysConst::APPLICATION_CANCELADO,
                        SysConst::APPLICATION_CONSUMIDO
                    ]);
                }

                $query->orderBy('hl.updated_at', 'DESC');

                $results = $query->get()->toArray();

        // Retornar los resultados como JSON
        return $results;
    }

    /**
     * Función que obtiene los empleados de un usuario desde PGH
     * 
     * @return array
     */
    public static function getEmployees($id_user_boss, $last_sync_date) {
        $org_chart_job_id = User::where('id', $id_user_boss)->value('org_chart_job_id');
        $lChildAreas = orgChartUtils::getAllChildsToRevice($org_chart_job_id);

        $query = \DB::table('users as u')
                        ->where('u.is_active', 1)
                        ->where('u.is_delete', 0)
                        ->where('u.id', '!=', 1)
                        ->whereIn('u.org_chart_job_id', $lChildAreas)
                        ->leftJoin('org_chart_jobs as org', 'org.id_org_chart_job', '=', 'u.org_chart_job_id')
                        ->leftJoin('ext_jobs as j', 'j.id_job', '=', 'u.job_id')
                        ->leftJoin('ext_departments as d', 'd.id_department', '=', 'j.department_id');

        if ($last_sync_date) {
            $query = $query->where('u.updated_at', '>=', $last_sync_date);
        }

        $query = $query->select(
                        'u.id',
                        'u.first_name',
                        'u.last_name',
                        'u.full_name',
                        'org.job_name as org_chart_job_name',
                        'j.job_name',
                        'd.department_name',
                        'u.updated_at',
                        'u.created_at'
                    )
                    ->get();
                        
        $lEmployees = $query->toArray();
        $lEmployees = usersInSystemUtils::FilterUsersInSystem($lEmployees, 'id');

        return $lEmployees;
    }

    /**
     * Obtiene el estatus de una solicitud de vacaciones, incidencia o permiso
     *
     * @param  object $oApplication
     * @param  string $type
     * @return object
     */
    public static function getApplicationStatus($oApplication, $class){
        switch ($class) {
            case 'VACATION':
            case 'INCIDENT':
                $oStatus = Application::where('id_application', $oApplication->id)
                    ->join('sys_applications_sts', 'applications.request_status_id', '=', 'sys_applications_sts.id_applications_st')
                    ->select(
                            "sys_applications_sts.id_applications_st",
                            "sys_applications_sts.applications_st_code",
                            "sys_applications_sts.applications_st_name"
                        )
                    ->first();
                break;

            case 'PERMISSION':
                $oStatus = Permission::where('id_hours_leave', $oApplication->id)
                    ->join('sys_applications_sts', 'hours_leave.request_status_id', '=', 'sys_applications_sts.id_applications_st')
                    ->select(
                        "sys_applications_sts.id_applications_st",
                        "sys_applications_sts.applications_st_code",
                        "sys_applications_sts.applications_st_name"
                    )
                    ->first();
                break;

            default:
                $oStatus = null;
                break;
        }

        return $oStatus;
    }

    /**
     * Autoriza las vacaciones recibidas
     * @param mixed $oVacation
     * @return bool|string
     */
    public static function authorizeVacations($oVacation) {
        try {
            $newRequest = new Request((array)$oVacation);
            $oController = app(requestVacationsController::class);
            $result = $oController->acceptRequest($newRequest);
        } catch (\Throwable $th) {
            \Log::error($th);
            $result = json_encode(['sucess' => false, 'error' => $th->getMessage()]);
        }

        $oResult = json_decode($result);
        return json_encode(['success' => $oResult->success, 'message' => $oResult->message]);
    }

    /**
     * Autoriza la incidencia recibida
     * @param mixed $oIncidence
     * @return bool|string
     */
    public static function authorizeIncidence($oIncidence) {
        try {
            $newRequest = new Request((array)$oIncidence);
            $oController = app(requestIncidencesController::class);
            $result = $oController->approbeIncidence($newRequest);
        } catch (\Throwable $th) {
            \Log::error($th);
            $result = json_encode(['sucess' => false, 'error' => $th->getMessage()]);
        }

        $oResult = json_decode($result);
        return json_encode(['success' => $oResult->success, 'message' => $oResult->message]);
    }

    /**
     * Autoriza el permiso recibido
     * @param mixed $oPermission
     * @return bool|string
     */
    public static function authorizePermission($oPermission) {
        try {
            $newRequest = new Request((array)$oPermission);
            $oController = app(requestPermissionController::class);
            $result = $oController->approbePermission($newRequest);
        } catch (\Throwable $th) {
            \Log::error($th);
            $result = json_encode(['sucess' => false, 'error' => $th->getMessage()]);
        }

        $oResult = json_decode($result);
        return json_encode(['success' => $oResult->success, 'message' => $oResult->message]);
    }

    /**
     * Rechaza las vacaciones recibidas
     * @param mixed $oVacation
     * @return bool|string
     */
    public static function rejectVacations($oVacation) {
        try {
            $newRequest = new Request((array)$oVacation);
            $oController = app(requestVacationsController::class);
            $result = $oController->rejectRequest($newRequest);
        } catch (\Throwable $th) {
            \Log::error($th);
            $result = json_encode(['sucess' => false, 'error' => $th->getMessage()]);
        }
        $oResult = json_decode($result);
        return json_encode(['success' => $oResult->success, 'message' => $oResult->message]);
    }

    /**
     * Rechaza la incidencia recibida
     * @param mixed $oIncidence
     * @return bool|string
     */
    public static function rejectIncidence($oIncidence) {
        try {
            $newRequest = new Request((array)$oIncidence);
            $oController = app(requestIncidencesController::class);
            $result = $oController->rejectIncidence($newRequest);
        } catch (\Throwable $th) {
            \Log::error($th);
            $result = json_encode(['sucess' => false, 'error' => $th->getMessage()]);
        }
        $oResult = json_decode($result);
        return json_encode(['success' => $oResult->success, 'message' => $oResult->message]);
    }

    /**
     * Rechaza el permiso recibido
     * @param mixed $oPermission
     * @return bool|string
     */
    public static function rejectPermission($oPermission) {
        try {
            $newRequest = new Request((array)$oPermission);
            $oController = app(requestPermissionController::class);
            $result = $oController->rejectPermission($newRequest);
        } catch (\Throwable $th) {
            \Log::error($th);
            $result = json_encode(['sucess' => false, 'error' => $th->getMessage()]);
        }
        $oResult = json_decode($result);
        return json_encode(['success' => $oResult->success, 'message' => $oResult->message]);
    }

    /**
     * Indica si la solicitud esta autorizada
     * @param mixed $oApplication
     * @param string $type
     * @return bool
     */
    public static function isAuthorized($oApplication, $type) {
        $oStatus = ExportUtils::getApplicationStatus($oApplication, $type);
        return $oStatus->applications_st_code == 'APR' || $oStatus->applications_st_code == 'CON';
    }

    /**
     * Indica si la solicitud esta rechazada
     * @param mixed $oApplication
     * @param string $type
     * @return bool
     */
    public static function isRejected($oApplication, $type) {
        $oStatus = ExportUtils::getApplicationStatus($oApplication, $type);
        return $oStatus->applications_st_code == 'REC';
    }

    public static function getEventsType() {
        $config = \App\Utils\Configuration::getConfigurations();
        $lEventsType = $config->eventsType;
        return $lEventsType;
    }

    public static function getHolidays($start_date, $last_sync_date) {
        $holidays = Holiday::where('is_deleted', 0);

        if ($start_date) {
            $holidays = $holidays->where('fecha', '>=', $start_date);
        }

        if ($last_sync_date) {
            $holidays = $holidays->where('updated_at', '>=', $last_sync_date);
        }

        $holidays = $holidays->select(
                                'id',
                                'name',
                                'fecha',
                                'year',
                                'is_deleted',
                                'created_at',
                                'updated_at'
                            )
                            ->orderBy('fecha', 'asc')
                            ->get();

        foreach ($holidays as $holiday) {
            $holiday->start_date = $holiday->fecha;
            $holiday->end_date = $holiday->fecha;
            $holiday->type_key = 'HOL';
        }
        return $holidays;
    }

    public static function createAndSendVacation($oVacation) {
        try {
            $employee_id = \Auth::user()->id;
            $user = EmployeeVacationUtils::getEmployeeVacationsData($employee_id, true, 1);
            $vacations = collect($user->vacation)->sortBy('year');

            \DB::beginTransaction();

            $result = json_decode(creeateSentIncidentsUtils::createVacation($oVacation, $user, $vacations));
            if ($result->success) {
                $sendResult = json_decode(creeateSentIncidentsUtils::sendVacation($user, $result->application->id_application));
                if ($sendResult->success) {
                    \DB::commit();   
                    $application = $sendResult->application;
                    $toUsers = collect($sendResult->toUsers);
                    $oMailLog = $sendResult->oMailLog;
                    try {
                        creeateSentIncidentsUtils::sendMail($application, $toUsers, 'VAC', $oMailLog->id_mail_log);
                        creeateSentIncidentsUtils::sendAppNotification($user, $toUsers, 'Envió solicitud de vacaciones');
                    } catch (\Throwable $th) {
                        \Log::error($th->getMessage());
                    }
                }
            }

        } catch (\Throwable $th) {
            \Log::error($th);
            \DB::rollBack();
            return json_encode([
                    'success' => false,
                    'message' => $th->getMessage()
                ]);
        }

        return json_encode([
            'success' => true,
            'message' => 'Solicitud de vacaciones generada con éxito'
        ]);
    }

    public static function createAndSendIncidence($oIncidence) {
        try {
            \DB::beginTransaction();
            $oUser = \Auth::user();
            $result = json_decode(creeateSentIncidentsUtils::createIncidence($oIncidence, $oUser));
            if ($result->success) {
                $sendResult = json_decode(creeateSentIncidentsUtils::sendIncidence($oUser, $result->application->id_application));
                if ($sendResult->success) {
                    \DB::commit();   
                    $application = $sendResult->application;
                    $toUsers = collect($sendResult->toUsers);
                    $oMailLog = $sendResult->oMailLog;
                    $type_incident = $sendResult->type_incident;
                    try {
                        creeateSentIncidentsUtils::sendMail($application, $toUsers, 'INC', $oMailLog->id_mail_log);
                        creeateSentIncidentsUtils::sendAppNotification($oUser, $toUsers, 'Envió solicitud de ' . mb_strtolower($type_incident, 'UTF-8'));
                    } catch (\Throwable $th) {
                        \Log::error($th->getMessage());
                    }
                }
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            \DB::rollBack();
            return json_encode([
                    'success' => false,
                    'message' => $th->getMessage()
                ]);
        }

        return json_encode([
            'success' => true,
            'message' => 'Incidencia generada con éxito'
        ]);
    }

    public static function createAndSendPermission($oPermission) {
        try {
            \DB::beginTransaction();
            $oUser = \Auth::user();
            $result = json_decode(creeateSentIncidentsUtils::createPermission($oPermission, $oUser));
            if ($result->success) {
                $sendResult = json_decode(creeateSentIncidentsUtils::sendPermission($oUser, $result->permission->id_hours_leave));
                if ($sendResult->success) {
                    \DB::commit();   
                    $permission = $sendResult->permission;
                    $toUsers = collect($sendResult->toUsers);
                    $oMailLog = $sendResult->oMailLog;
                    
                    $class_permission = \DB::table('permission_cl')
                                    ->where('id_permission_cl', $permission->cl_permission_id)
                                    ->value('permission_cl_name');

                    try {
                        creeateSentIncidentsUtils::sendMail($permission, $toUsers, 'PER', $oMailLog->id_mail_log);
                        creeateSentIncidentsUtils::sendAppNotification($oUser, $toUsers, 'Envió solicitud de ' . mb_strtolower($class_permission, 'UTF-8'));
                    } catch (\Throwable $th) {
                        \Log::error($th->getMessage());
                    }
                }
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            \DB::rollBack();
            return json_encode([
                    'success' => false,
                    'message' => $th->getMessage()
                ]);
        }

        return json_encode([
            'success' => true,
            'message' => 'Permiso generado con éxito'
        ]);
    }
}