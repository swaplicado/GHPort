@extends('layouts.principal')

@section('headJs')
    <script>
        function GlobalData(){
            this.logs = <?php echo json_encode($logs); ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:bitacoras#bitacora_plan_de_vacaciones" ); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="logs">
    <div class="card-header">
        <h3>
            <b>Bitácora plan de vacaciones (días)</b>
            @include('layouts.manual_button')
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
    @include('layouts.manual_jsControll')
@endsection