@extends('layouts.principal')
@section('headStyles')
<link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.min.css")}}">

<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
<!-- Standalone -->
<link href="myApp/Utils/SDatePicker/css/datepicker.min.css" rel="stylesheet" />
<!-- For Bootstrap 4 -->
<link href="myApp/Utils/SDatePicker/css/datepicker-bs4.min.css" rel="stylesheet" />
<!-- For Bulma -->
<link href="myApp/Utils/SDatePicker/css/datepicker-bulma.min.css" rel="stylesheet" />
<!-- For Foundation -->
<link href="myApp/Utils/SDatePicker/css/datepicker-foundation.min.css" rel="stylesheet" />

<style>
    /* ul {
        -webkit-column-count: 3;
        -moz-column-count: 3;
        column-count: 3;
    } */

    input:invalid {
        color: red;
    }

    [type="date"]::-webkit-inner-spin-button {
    display: none;
    }
    [type="date"]::-webkit-calendar-picker-indicator {
    display: none;
    }
</style>
@endsection
@section('headJs')
    <script src="{{ asset("daterangepicker/jquery.daterangepicker.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("select2js/js/select2.min.js") }}"></script>
    <script>
        function GlobalData(){
            this.oUser = <?php echo json_encode($user); ?>;
            this.lSuperviser = <?php echo json_encode($lSuperviser); ?>;
            this.initialCalendarDate = <?php echo json_encode($initialCalendarDate); ?>;
            this.lHolidays = <?php echo json_encode($lHolidays); ?>;
            this.lTemp = <?php echo json_encode($lTemp); ?>;
            this.year = <?php echo json_encode($year); ?>;
            this.today = <?php echo json_encode($today); ?>;
            this.requestVacRoute = <?php echo json_encode(route('myVacations_setRequestVac')); ?>;
            this.updateRequestVacRoute = <?php echo json_encode(route('myVacations_updateRequestVac')); ?>;
            this.myVacations_filterYearRoute = <?php echo json_encode(route('myVacations_filterYear')); ?>;
            this.deleteRequestRoute = <?php echo json_encode(route('myVacations_delete_requestVac')); ?>;
            this.sendRequestRoute = <?php echo json_encode(route('myVacations_send_requestVac')); ?>;
            this.checkMailRoute = <?php echo json_encode(route('myVacations_checkMail')); ?>;
            this.applicationsEARoute = <?php echo json_encode(route('myVacations_getEmpApplicationsEA')); ?>;
            this.getlDaysRoute = <?php echo json_encode(route('myVacations_getlDays')); ?>;
            this.getMyVacationHistoryRoute = <?php echo json_encode(route('myVacations_getMyVacationHistory')); ?>;
            this.hiddeHistoryRoute = <?php echo json_encode(route('myVacations_hiddeHistory')); ?>;
            this.calcReturnDate = <?php echo json_encode(route('myVacations_calcReturnDate')); ?>;
            this.const = <?php echo json_encode($constants); ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:misvacaciones#mis_vacaciones" ); ?>;
            this.manualRoute[1] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:misvacaciones#solicitud_de_vacaciones" ); ?>;

            //Al modificar index no olvidar agregarlo en la funcion reDraw de vue
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
<div class="card shadow mb-4" id="myVacations">
    @include('emp_vacations.modal_myRequest')
    <div class="card-body">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h3>
                    <b>Mis vacaciones</b>
                    @include('layouts.manual_button')
                </h3>
            </div>
            <div>
                <div class="card-body" v-if="oUser != null">
                    <div class="col-md-6 card border-left-primary">
                        <table style="margin-left: 10px;">
                            <thead>
                                <th></th>
                                <th></th>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Nombre:</th>
                                    <td>@{{oUser.full_name}}</td>
                                </tr>
                                <tr>
                                    <th>Fecha ingreso:</th>
                                    <td>@{{oDateUtils.formatDate(oUser.benefits_date)}}</td>
                                </tr>
                                <tr>
                                    <th>Antigüedad:</th>
                                    <td>@{{oUser.antiquity}} al día de hoy</td>
                                </tr>
                                <tr>
                                    <th>Departamento:</th>
                                    <td>@{{oUser.department_name_ui}}</td>
                                </tr>
                                <tr>
                                    <th>Puesto:</th>
                                    <td>@{{oUser.job_name_ui}}</td>
                                </tr>
                                <tr>
                                    <th>Plan de vacaciones:</th>
                                    <td>@{{oUser.vacation_plan_name}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <div style="float: right;">
                                <button class="btn btn-primary" v-on:click="getHistoryVac('vacationsTable');">Ver historial</button>
                                <button class="btn btn-secondary" v-on:click="hiddeHistory('vacationsTable');">Ocultar historial</button>
                            </div>
                        </div>
                    </div>
                    <br>
                    <table class="table table-bordered" id="vacationsTable" style="width: 100%;">
                        <thead class="thead-light">
                            <th class="no-sort">Periodo</th>
                            <th>Aniversario</th>
                            <th class="no-sort">Vac. ganadas</th>
                            <th class="no-sort">Vac. gozadas</th>
                            <th class="no-sort">Vac. vencidas</th>
                            <th class="no-sort">Vac. solicitadas</th>
                            <th class="no-sort">Vac. pendientes</th>
                        </thead>
                        <tbody>
                            <tr v-for="vac in oUser.vacation">
                                <td>@{{oDateUtils.formatDate(vac.date_start)}} a @{{oDateUtils.formatDate(vac.date_end)}}</td>
                                <td>@{{vac.id_anniversary}}</td>
                                <td>@{{vac.vacation_days}}</td>
                                <td>@{{vac.num_vac_taken}}</td>
                                <td>@{{vac.expired}}</td>
                                <td>@{{vac.request}}</td>
                                <td v-if="vac.remaining >= 0">@{{vac.remaining}}</td>
                                <td v-else style="color: red">@{{vac.remaining}}</td>
                            </tr>
                            <tfoot>
                                <tr class="thead-light">
                                    <td></td>
                                    <th>Total</th>
                                    <td>@{{oUser.tot_vacation_days}}</td>
                                    <td>@{{oUser.tot_vacation_taken}}</td>
                                    <td>@{{oUser.tot_vacation_expired}}</td>
                                    <td>@{{oUser.tot_vacation_request}}</td>
                                    <td v-if="oUser.tot_vacation_remaining >= 0">@{{oUser.tot_vacation_remaining}}</td>
                                    <td v-else style="color: red">@{{oUser.tot_vacation_remaining}}</td>
                                </tr>
                            </tfoot>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card shadow mb-4">
            <div class="card-header">
                <h3>
                    <b>Mis solicitudes de vacaciones</b>
                    @include('layouts.manual_button')
                </h3>
            </div>
            <div>
                <div class="card-body" v-if="oUser != null">
                    @include('layouts.table_buttons', ['crear' => true, 'editar' => true, 'delete' => true, 'send' => true])
                    <div class="col-md-7" style="float: right; text-align: right; padding-right: 0 !important;">
                        <label for="rqStatus">Filtrar por estatus: </label>
                        <select class="select2-class form-control inline" name="rqStatus" id="rqStatus" style="width: 30%;">
                            <option value="0" selected>Creados</option>
                            <option value="1">Enviados</option>
                            <option value="2">Aprobados</option>
                            <option value="3">Rechazados</option>
                            <option value="4">Cancelados</option>
                        </select>&nbsp;&nbsp;
                        <label>Filtrar por año:</label>
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
                    <table class="table table-bordered" id="table_myRequest" style="width: 100%;">
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
                            <th>Días efectivos</th>
                            <th>Tipo</th>
                            <th>Estatus</th>
                            <th>sup coment.</th>
                        </thead>
                        <tbody>
                            <template v-for="rec in oUser.applications">
                                <tr :style="{ background: (rec.request_status_id == 3 ? '#E8F5E9' : (rec.request_status_id == 4 ? '#FCE4EC' : '')) }">
                                    <td>@{{rec.id_application}}</td>
                                    <td>@{{rec.request_status_id}}</td>
                                    <td>@{{rec.take_holidays}}</td>
                                    <td>@{{rec.take_rest_days}}</td>
                                    <td>@{{rec.emp_comments_n}}</td>
                                    <td>@{{rec.user_apr_rej_id}}</td>
                                    <td>@{{rec.id_application_vs_type}}</td>
                                    <td>@{{rec.folio_n}}</td>
                                    <td>@{{oDateUtils.formatDate(rec.created_at, 'ddd DD-MMM-YYYY')}}</td>
                                    <td>@{{rec.user_apr_rej_name}}</td>
                                    <td>
                                        @{{
                                            (rec.request_status_id == oData.const.APPLICATION_CONSUMIDO ||
                                            rec.request_status_id == oData.const.APPLICATION_APROBADO
                                            ) 
                                            ?
                                                oDateUtils.formatDate(rec.approved_date_n, 'ddd DD-MMM-YYYY') :
                                                ((rec.request_status_id == oData.const.APPLICATION_RECHAZADO) ?
                                                    oDateUtils.formatDate(rec.rejected_date_n, 'ddd DD-MMM-YYYY') :
                                                    '')
                                        }}
                                    </td>
                                    <td>@{{oDateUtils.formatDate(rec.start_date, 'ddd DD-MMM-YYYY')}}</td>
                                    <td>@{{oDateUtils.formatDate(rec.end_date, 'ddd DD-MMM-YYYY')}}</td>
                                    <td>@{{oDateUtils.formatDate(rec.return_date, 'ddd DD-MMM-YYYY')}}</td>
                                    <td>@{{rec.total_days}}</td>
                                    <td>@{{specialType(rec)}}</td>
                                    <td>
                                        @{{
                                            rec.applications_st_name == 'CONSUMIDO' ? 'APROBADO' : rec.applications_st_name
                                        }}
                                    </td>
                                    <td>@{{rec.sup_comments_n}}</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    moment.locale('es');
    $(document).ready(function () {
        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                let registerVal = parseInt( $('#rqStatus').val(), 10 );
                let filter = 0;

                switch (registerVal) {
                    case 0:
                        filter = parseInt( data[oServerData.indexesMyRequestTable.request_status_id] );
                        return filter === 1;
                        
                    case 1:
                        filter = parseInt( data[oServerData.indexesMyRequestTable.request_status_id] );
                        return filter === 2;

                    case 2:
                        filter = parseInt( data[oServerData.indexesMyRequestTable.request_status_id] );
                        return filter === 3 || filter === 5;

                    case 3:
                        filter = parseInt( data[oServerData.indexesMyRequestTable.request_status_id] );
                        return filter === 4;

                    case 4:
                        filter = parseInt( data[oServerData.indexesMyRequestTable.request_status_id] );
                        return filter === 6;

                    default:
                        break;
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
                                    ] )

@include('layouts.table_jsControll', [
                                        'table_id' => 'table_myRequest',
                                        'colTargets' => [0,2,3,4,5,6,17],
                                        'colTargetsSercheable' => [1],
                                        'select' => true,
                                        'noDom' => true,
                                        'order' => [[4, 'desc']],
                                        'edit_modal' => true,
                                        'crear_modal' => true,
                                        'delete' => true,
                                        'send' => true
                                    ] )
@include('layouts.manual_jsControll')
<script>
    $(document).ready(function (){
        $('#rqStatus').change( function() {
            table['table_myRequest'].draw();
        });
    });
</script>
<script type="text/javascript" src="{{ asset('myApp/Utils/SReDrawTables.js') }}"></script>
<script type="text/javascript" src="{{ asset('myApp/emp_vacations/vacations_utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('myApp/emp_vacations/vue_my_vacations.js') }}"></script>
<script src="myApp/Utils/SDatePicker/js/datepicker-full.min.js"></script>
<script>
    var aniversaryDay = '';
    var birthday = '';
    appMyVacations.initValuesForUser(oServerData.oUser);
    var app = appMyVacations;
</script>
<script src="{{ asset('myApp/Utils/SDateRangePickerUtils.js') }}"></script>
<script>
    var elem = document.querySelector('input[name="datepicker"]');
    var datepicker = new Datepicker(elem, {
        language: 'es',
        format: 'dd/mm/yyyy',
        // minDate: null,
    });

    elem.addEventListener('changeDate', function (e, details) { 
        app.setMyReturnDate();
    });

    var oDateRangePickerForMyRequest  = new SDateRangePicker();
    var dateRangePickerArrayApplications = [];
    // var dateRangePickerArraySpecialSeasons = [];
    var dateRangePickerValid = true;
    // var selfDatePicker = oDateRangePickerForMyRequest
    // for (let index = 0; index < oServerData.lTemp.length; index++) {
    //     oDateRangePickerForMyRequest.createClass(oServerData.lTemp[index].priority, oServerData.lTemp[index].color);
    // }

    oDateRangePickerForMyRequest.setDateRangePicker(
        'two-inputs-myRequest',
        oServerData.initialCalendarDate,
        oServerData.oUser.payment_frec_id,
        oServerData.const.QUINCENA,
        'date-range200-myRequest',
        'date-range201-myRequest',
        'clear',
        oServerData.lHolidays
    );

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
        app.takedNoBussinesDay = false;
        app.originalDaysTaked = 0;
        app.lNoBussinesDay = [];
        app.noBussinesDayIndex = 0;
        app.lDays = [];
        app.newData = true;
    }
</script>
@endsection