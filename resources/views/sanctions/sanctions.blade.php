@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
<link href={{asset('myApp/Utils/singleDateRangePicker/daterangepicker.css')}} rel="stylesheet" />
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
            this.lSanctions = <?php echo json_encode($lSanctions) ?>;
            this.oUser = <?php echo json_encode($oUser) ?>;
            this.myEmpRoute = <?php echo json_encode(route('sanctions_myEmployees')) ?>;
            this.allEmpRoute = <?php echo json_encode(route('sanctions_allEmployees')) ?>;
            this.mySanctionRoute = <?php echo json_encode(route('sanctions_getMySanction')) ?>;
            this.type = <?php echo json_encode($type) ?>;
            this.lTypes = <?php echo json_encode($lTypes) ?>;
            this.lRoles = <?php echo json_encode($lRoles) ?>;
            this.startDate = <?php echo json_encode($startDate) ?>;
            this.endDate = <?php echo json_encode($endDate) ?>;

            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:sanciones" ); ?>;

            this.indexesSanctionsTable = {
                'id_employee': 0,
                'num': 1,
                'date': 2,
                'title': 3,
                'description': 4,
                'offender': 5,
            };
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content')
<div id="admMinutesApp">

    <a class="btn btn-outline-secondary focus" id="empMinutes" href="#" v-on:click="setViewMode('empMinutes');" v-show="oUser.rol_id != lRoles.ESTANDAR">Sanciones mis colaboradores</a>
    <a class="btn btn-outline-secondary" id="myMinutes" href="#" v-on:click="setViewMode('myMinutes');" v-show="oUser.rol_id != lRoles.ESTANDAR">Mis sanciones</a>

    <div class="card shadow mb-4">
        <div class="card-header">
            <h3>
                <b v-if="viewMode == 'empMinutes'">Sanciones colaboradores</b>
                <b v-if="viewMode == 'myMinutes'">Mis sanciones</b>
                @include('layouts.manual_button')
            </h3>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="" v-show="viewMode == 'empMinutes' && oUser.rol_id != lRoles.ESTANDAR">
                    <div class="elem">
                        <div class="ks-cboxtags">
                            <div class="ks-cbox">
                                <input type="checkbox" id="checkBoxAllEmployees" v-on:click="getMinutes();">
                                <label for="checkBoxAllEmployees">Todos los colaboradores</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <input type="text" name="daterange" id="daterange" class="form-control"/>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary">
                        <span class="bx bx-search"></span>
                    </button>
                </div>
            </div>
            <br>
            <table class="table table-bordered" id="sanctions_table" style="width: 100%;">
                <thead class="thead-light">
                    <th>id_employee</th>
                    <th>Num</th>
                    <th>Fecha</th>
                    <th>Titulo</th>
                    <th>Descripción</th>
                    <th>Ofensor</th>
                    <tbody>
                        <tr v-for="san in lSanctions">
                            <td>@{{san.employee_id}}</td>
                            <td>@{{san.num}}</td>
                            <td>@{{san.startDate}}</td>
                            <td>@{{san.title}}</td>
                            <td>@{{san.description}}</td>
                            <td>@{{san.offender}}</td>
                        </tr>
                    </tbody>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript" src="{{ asset('myApp/Utils/singleDateRangePicker/daterangepicker.js') }}"></script>
<script>
    var self;
    $(document).ready(function () {
        
    });
</script>

@include('layouts.table_jsControll', [
                                        'table_id' => 'sanctions_table',
                                        'colTargets' => [0],
                                        'colTargetsSercheable' => [],
                                        // 'noDom' => true,
                                    ] )

@include('layouts.manual_jsControll')
<script type="text/javascript" src="{{ asset('myApp/sanctions/vue_sanctions.js') }}"></script>
<script>
    $(function() {
        var oDatePicker = $('input[name="daterange"]').daterangepicker({
                                opens: 'left',
                                locale: {
                                    format: 'DD [de] MMMM [de] YYYY',
                                    applyLabel: 'Aplicar',
                                    cancelLabel: 'Cancelar',
                                    fromLabel: 'Desde',
                                    toLabel: 'Hasta',
                                    customRangeLabel: 'Rango personalizado',
                                    weekLabel: 'S',
                                    daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
                                    monthNames: ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
                                    firstDay: 1
                                },
                                startDate: moment(oServerData.startDate),
                                endDate: moment(oServerData.endDate),
                            }, function(start, end, label) {
                                app.startDate = start.format('YYYY-MM-DD');
                                app.endDate = end.format('YYYY-MM-DD');
                                console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
                            });
    });
</script>
@endsection