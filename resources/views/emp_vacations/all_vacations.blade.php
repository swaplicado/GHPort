@extends('layouts.principal')

@section('headJs')
    <script>
        function GlobalData(){
            this.lEmployees = <?php echo json_encode($lEmployees); ?>;
            this.year = <?php echo json_encode($year); ?>;
            this.getPeriodRoute = <?php echo json_encode(route('allVacations_getPeriod')); ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:todasvacaciones" ); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="allVacations">
    <div class="card-header">
        <h3>
            <b>REPORTE VACACIONES</b>
            @include('layouts.manual_button')
            <div style="float: right;">
                <h4>Periodo: @{{period}}</h4>
            </div>
        </h3>
    </div>
    <div class="card-body">
        <div class="col-md-7" style="float: right; text-align: right; padding-right: 0 !important;">
            <button class="btn btn-info" v-on:click="showCompletePeriod()">Todos los periodos</button>&nbsp;&nbsp;
            <label>Ver a partir del año:</label>
            <button v-on:click="year = year - 1;" class="btn btn-secondary" type="button" style = "display: inline;">
                <span class="bx bx-minus" ></span>
            </button>
            <input type="number" class="form-control" v-model="year" readonly style="width: 10ch; display: inline;">
            <button v-on:click="year = year + 1;" class="btn btn-secondary" type="button" style = "display: inline;">
                <span class="bx bx-plus"></span>
            </button>
            <button type="button" class="btn btn-primary"  v-on:click="filterYear();">
                <span class="bx bx-search"></span>
            </button>
        </div>
        <br>
        <br>
        <table class="table table-bordered" id="vacationsTable" style="width: 100%;">
            <thead class="thead-light">
                <th>emp_id</th>
                <th>Nombre</th>
                <th>Tot. Vac. ganadas</th>
                <th>Tot. Vac. gozadas</th>
                <th>Tot. Vac. vencidas</th>
                <th>Tot. Vac. solicitadas</th>
                <th>Tot. Vac. pendientes</th>
            </thead>
            <tbody>
                <tr v-for="emp in lEmployees">
                    <td>@{{emp.id}}</td>
                    <td>@{{emp.full_name_ui}}</td>
                    <td>@{{emp.tot_vacation_days}}</td>
                    <td>@{{emp.tot_vacation_taken}}</td>
                    <td>@{{emp.tot_vacation_expired}}</td>
                    <td>@{{emp.tot_vacation_request}}</td>
                    <td>@{{emp.tot_vacation_remaining}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
@include('layouts.table_jsControll', [
                                        'table_id' => 'vacationsTable',
                                        'colTargets' => [0],
                                        'colTargetsSercheable' => [],
                                        // 'noSearch' => true,
                                        // 'noDom' => true,
                                        // 'noPaging' => true,
                                        // 'noInfo' => true,
                                        // 'noColReorder' => true,
                                        // 'noSort' => true
                                    ] )
@include('layouts.manual_jsControll')
<script type="text/javascript" src="{{ asset('myApp/emp_vacations/vue_all_vacations.js') }}"></script>
@endsection