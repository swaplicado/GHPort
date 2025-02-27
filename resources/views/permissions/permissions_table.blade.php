<div class="table-responsive">
    <table class="table table-bordered" id="{{$table_id}}" ref="{{$table_ref}}" width="100%" cellspacing="0">
        <thead class="thead-light">
            <th>id</th>
            <th>request_status_id</th>
            <th>emp coment.</th>
            <th>sup coment.</th>
            <th>revisor_id</th>
            <th>type_incident_id</th>
            <th>class_permission_id</th>
            <th>Empleado</th>
            <th>Permiso</th>
            <th>Clase</th>
            <th>Tiempo</th>
            <th>Folio</th>
            <th>Fecha envío</th>
            <th>Revisor</th>
            <th style="max-width: 20%;">Fecha revisión</th>
            <th>Fecha aplicación</th>
            <th>Estatus</th>
            <th>Fecha envío</th>
            <th>is_direct</th>
        </thead>
        <tbody>
            <tr v-for="permission in lPermissions">
                <td>@{{permission.id_hours_leave}}</td>
                <td>@{{permission.request_status_id}}</td>
                <td>@{{permission.emp_comments_n}}</td>
                <td>@{{permission.sup_comments_n}}</td>
                <td>@{{permission.user_apr_rej_id}}</td>
                <td>@{{permission.type_permission_id}}</td>
                <td>@{{permission.class_permission_id}}</td>
                <td>@{{permission.employee}}</td>
                <td>@{{permission.permission_tp_name}}</td>
                <td>@{{permission.permission_cl_name}}</td>
                <td>@{{permission.time}}</td>
                <td>@{{permission.folio_n}}</td>
                <td>
                    @{{
                        (permission.date_send_n != null ?
                            oDateUtils.formatDate(permission.date_send_n, 'ddd DD-MMM-YYYY') :
                                oDateUtils.formatDate(permission.updated_at, 'ddd DD-MMM-YYYY')
                            )
                    }}
                </td>
                <td>@{{permission.user_apr_rej_name}}</td>
                <td>
                    @{{
                        (permission.request_status_id == oData.constants.APPLICATION_APROBADO) ?
                            oDateUtils.formatDate(permission.approved_date_n, 'ddd DD-MMM-YYYY') :
                            ((permission.request_status_id == oData.constants.APPLICATION_RECHAZADO) ?
                                oDateUtils.formatDate(permission.rejected_date_n, 'ddd DD-MMM-YYYY') :
                                '')
                    }}
                </td>
                <td>@{{oDateUtils.formatDate(permission.start_date, 'ddd DD-MMM-YYYY')}}</td>
                <td>@{{
                        !isRevision ? permission.applications_st_name : 
                            (permission.request_status_id == 2 ? 'Por aprobar' : permission.applications_st_name)
                    }}
                </td>
                <td>
                    @{{permission.date_send_n}}
                </td>
                <td>
                    @{{permission.is_direct || 0}}
                </td>
            </tr>
        </tbody>
    </table>
</div>