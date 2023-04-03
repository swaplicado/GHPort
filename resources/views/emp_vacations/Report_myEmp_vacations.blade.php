@extends('layouts.principal')

@section('headJs')
    <script>
        function GlobalData(){
            this.lEmployees = <?php echo json_encode($lEmployees); ?>;
            this.year = <?php echo json_encode($year); ?>;
            this.lLevels= <?php echo json_encode($lLevels); ?>;
            this.getDownLevelRoute = <?php echo json_encode(route('report_getLevelDown')); ?>;
            this.getUpLevelRoute = <?php echo json_encode(route('report_getLevelUp')); ?>;
            this.myEmpVacationsFilterYearRoute = <?php echo json_encode(route('report_myEmpVacationsFilterYear')); ?>;
            this.indexes = {
                'id': 0,
                'employee': 1,
                'tot_vacation_days': 2,
                'tot_vacation_taken': 3,
                'tot_vacation_expired': 4,
                'tot_vacation_request': 5,
                'tot_vacation_remaining': 6,
            };
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:todasvacaciones" ); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="myEmpVacations">
    <div class="card-header">
        <h3>
            <b>Reporte vacaciones de mis colaboradores</b>
            @include('layouts.manual_button')
            <div style="float: right;">
                <h4>Periodo: @{{period}}</h4>
            </div>
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-5">
                <h5>
                <button class="btn3d btn-secondary" style="display: inline-block; margin-right: 5px"
                        title="Nivel inferior" v-on:click="getLevelDown();">
                    <span class="bx bxs-arrow-from-top"></span>
                </button>
                <button class="btn3d btn-secondary" style="display: inline-block; margin-right: 5px"
                        title="Nivel superior" v-on:click="getLevelUp();">
                    <span class="bx bxs-arrow-from-bottom"></span>
                </button>
                &nbsp
                &nbsp
                <span><b>Estas visualizando:</b> @{{seeLevel}}</span>
                </h5>
            </div>
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
                    <td>@{{emp.employee}}</td>
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
<script type="text/javascript" src="{{ asset('myApp/emp_vacations/vue_report_myEmpVacations.js') }}"></script>
@include('layouts.manual_jsControll')
@endsection