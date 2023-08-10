<div class="table-responsive">
    <table class="table table-bordered" id="{{$table_id}}" ref="{{$table_ref}}" width="100%" cellspacing="0">
        <thead class="thead-light">
            <th>id</th>
            <th>incidence_status_id</th>
            <th>emp coment.</th>
            <th>sup coment.</th>
            <th>revisor_id</th>
            <th>incidence_cl_id</th>
            <th>incidence_type_id</th>
            <th>Empleado</th>
            <th>Incidencia</th>
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
            <th>fecha env</th>
        </thead>
        <tbody>
            <tr v-for="incident in lIncidences">
                <td>@{{incident.id_application}}</td>
                <td>@{{incident.request_status_id}}</td>
                <td>@{{incident.emp_comments_n}}</td>
                <td>@{{incident.sup_comments_n}}</td>
                <td>@{{incident.user_apr_rej_id}}</td>
                <td>@{{incident.id_incidence_cl}}</td>
                <td>@{{incident.id_incidence_tp}}</td>
                <td>@{{incident.employee}}</td>
                <td>@{{incident.incidence_tp_name}}</td>
                <td>@{{incident.folio_n}}</td>
                <td>
                    @{{
                        (incident.date_send_n != null ?
                            oDateUtils.formatDate(incident.date_send_n, 'ddd DD-MMM-YYYY') :
                                oDateUtils.formatDate(incident.updated_at, 'ddd DD-MMM-YYYY')
                            )
                    }}
                </td>
                <td>@{{incident.user_apr_rej_name}}</td>
                <td>
                    @{{
                        (incident.request_status_id == oData.constants.APPLICATION_APROBADO) ?
                            oDateUtils.formatDate(incident.approved_date_n, 'ddd DD-MMM-YYYY') :
                            ((incident.request_status_id == oData.constants.APPLICATION_RECHAZADO) ?
                                oDateUtils.formatDate(incident.rejected_date_n, 'ddd DD-MMM-YYYY') :
                                '')
                    }}
                </td>
                <td>@{{oDateUtils.formatDate(incident.start_date, 'ddd DD-MMM-YYYY')}}</td>
                <td>@{{oDateUtils.formatDate(incident.end_date, 'ddd DD-MMM-YYYY')}}</td>
                <td>@{{oDateUtils.formatDate(incident.return_date, 'ddd DD-MMM-YYYY')}}</td>
                <td>@{{incident.total_days}}</td>
                <td>SUBTIPO</td>
                <td>@{{
                        !isRevision ? incident.applications_st_name : 
                            (incident.request_status_id == 2 ? 'NUEVO' : incident.applications_st_name)
                    }}
                </td>
                <td>
                    @{{incident.date_send_n}}
                </td>
            </tr>
        </tbody>
    </table>
</div>