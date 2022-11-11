@extends('layouts.principal')

@section('headJs')
    <script>
        function GlobalData(){
            this.logs = <?php echo json_encode($logs); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="logs">
    <div class="card-header">
        <h3>
            <b>BITACORA ADMISIÓN COLABORADORES</b>
            <a href="http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:bitacoras#bitacora_admision_colaboradores" target="_blank">
                <span class="bx bx-question-mark btn3d" style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
            </a>
        </h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered" id="table_admission_users" style="width: 100%">
            <thead class="thead-light">
                <th>id_user_admission_log</th>
                <th>Colaborador</th>
                <th>Fecha admisión</th>
                <th>Fecha salida</th>
                <th>Número admisiones</th>
                <th>Fecha creado</th>
                <th>Fecha actualizado</th>
            </thead>
            <tbody>
                <tr v-for="log in logs">
                    <td>@{{log.id_user_admission_log}}</td>
                    <td>@{{log.full_name_ui}}</td>
                    <td>@{{log.user_admission_date}}</td>
                    <td>@{{log.user_leave_date}}</td>
                    <td>@{{log.admission_count}}</td>
                    <td>@{{log.created_at}}</td>
                    <td>@{{log.updated_at}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
@include('layouts.table_jsControll', [
                                        'table_id' => 'table_admission_users',
                                        'colTargets' => [0],
                                        'colTargetsSercheable' => [],
                                        'noSort' => true,
                                    ])
    <script type="text/javascript" src="{{ asset('myApp/Adm/vue_vacation_user_log.js') }}"></script>
@endsection