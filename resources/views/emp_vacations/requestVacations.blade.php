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
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2-class').select2({});
        })
    </script>
    <script src="{{ asset('daterangepicker/jquery.daterangepicker.min.js') }}" type="text/javascript"></script>
    <script>
        var app;

        function GlobalData() {
        //data para ambas vistas
            this.year = <?php echo json_encode($year); ?>;
            this.lHolidays = <?php echo json_encode($lHolidays); ?>;
            this.const = <?php echo json_encode($constants); ?>;
            this.getUserDataRoute = <?php echo json_encode(route('getUserData')); ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:solicitudesvacaciones" ); ?>;
            this.manualRoute[1] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:gestionvacaciones" ); ?>;
            this.manualRoute[2] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:misvacaciones#solicitud_de_vacaciones" ); ?>;

        //data para la vista requestVacations
            this.myManagers = <?php echo json_encode($myManagers); ?>;
            this.lEmployees = <?php echo json_encode($lEmployees); ?>;
            this.acceptRequestRoute = <?php echo json_encode(route('requestVacations_acceptRequest')); ?>;
            this.rejectRequestRoute = <?php echo json_encode(route('requestVacations_rejectRequest')); ?>;
            this.filterYearRoute = <?php echo json_encode(route('requestVacations_filterYear')); ?>;
            this.checkMailRoute = <?php echo json_encode(route('requestVacations_checkMail')); ?>;
            this.applicationsEARoute = <?php echo json_encode(route('requestVacations_getEmpApplicationsEA')); ?>;
            this.getDataManagerRoute = <?php echo json_encode(route('requestVacations_getDataManager')); ?>;
            this.getRequestlDaysRoute = <?php echo json_encode(route('requestVacations_getlDays')); ?>;
            this.idApplication = <?php echo json_encode($idApplication); ?>;
            this.oApplication = <?php echo json_encode($oApplication); ?>;
            this.getApplicationRoute = <?php echo json_encode(route('requestVacations_getApplication')); ?>;
            //Al agregar un nuevo index no olvidar agregarlo en la funcion reDraw de vue
            this.indexesRequest = {
                'id': 0,
                'user_id': 1,
                'birthday': 2,
                'benefits_date': 3,
                'payment_frec_id': 4,
                'request_status_id': 5,
                'take_holidays': 6,
                'take_rest_days': 7,
                'sup_comments': 8,
                'user_apr_rej_id': 9,
                'employee': 10,
                'folio': 11,
                'created_at': 12,
                'user_apr_rej_name': 13,
                'approved_date': 14,
                'start_date': 15,
                'end_date': 16,
                'return_date': 17,
                'total_days': 18,
                'type': 19,
                'applications_st_name': 20,
                'comments': 21,
            };

        //data para la vista my_vacations
            this.getEmployeeDataRoute = <?php echo json_encode(route('vacationManagement_getEmployeeData')); ?>;
            this.myVacations_filterYearRoute = <?php echo json_encode(route('myVacations_filterYear')); ?>;
            this.getMyVacationHistoryRoute = <?php echo json_encode(route('myVacations_getMyVacationHistory')); ?>;
            this.hiddeHistoryRoute = <?php echo json_encode(route('myVacations_hiddeHistory')); ?>;
            this.requestVacRoute = <?php echo json_encode(route('myVacations_setRequestVac')); ?>;
            this.updateRequestVacRoute = <?php echo json_encode(route('myVacations_updateRequestVac')); ?>;
            this.deleteRequestRoute = <?php echo json_encode(route('myVacations_delete_requestVac')); ?>;
            this.sendRequestRoute = <?php echo json_encode(route('myVacations_send_requestVac')); ?>;
            this.getDirectEmployeesRoute = <?php echo json_encode(route('vacationManagement_getDirectEmployees')); ?>;
            this.getAllEmployeesRoute = <?php echo json_encode(route('vacationManagement_getAllEmployees')); ?>;
            this.getlDaysRoute = <?php echo json_encode(route('myVacations_getlDays')); ?>;
            this.quickSendRoute = <?php echo json_encode(route('requestVacations_quickSend')); ?>;
            this.quickDataRoute = <?php echo json_encode(route('requestVacations_quickData')); ?>;
            //Al agregar un nuevo index no olvidar agregarlo en la funcion reDraw de vue
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
    <a class="btn btn-outline-secondary focus" id="requestVac" onclick="btnActive('requestVac');" href="#home"
        data-role="link">Solicitudes de vacaciones</a>
    <a class="btn btn-outline-secondary" id="gestionVac" onclick="btnActive('gestionVac');" href="#other"
        data-role="link">Gestión de vacaciones</a>
    {{-- Vista solicitudes de vacaciones --}}
    <div data-page="home" id="home" class="active">
        <div class="card shadow mb-4" id="requestVacations">
            @include('emp_vacations.modal_requests')
            <div class="card-header">
                <h3>
                    <b>Solicitudes vacaciones</b>
                    @include('layouts.manual_button')
                </h3>
            </div>
            <div class="card-body">
                <div v-if="myManagers.length > 0" class="row">
                    <div class="col-md-1">
                        <label for="selManager">Ver como:</label>
                    </div>
                    <div class="col-md-3">
                        <select class="select2-class form-control" id="selManager"></select>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-primary" v-on:click="seeLikeManager();">Ver solicitudes</button>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-secondary" v-on:click="cleanManager();">Limpiar</button>
                    </div>
                </div>
                <br>
                <button id="btn_getApp" v-on:click="getApplication()" type="button" class="btn3d bg-gray-400" style="display: inline-block; margin-right: 5px" title="Ver solicitud">
                    <span class="bx bx-show-alt"></span>
                </button>
                <div class="col-md-9" style="float: right; text-align: right; padding-right: 0 !important;">
                    &nbsp;&nbsp;
                    <label for="rqStatus">Filtrar por estatus: </label>
                    <select class="form-control inline" name="rqStatus" id="rqStatus" v-model="rqStatus" style="width: 30%;">
                        <option value="0" selected>Nuevos</option>
                        <option value="1">Aprobados</option>
                        <option value="2">Rechazados</option>
                    </select>&nbsp;&nbsp;
                    <template v-if="rqStatus != 0">
                        <label>Filtrar por año:</label>
                        <button v-on:click="year = year - 1;" class="btn btn-secondary" type="button" style="display: inline;">
                            <span class="bx bx-minus"></span>
                        </button>
                        <input type="number" class="form-control" v-model="year" readonly
                            style="width: 10ch; display: inline;">
                        <button v-on:click="year = year + 1;" class="btn btn-secondary" type="button" style="display: inline;">
                            <span class="bx bx-plus"></span>
                        </button>
                        <button type="button" class="btn btn-primary" v-on:click="filterYear();">
                            <span class="bx bx-search"></span>
                        </button>
                    </template>
                </div>
                <br>
                <br>
                <table class="table table-bordered" id="table_requestVac" style="width: 100%;">
                    <thead class="thead-light">
                        <th>id</th>
                        <th>user_id</th>
                        <th>birthday</th>
                        <th>benefits_date</th>
                        <th>emp_frecuency_pay</th>
                        <th>request_status_id</th>
                        <th>take_holidays</th>
                        <th>take_rest_days</th>
                        <th>sup comments</th>
                        <th>Usuario apr/rec id</th>
                        <th>Empleado</th>
                        <th>Folio</th>
                        <th>Fecha solicitud</th>
                        <th>Revisor</th>
                        <th style="max-width: 15%;">Fecha Revisión</th>
                        <th>Fecha inicio</th>
                        <th>Fecha fin</th>
                        <th>Fecha regreso</th>
                        <th>Dias efectivos</th>
                        <th>Tipo</th>
                        <th>Estatus</th>
                        <th>coment.</th>
                    </thead>
                    <tbody>
                        <template v-for="emp in lEmployees">
                            <template v-for="rec in emp.applications">
                                <tr v-bind:class="[ checkIsSelectable(rec) ]" :style="{ background: (rec.request_status_id == 3 ? '#E8F5E9' : (rec.request_status_id == 4 ? '#FCE4EC' : '')) }">
                                    <td>@{{ rec.id_application }}</td>
                                    <td>@{{ rec.user_id }}</td>
                                    <td>@{{ emp.birthday_n }}</td>
                                    <td>@{{ emp.benefits_date }}</td>
                                    <td>@{{ emp.payment_frec_id }}</td>
                                    <td>@{{ rec.request_status_id }}</td>
                                    <td>@{{ rec.take_holidays }}</td>
                                    <td>@{{ rec.take_rest_days }}</td>
                                    <td>@{{ rec.sup_comments_n }}</td>
                                    <td>@{{ rec.user_apr_rej_id }}</td>
                                    <td>@{{ emp.employee }}</td>
                                    <td>@{{ rec.folio_n }}</td>
                                    <td>@{{ oDateUtils.formatDate(rec.created_at, 'ddd DD-MMM-YYYY') }}</td>
                                    <td>@{{ rec.user_apr_rej_name }}</td>
                                    <td>
                                        @{{ (rec.request_status_id == oData.const.APPLICATION_CONSUMIDO ||
                                                rec.request_status_id == oData.const.APPLICATION_CONSUMIDO
                                            ) ?
    oDateUtils.formatDate(rec.approved_date_n, 'ddd DD-MMM-YYYY'):
        ((rec.request_status_id == oData.const.APPLICATION_RECHAZADO) ?
            oDateUtils.formatDate(rec.rejected_date_n, 'ddd DD-MMM-YYYY') :
            '') }}
                                    </td>
                                    <td>@{{ oDateUtils.formatDate(rec.start_date, 'ddd DD-MMM-YYYY') }}</td>
                                    <td>@{{ oDateUtils.formatDate(rec.end_date, 'ddd DD-MMM-YYYY') }}</td>
                                    <td>@{{ oDateUtils.formatDate(rec.return_date, 'ddd DD-MMM-YYYY') }}</td>
                                    <td>@{{ rec.total_days }}</td>
                                    <td>@{{ specialType(rec) }}</td>
                                    <td>@{{ rec.request_status_id == 2 ? 'NUEVO' : (rec.applications_st_name == 'CONSUMIDO' ? 'APROBADO' : rec.applications_st_name) }}</td>
                                    <td>@{{ rec.emp_comments_n }}</td>
                                </tr>
                            </template>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Vista gestióin de vacaciones mis colaboradores --}}
    <div data-page="other" id="other">
        <div class="card shadow mb-4" id="myVacations">
            <div class="card-header">
                <h3>
                    <b>Gestión de vacaciones mis colaboradores</b>
                    @include('layouts.manual_button')
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="inline">
                        <div class="wrap">
                            <div class="elem">
                                <div class="ks-cboxtags">
                                    <div class="ks-cbox">
                                        <input type="checkbox" id="checkBoxAllEmployees" v-on:click="getAllEmployees();">
                                        <label for="checkBoxAllEmployees">Todos los colaboradores</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                            ])
                            <button id="btn_sendAprov" onclick="mySendAprove();" type="button" class="btn3d" style="display: inline-block; margin-right: 5px; background-color: #4DB6AC" title="Enviar y autorizar">
                                <span class="bx bxs-send"></span>
                            </button>
                            <div class="col-md-7" style="float: right; text-align: right; padding-right: 0 !important;">
                                <label for="myRqStatus">Filtrar por estatus: </label>
                                <select class="form-control inline" v-on:change="filterMyVacationTable();" name="myRqStatus" id="myRqStatus" style="width: 30%;">
                                    <option value="0" selected>Creados</option>
                                    <option value="1">Enviados</option>
                                    <option value="2">Aprobados</option>
                                    <option value="3">Rechazados</option>
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
                    let registerVal = parseInt($('#rqStatus').val(), 10);
                    let filter = 0;
                    if (settings.nTable.id == 'table_requestVac'){
                        switch (registerVal) {
                            case 0:
                                filter = parseInt(data[oServerData.indexesRequest.request_status_id]);
                                return filter === 2;
    
                            case 1:
                                filter = parseInt(data[oServerData.indexesRequest.request_status_id]);
                                return filter === 3 || filter === 5;
    
                            case 2:
                                filter = parseInt(data[oServerData.indexesRequest.request_status_id]);
                                return filter === 4;

                            case 3:
                                filter = parseInt(data[oServerData.indexesRequest.request_status_id]);
                                return filter === 5;
    
                            default:
                                break;
                        }
                    }

                    let myRqStatusVal = parseInt($('#myRqStatus').val(), 10);
                    let myRqStatusfilter = 0;
                    if(settings.nTable.id == 'table_myRequest'){
                        switch (myRqStatusVal) {
                            case 0:
                                myRqStatusfilter = parseInt( data[oServerData.indexesMyRequestTable.request_status_id] );
                                return myRqStatusfilter === 1;
                                
                            case 1:
                                myRqStatusfilter = parseInt( data[oServerData.indexesMyRequestTable.request_status_id] );
                                return myRqStatusfilter === 2;

                            case 2:
                                myRqStatusfilter = parseInt( data[oServerData.indexesMyRequestTable.request_status_id] );
                                return myRqStatusfilter === 3 || myRqStatusfilter === 5;

                            case 3:
                                myRqStatusfilter = parseInt( data[oServerData.indexesMyRequestTable.request_status_id] );
                                return myRqStatusfilter === 4;

                            case 4:
                                myRqStatusfilter = parseInt( data[oServerData.indexesMyRequestTable.request_status_id] );
                                return myRqStatusfilter === 5;

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
        'table_id' => 'table_requestVac',
        'colTargets' => [0, 1, 2, 3, 4, 6, 7, 8, 9, 21],
        'colTargetsSercheable' => [5],
        'select' => true,
        'noSort' => true,
        'accept' => true,
        'reject' => true,
    ])

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
    @include('layouts.manual_jsControll')
    <script>
        $(document).ready(function() {
            $('#rqStatus').change(function() {
                table['table_requestVac'].draw();
            });

            var search = document.querySelectorAll('input[type=search]');
            // if (app.oApplication != null) {
            //     app.showModal();
            //     // table['table_requestVac'].columns(0).search("(^" + app.idApplication + "$)", true, false).draw();
            //     // table['table_requestVac'].columns(0).search("", true, true);
            //     // search[0].value = app.idApplication;
            // }
        });
    </script>
    <script type="text/javascript" src="{{ asset('myApp/Utils/SReDrawTables.js') }}"></script>
    <script type="text/javascript" src="{{ asset('myApp/Utils/SUsersUtils.js') }}"></script>
    <script type="text/javascript" src="{{ asset('myApp/emp_vacations/vacations_utils.js') }}"></script>
    <script type="text/javascript" src="{{ asset('myApp/emp_vacations/vue_request_vacations.js') }}"></script>
    <script type="text/javascript" src="{{ asset('myApp/emp_vacations/vue_my_vacations.js') }}"></script>
    <script src="{{ asset('myApp/Utils/SDatePicker/js/datepicker-full.min.js') }}"></script>
    <script src="{{ asset('myApp/Utils/SDateRangePickerUtils.js') }}"></script>
    <script>
        const btn_ids = ['requestVac', 'gestionVac'];
        app = appRequestVacation;

        function btnActive(id) {
            let btn = document.getElementById(id);
            btn.style.backgroundColor = '#858796';
            btn.style.color = '#fff';

            for (const bt_id of btn_ids) {
                if (bt_id != id) {
                    let bt = document.getElementById(bt_id);
                    bt.style.backgroundColor = '#fff';
                    bt.style.color = '#858796';
                    bt.style.boxShadow = '0 0 0';
                }
            }

            if (id == 'requestVac') {
                app = appRequestVacation;
                app.initView();
            } else if (id == 'gestionVac') {
                app = appMyVacations;
                app.initView(appRequestVacation.lEmployees);
            }
        }
    </script>
    <script>
        (function() {
            let pages = [];
            let links = [];

            document.addEventListener("DOMContentLoaded", function() {
                pages = document.querySelectorAll('[data-page]');
                links = document.querySelectorAll('[data-role="link"]');
                [].forEach.call(links, function(link) {
                    link.addEventListener("click", navigate)
                });
            });

            function navigate(ev) {
                ev.preventDefault();
                let id = ev.currentTarget.href.split("#")[1];
                [].forEach.call(pages, function(page) {
                    if (page.id === id) {
                        page.classList.remove('noActive');
                        page.classList.add('active');
                    } else {
                        page.classList.remove('active');
                        page.classList.add('noActive');
                    }
                });
                return false;
            }
        })();
    </script>
    <script>
        var elem = null;
        var datepicker = null;

        var ReqElem = document.querySelector('input[name="reqDatepicker"]');
        var ReqDatepicker = new Datepicker(ReqElem, {
            language: 'es',
            format: 'dd/mm/yyyy',
            // minDate: null,
        });

        ReqElem.addEventListener('changeDate', function (e, details) { 
            app.setMyReturnDate();
        });

        var oDateRangePicker = new SDateRangePicker();
        var oDateRangePickerForMyRequest;
        var dateRangePickerArrayApplications = [];
        var dateRangePickerArraySpecialSeasons = [];
        let dateRangePickerValid = true;
        var aniversaryDay = '';
        var birthday = '';
        $(document).ready(function() {
            oDateRangePicker.setDateRangePickerWithSelectDataTable(
                'two-inputs',
                'table_requestVac',
                'date-range200',
                'date-range201',
                oServerData.indexesRequest.payment_frec_id,
                oServerData.const.QUINCENA,
                oServerData.lHolidays
            );
        });

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
    <script>
        $(document).ready(function(){
            if (app.oApplication != null) {
                app.showModal();
            }
        });
    </script>
@endsection
