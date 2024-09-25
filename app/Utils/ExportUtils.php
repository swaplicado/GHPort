<?php namespace App\Utils;

use \App\Models\Vacations\Application;
use \App\Models\Permissions\Permission;
use \App\Http\Controllers\Pages\requestVacationsController;
use \App\Http\Controllers\Pages\requestIncidencesController;
use \App\Http\Controllers\Pages\requestPermissionController;
use Illuminate\Http\Request;

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
    public static function getEvents($startDate, $endDate, $userIds)
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
            . ($startDate ? ' AND t.start_date >= ?' : '')
            . ($endDate ? ' AND t.end_date <= ?' : '') .
            ' ORDER BY 
                t.start_date ASC, 
                t.end_date ASC, 
                t.name ASC, 
                t.user_id ASC;';

        // Crear un array con los valores de parámetros a pasar a la consulta
        $bindings = [];

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
    public static function getIncidents($startDate, $endDate, $userIds)
    {
        // Construimos la consulta base        
        $query = \DB::table('applications AS a')
            ->select(
                'u.full_name',
                'u.employee_num',
                'st.applications_st_name',
                'tp.incidence_tp_name',
                'a.*',
                'cl.incidence_cl_name AS event_type'
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
    public static function getPermissions($startDate, $endDate, $userIds) {
        $query = 'SELECT 
                    u.full_name,
                    u.employee_num,
                    st.applications_st_name,
                    tp.permission_tp_name,
                    "permission" AS event_type,
                    hl.*
                FROM
                    hours_leave AS hl
                        INNER JOIN
                    sys_applications_sts AS st ON hl.request_status_id = st.id_applications_st
                        INNER JOIN
                    cat_permission_tp AS tp ON hl.type_permission_id = tp.id_permission_tp
                        INNER JOIN
                    permission_cl AS cl ON hl.cl_permission_id = cl.id_permission_cl
                        INNER JOIN
                    users AS u ON hl.user_id = u.id
                WHERE
                    NOT hl.is_deleted'
                    . ($userIds ? ' AND hl.user_id IN (' . implode(',', $userIds) . ')' : '')
                    . ($startDate ? ' AND hl.start_date >= ?' : '')
                    . ($endDate ? ' AND hl.end_date <= ?' : '') .
                ' ORDER BY hl.updated_at DESC;';

        // Crear un array con los valores de parámetros a pasar a la consulta
        $bindings = [];

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
     * Obtiene el estatus de una solicitud de vacaciones, incidencia o permiso
     *
     * @param  object $oApplication
     * @param  string $type
     * @return object
     */
    public static function getApplicationStatus($oApplication, $type){
        switch ($type) {
            case 'VACACIONES':
            case 'INASISTENCIA':
                $oStatus = Application::where('id_application', $oApplication->id_application)
                    ->join('sys_applications_sts', 'applications.request_status_id', '=', 'sys_applications_sts.id_applications_st')
                    ->select(
                            "sys_applications_sts.id_applications_st",
                            "sys_applications_sts.applications_st_code",
                            "sys_applications_sts.applications_st_name"
                        )
                    ->first();
                break;

            case 'permission':
                $oStatus = Permission::where('id_hours_leave', $oApplication->id_hours_leave)
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
}