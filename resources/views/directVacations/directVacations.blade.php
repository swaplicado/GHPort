@extends('layouts.principal')

@section('headStyles')
    <link href={{ asset('select2js/css/select2.min.css') }} rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('daterangepicker/daterangepicker.min.css') }}">
    <!-- Standalone -->
    <link href="{{ asset('myApp/Utils/SDatePicker/css/datepicker.min.css')}}" rel="stylesheet" />
    <!-- For Bootstrap 4 -->
    <link href="{{ asset('myApp/Utils/SDatePicker/css/datepicker-bs4.min.css')}}" rel="stylesheet" />
    <!-- For Bulma -->
    <link href="{{ asset('myApp/Utils/SDatePicker/css/datepicker-bulma.min.css')}}" rel="stylesheet" />
    <!-- For Foundation -->
    <link href="{{ asset('myApp/Utils/SDatePicker/css/datepicker-foundation.min.css')}}" rel="stylesheet" />

    <style>
        .swal2-title {
            font-size: 24px !important;
        }
    </style>
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script src="{{ asset('daterangepicker/jquery.daterangepicker.min.js') }}" type="text/javascript"></script>
    <script>
        var app;
        var self;
        function GlobalData() {
            this.lEmployees = <?php echo json_encode($lEmployees); ?>;
            this.getEmployeeDataRoute = <?php echo json_encode(route('vacationManagement_getEmployeeData')); ?>;
            this.lHolidays = <?php echo json_encode($lHolidays); ?>;
            this.const = <?php echo json_encode($constants); ?>;
            this.applicationsEARoute = <?php echo json_encode(route('myVacations_getEmpApplicationsEA')); ?>;
            this.calcReturnDate = <?php echo json_encode(route('myVacations_calcReturnDate')); ?>;
            this.requestVacRoute = <?php echo json_encode(route('myVacations_setRequestVac')); ?>;
            this.updateRequestVacRoute = <?php echo json_encode(route('myVacations_updateRequestVac')); ?>;
            this.getlDaysRoute = <?php echo json_encode(route('myVacations_getlDays')); ?>;
            this.deleteRequestRoute = <?php echo json_encode(route('myVacations_delete_requestVac')); ?>;
            this.directVacationsApprobeRoute = <?php echo json_encode(route('directVacations_approbe')); ?>;
            this.indexesMyRequestTable = {
                'id':0,
                'request_status_id':1,
                'take_holidays':2,
                'take_rest_days':3,
                'comments':4,
                'user_apr_rej_id':5,
                'application_vs_type_id':6,
                'folio':7,
                'request_date':8,
                'user_apr_rej_name':9,
                'accept_reject_date':10,
                'start_date':11,
                'end_date':12,
                'return_date':13,
                'taked_days':14,
                'type':15,
                'status':16,
                'sup_comments':17,
            };
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content')
    <div>
        <div class="card shadow mb-4" id="directVacation">
            <div class="card-header">
                <h3>
                    <b>Gestión de vacaciones para todos los colaboradores</b>
                    @include('layouts.manual_button')
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="inline">
                        <div class="wrap">
                            <div class="elem">
                                <label for="" style="padding-top: 5px">Selecciona colaborador:</label>
                            </div>
                        </div>
                    </div>
                    <div class="inline">
                        <div class="wrap" style="min-width: 25rem">
                            <div class="elem">
                                <select class="select2-class" id="selectEmp" style="width: 100%"></select>
                            </div>
                        </div>
                    </div>
                    <div class="inline">
                        <div class="wrap">
                            <div class="elem">
                                <button class="btn btn-primary" v-on:click="getEmployeeData();">Ver vacaciones</button>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <div v-if="oUser != null">
                    @include('emp_vacations.modal_myRequest')
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h3>Vacaciones: @{{ oUser.employee }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="col-md-6 card border-left-primary">
                                <table style="margin-left: 10px;">
                                    <thead>
                                        <th></th>
                                        <th></th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th>Nombre:</th>
                                            <td>@{{ oUser.full_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Fecha ingreso:</th>
                                            <td>@{{ oDateUtils.formatDate(oUser.last_admission_date) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Antigüedad:</th>
                                            <td>@{{ oUser.antiquity }} al día de hoy</td>
                                        </tr>
                                        <tr>
                                            <th>Departamento:</th>
                                            <td>@{{ oUser.department_name_ui }}</td>
                                        </tr>
                                        <tr>
                                            <th>Puesto:</th>
                                            <td>@{{ oUser.job_name_ui }}</td>
                                        </tr>
                                        <tr>
                                            <th>Plan de vacaciones:</th>
                                            <td>@{{ oUser.vacation_plan_name }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-12">
                                    <div style="float: right;">
                                        <button class="btn btn-primary" v-on:click="getHistoryVac('vacationsTable');">Ver
                                            historial</button>
                                        <button class="btn btn-secondary"
                                            v-on:click="hiddeHistory('vacationsTable');">Ocultar historial</button>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <table class="table table-bordered" ref="vacationsTable" id="vacationsTable"
                                style="width: 100%;">
                                <thead class="thead-light">
                                    <th class="no-sort">Periodo</th>
                                    <th>Aniversario</th>
                                    <th class="no-sort">Vac. ganadas</th>
                                    <th class="no-sort">Vac. gozadas</th>
                                    <th class="no-sort">Vac. vencidas</th>
                                    <th class="no-sort">Vac. solicitadas</th>
                                    <th class="no-sort">Vac. pendientes</th>
                                </thead>

                            </table>
                        </div>
                    </div>
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h3>
                                Solicitudes vacaciones: @{{ oUser.employee }}
                                @include('layouts.manual_button')
                            </h3>
                        </div>
                        <div class="card-body">
                            @include('layouts.table_buttons', [
                                'crear' => true,
                                'editar' => true,
                                'delete' => true,
                                'sendAprov' => true,
                            ])
                            <div class="col-md-7" style="float: right; text-align: right; padding-right: 0 !important;">
                                <label for="myRqStatus">Filtrar por estatus: </label>
                                <select class="form-control inline" v-on:change="filterMyVacationTable();" name="myRqStatus" id="myRqStatus" style="width: 30%;">
                                    @foreach($lGestionStatus as $st)
                                        @if($st->id == 1)
                                            <option value="{{$st->id}}" selected>{{$st->name}}</option>
                                        @else
                                            <option value="{{$st->id}}">{{$st->name}}</option>
                                        @endif
                                    @endforeach
                                </select>&nbsp;&nbsp;
                                <label>Filtrar por año:</label>
                                <button v-on:click="year = year - 1;" class="btn btn-secondary" type="button"
                                    style="display: inline;">
                                    <span class="bx bx-minus"></span>
                                </button>
                                <input type="number" class="form-control" v-model="year" readonly
                                    style="width: 10ch; display: inline;">
                                <button v-on:click="year = year + 1;" class="btn btn-secondary" type="button"
                                    style="display: inline;">
                                    <span class="bx bx-plus"></span>
                                </button>
                                <button type="button" class="btn btn-primary" v-on:click="filterYear();">
                                    <span class="bx bx-search"></span>
                                </button>
                            </div>
                            <br>
                            <br>
                            <table class="table table-bordered" ref="table_myRequest" id="table_myRequest"
                                style="width: 100%;">
                                <thead class="thead-light">
                                <th>id</th>
                                <th>request_status_id</th>
                                <th>take_holidays</th>
                                <th>take_rest_days</th>
                                <th>emp coment.</th>
                                <th>Usuario apr/rec id</th>
                                <th>application vs type id</th>
                                <th>Folio</th>
                                <th>Fecha solicitud</th>
                                <th>Revisor</th>
                                <th style="max-width: 20%;">Fecha revisión</th>
                                <th>Fecha incio</th>
                                <th>Fecha fin</th>
                                <th>Fecha regreso</th>
                                <th>Dias efectivos</th>
                                <th>Tipo</th>
                                <th>Estatus</th>
                                <th>sup coment.</th>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        moment.locale('es');
        $(document).ready(function() {
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    let myRqStatusVal = parseInt($('#myRqStatus').val(), 10);
                    let myRqStatusfilter = 0;
                    if(settings.nTable.id == 'table_myRequest'){
                        switch (myRqStatusVal) {
                            case 1:
                                myRqStatusfilter = parseInt( data[oServerData.indexesMyRequestTable.request_status_id] );
                                return myRqStatusfilter === 1;
                                
                            case 2:
                                myRqStatusfilter = parseInt( data[oServerData.indexesMyRequestTable.request_status_id] );
                                return myRqStatusfilter === 2;

                            case 3:
                                myRqStatusfilter = parseInt( data[oServerData.indexesMyRequestTable.request_status_id] );
                                return myRqStatusfilter === 3 || myRqStatusfilter === 5;

                            case 4:
                                myRqStatusfilter = parseInt( data[oServerData.indexesMyRequestTable.request_status_id] );
                                return myRqStatusfilter === 4;

                            case 6:
                                myRqStatusfilter = parseInt( data[oServerData.indexesMyRequestTable.request_status_id] );
                                return myRqStatusfilter === 6;

                            default:
                                break;
                        }
                    }
                    return false;
                }
            );
        });
    </script>
    @include('layouts.table_jsControll', [
        'table_id' => 'vacationsTable',
        'colTargets' => [],
        'colTargetsSercheable' => [],
        'noSearch' => true,
        'noDom' => true,
        'noPaging' => true,
        'noInfo' => true,
        'noColReorder' => true,
        'noSort' => true,
        'order' => [[1, $config->orderVac]],
        'ordering' => true,
    ])
    
    <script>
        $(document).ready(function() {
            $('#myRqStatus').change(function() {
                table['table_myRequest'].draw();
            });
        });
    </script>
    <script type="text/javascript" src="{{ asset('myApp/Utils/SReDrawTables.js') }}"></script>
    <script type="text/javascript" src="{{ asset('myApp/Utils/SUsersUtils.js') }}"></script>
    <script type="text/javascript" src="{{ asset('myApp/emp_vacations/vacations_utils.js') }}"></script>
    <script type="text/javascript" src="{{ asset('myApp/emp_vacations/vue_direct_vac.js') }}"></script>
    <script src="{{ asset('myApp/Utils/SDatePicker/js/datepicker-full.min.js') }}"></script>
    <script src="{{ asset('myApp/Utils/SDateRangePickerUtils.js') }}"></script>
    <script>
        var elem = null;
        var datepicker = null;

        function createDatePicker(){
            var ReqElem = document.querySelector('input[name="datepicker"]');
            datepicker = new Datepicker(ReqElem, {
                language: 'es',
                format: 'dd/mm/yyyy',
                // minDate: null,
            });
    
            ReqElem.addEventListener('changeDate', function (e, details) { 
                app.setMyReturnDate();
            });
        }

        var oDateRangePickerForMyRequest;
        var dateRangePickerArrayApplications = [];
        var dateRangePickerArrayIncidences = [];
        var dateRangePickerArraySpecialSeasons = [];
        let dateRangePickerValid = true;
        var aniversaryDay = '';
        var birthday = '';

        function createDateRangePicker(payment_frec_id, payment){
            var oDateRangePickerForMyRequest = new SDateRangePicker();

            oDateRangePickerForMyRequest.setDateRangePicker(
                'two-inputs-myRequest',
                app.initialCalendarDate,
                app.oUser.payment_frec_id,
                oServerData.const.QUINCENA,
                'date-range200-myRequest',
                'date-range201-myRequest',
                'clear',
                oServerData.lHolidays
            );
        }

        function dateRangePickerSetValue(){
            if($('#date-range200-myRequest').val() && $('#date-range201-myRequest').val()){
                app.startDate = app.oDateUtils.formatDate($('#date-range200-myRequest').val(), 'ddd DD-MMM-YYYY');
                app.endDate = app.oDateUtils.formatDate($('#date-range201-myRequest').val(), 'ddd DD-MMM-YYYY');
                app.checkSelectDates();
            }else{
                app.startDate = '';
                app.endDate = '';
            }
            app.getDataDays();
        }

        function dateRangePickerGetValue(){
            if ($('#date-range200-myRequest').val() && $('#date-range201-myRequest').val() ){
                app.startDate = app.oDateUtils.formatDate($('#date-range200-myRequest').val());
                app.endDate = app.oDateUtils.formatDate($('#date-range201-myRequest').val());
                app.getDataDays();
            }
        }

        function dateRangePickerClearValue(){
            app.returnDate = null;
        }

        function mySendAprove(){
            if (table['table_myRequest'].row('.selected').data() == undefined) {
                SGui.showError("Debe seleccionar un renglón");
                return;
            }
    
            app.sendAprove(table['table_myRequest'].row('.selected').data());
        }
    </script>
@endsection
