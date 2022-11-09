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
            <b>BITACORA PLAN DE VACACIONES (DÍAS)</b>
            <a href="#" target="_blank">
                <span class="bx bx-question-mark btn3d" style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
            </a>
        </h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered" id="table_vacation_plan_days_logs" style="width: 100%">
            <thead class="thead-light">
                <th>id_vacation_plan_day_log</th>
                <th>vacations_plan_id</th>
                <th>Plan de vacaciones</th>
                <th>Año</th>
                <th>Días de vacaciones</th>
                <th>Creado por</th>
                <th>Fecha creado</th>
                <th>Fecha actualizado</th>
            </thead>
            <tbody>
                <tr v-for="log in logs">
                    <td>@{{log.id_vacation_plan_day_log}}</td>
                    <td>@{{log.vacations_plan_id}}</td>
                    <td>@{{log.vacation_plan_name}}</td>
                    <td>@{{log.until_year}}</td>
                    <td>@{{log.vacation_days}}</td>
                    <td>@{{log.full_name_ui}}</td>
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
                                        'table_id' => 'table_vacation_plan_days_logs',
                                        'colTargets' => [0,1],
                                        'colTargetsSercheable' => [],
                                        'noSort' => true,
                                    ])
    <script type="text/javascript" src="{{ asset('myApp/Adm/vue_vacation_plan_days_log.js') }}"></script>
@endsection