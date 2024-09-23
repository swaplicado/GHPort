<?php namespace App\Utils;

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
        $query = 'SELECT 
                    u.full_name,
                    u.employee_num,
                    st.applications_st_name,
                    tp.incidence_tp_name,
                    "incident" AS event_type,
                    a.*
                FROM
                    applications AS a
                        INNER JOIN
                    sys_applications_sts AS st ON a.request_status_id = st.id_applications_st
                        INNER JOIN
                    users AS u ON a.user_id = u.id
                        INNER JOIN
                    cat_incidence_tps AS tp ON a.type_incident_id = tp.id_incidence_tp
                WHERE
                    NOT a.is_deleted'
                    . ($userIds ? ' AND a.user_id IN (' . implode(',', $userIds) . ')' : '')
                    . ($startDate ? ' AND a.start_date >= ?' : '')
                    . ($endDate ? ' AND a.end_date <= ?' : '') .
                ' ORDER BY a.updated_at DESC;';
        
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
}