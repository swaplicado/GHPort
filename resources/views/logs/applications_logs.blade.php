@extends('layouts.principal')

@section('headJs')
    <script>
        function GlobalData(){
            this.logs = <?php echo json_encode($logs); ?>;
            this.getApplicationLogsDataRoute = <?php echo json_encode(route('bitacoras_getApplicationLogsData')); ?>;
            this.indexes = {
                'id': 0,
                'folio': 1,
                'employee': 2,
                'created_at': 3,
                'dates_vac': 4,
                'effective_days': 5,
                'return_date': 6,
            };
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="logs">
    @include('logs.modal_applications_logs')
    <div class="card-header">
        <h3>
            <b>BITACORA SOLICITUDES VACACIONES</b>
            <a href="#" target="_blank">
                <span class="bx bx-question-mark btn3d" style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
            </a>
        </h3>
    </div>
    <div class="card-body">
        @include('layouts.table_buttons', ['show' => true])
        <br>
        <br>
        <table class="table table-bordered" id="table_applications" style="width: 100%">
            <thead class="thead-light">
                <th>application_id</th>
                <th>Folio</th>
                <th>Colaborador</th>
                <th>Fecha creacion</th>
                <th>Fechas Vac.</th>
                <th>Días efectivos</th>
                <th>Fecha regreso</th>
            </thead>
            <tbody>
                <tr v-for="log in logs">
                    <td>@{{log.application_id}}</td>
                    <td>@{{log.folio_n}}</td>
                    <td>@{{log.employee}}</td>
                    <td>@{{log.created_at}}</td>
                    <td>@{{log.start_date}} a @{{log.end_date}}</td>
                    <td>@{{log.total_days}}</td>
                    <td>@{{log.return_date}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
@include('layouts.table_jsControll', [
                                        'table_id' => 'table_applications',
                                        'colTargets' => [0,1],
                                        'colTargetsSercheable' => [],
                                        'noSort' => true,
                                        'select' => true,
                                        'show' => true,
                                    ])
    <script type="text/javascript" src="{{ asset('myApp/Adm/vue_application_log.js') }}"></script>
@endsection