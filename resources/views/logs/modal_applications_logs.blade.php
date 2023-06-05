<div class="modal fade" id="modal_application_log" tabindex="-1" role="dialog" aria-labelledby="application"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="application">Bitácora solicitud de vacaciones</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered" style="width: 100%">
                    <thead class="thead-light">
                        <th>Creado por</th>
                        <th>Fecha</th>
                        <th>Estatus</th>
                    </thead>
                    <tbody>
                        <tr v-for="appLog in lApplicationLogs">
                            <td>@{{appLog.created_by_name}}</td>
                            <td>@{{appLog.created_at}}</td>
                            <td>@{{appLog.status}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>