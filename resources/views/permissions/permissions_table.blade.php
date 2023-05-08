<div class="table-responsive">
    <table class="table table-bordered" id="{{$table_id}}" ref="{{$table_ref}}" width="100%" cellspacing="0">
        <thead class="thead-light">
            <th>id</th>
            <th>incidence_status_id</th>
            <th>emp coment.</th>
            <th>sup coment.</th>
            <th>revisor_id</th>
            <th>permission_type_id</th>
            <th>Permiso</th>
            <th>Folio</th>
            <th>Fecha solicitud</th>
            <th>Revisor</th>
            <th style="max-width: 20%;">Fecha revisión</th>
            <th>Fecha incio</th>
            <th>Fecha fin</th>
            <th>Fecha regreso</th>
            <th>Días efectivos</th>
            <th>Sub tipo</th>
            <th>Estatus</th>
        </thead>
        <tbody>
            <tr v-for="permission in lPermissions">
                <td>@{{permission.id_application}}</td>
                <td>@{{permission.request_status_id}}</td>
                <td>@{{permission.emp_comments_n}}</td>
                <td>@{{permission.sup_comments_n}}</td>
                <td>@{{permission.user_apr_rej_id}}</td>
                <td>@{{permission.id_permission_tp}}</td>
                <td>@{{permission.permission_tp_name}}</td>
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
                <td>@{{oDateUtils.formatDate(permission.end_date, 'ddd DD-MMM-YYYY')}}</td>
                <td>@{{oDateUtils.formatDate(permission.return_date, 'ddd DD-MMM-YYYY')}}</td>
                <td>@{{permission.total_days}}</td>
                <td>SUBTIPO</td>
                <td>@{{
                        !isRevision ? permission.applications_st_name : 
                            (permission.request_status_id == 2 ? 'NUEVO' : permission.applications_st_name)
                    }}
                </td>
            </tr>
        </tbody>
    </table>
</div>