@extends('layouts.principal')
@section('headStyles')
<link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.min.css")}}">
<style>
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
    <script>
        function GlobalData(){
            this.oUser = <?php echo json_encode($user); ?>;
            this.initialCalendarDate = <?php echo json_encode($initialCalendarDate); ?>;
            this.lHolidays = <?php echo json_encode($lHolidays); ?>;
            this.year = <?php echo json_encode($year); ?>;
            this.requestVacRoute = <?php echo json_encode(route('specialVacations_setRequestVac')); ?>;
            this.updateRequestVacRoute = <?php echo json_encode(route('specialVacations_updateRequestVac')); ?>;
            this.myVacations_filterYearRoute = <?php echo json_encode(route('myVacations_filterYear')); ?>;
            this.deleteRequestRoute = <?php echo json_encode(route('specialVacations_deleteRequestVac')); ?>;
            this.sendRequestRoute = <?php echo json_encode(route('specialVacations_sendRequestVac')); ?>;
            this.applicationsEARoute = <?php echo json_encode(route('specialVacations_getEmpApplicationsEA')); ?>;
            this.getMyVacationHistoryRoute = <?php echo json_encode(route('myVacations_getMyVacationHistory')); ?>;
            this.hiddeHistoryRoute = <?php echo json_encode(route('myVacations_hiddeHistory')); ?>;
            this.const = <?php echo json_encode($constants); ?>;

            //Al modificar index no olvidar agregarlo en la funcion reDraw de vue
            this.indexesSpecialRequestTable = {
                'id':0,
                'request_status_id':1,
                'take_holidays':2,
                'take_rest_days':3,
                'comments':4,
                'user_apr_rej_id':5,
                'request_date':6,
                'folio':7,
                'user_apr_rej_name':8,
                'accept_reject_date':9,
                'start_date':10,
                'end_date':11,
                'return_date':12,
                'taked_days':13,
                'status':14,
                'sup_comments':15,
            };
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="specialVacations" style="background-color: #B3E5FC;">
    @include('special_vacations.modal_specialRequest')
    <div class="card-body">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h3>
                    <b>VACACIONES: DIRECCIÓN GENERAL</b>
                    <a href="http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:misvacaciones#mis_vacaciones" target="_blank">
                        <span class="bx bx-question-mark btn3d" style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
                    </a>
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
                                    <td>@{{oDateUtils.formatDate(oUser.last_admission_date)}}</td>
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
                            <th class="no-sort">Período</th>
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
                    <b>SOLICITUDES VACACIONES: DIRECCIÓN GENERAL</b>
                    <a href="http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:misvacaciones#solicitud_de_vacaciones" target="_blank">
                        <span class="bx bx-question-mark btn3d" style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
                    </a>
                </h3>
            </div>
            <div>
                <div class="card-body" v-if="oUser != null">
                    @include('layouts.table_buttons', ['crear' => true, 'editar' => true, 'delete' => true, 'send' => true])
                    <div class="col-md-7" style="float: right; text-align: right; padding-right: 0 !important;">
                        <label for="rqStatus">Filtrar por estatus: </label>
                        <select class="form-control inline" name="rqStatus" id="rqStatus" style="width: 30%;">
                            <option value="0" selected>Creados</option>
                            <option value="1">Aprobados</option>
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
                            <th>Fecha solicitud</th>
                            <th>Folio</th>
                            <th>Usuario apr/rec</th>
                            <th style="max-width: 20%;">Fecha apr/rec</th>
                            <th>Fecha incio</th>
                            <th>Fecha fin</th>
                            <th>Fecha regreso</th>
                            <th>Dias efic.</th>
                            <th>Estatus</th>
                            <th>sup coment.</th>
                        </thead>
                        <tbody>
                            <tr v-for="rec in oUser.applications">
                                <td>@{{rec.id_application}}</td>
                                <td>@{{rec.request_status_id}}</td>
                                <td>@{{rec.take_holidays}}</td>
                                <td>@{{rec.take_rest_days}}</td>
                                <td>@{{rec.emp_comments_n}}</td>
                                <td>@{{rec.user_apr_rej_id}}</td>
                                <td>@{{oDateUtils.formatDate(rec.created_at, 'ddd DD-MMM-YYYY')}}</td>
                                <td>@{{rec.folio_n}}</td>
                                <td>@{{rec.user_apr_rej_name}}</td>
                                <td>
                                    @{{
                                        (rec.request_status_id == oData.const.APPLICATION_APROBADO) ?
                                            oDateUtils.formatDate(rec.approved_date_n, 'ddd DD-MMM-YYYY') :
                                            ((rec.request_status_id == oData.const.APPLICATION_RECHAZADO) ?
                                                oDateUtils.formatDate(rec.rejected_date_n, 'ddd DD-MMM-YYYY') :
                                                '')
                                    }}
                                </td>
                                <td>@{{oDateUtils.formatDate(rec.start_date, 'ddd DD-MMM-YYYY')}}</td>
                                <td>@{{oDateUtils.formatDate(rec.end_date, 'ddd DD-MMM-YYYY')}}</td>
                                <td>@{{oDateUtils.formatDate(rec.returnDate, 'ddd DD-MMM-YYYY')}}</td>
                                <td>@{{rec.total_days}}</td>
                                <td>@{{rec.applications_st_name}}</td>
                                <td>@{{rec.sup_comments_n}}</td>
                            </tr>
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
                        filter = parseInt( data[oServerData.indexesSpecialRequestTable.request_status_id] );
                        return filter === 1;
                        
                    case 1:
                        filter = parseInt( data[oServerData.indexesSpecialRequestTable.request_status_id] );
                        return filter === 3;

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
                                        'colTargets' => [0,2,3,4,5],
                                        'colTargetsSercheable' => [1],
                                        'select' => true,
                                        'noDom' => true,
                                        'order' => [[4, 'desc']],
                                        'edit_modal' => true,
                                        'crear_modal' => true,
                                        'delete' => true,
                                        'send' => true
                                    ] )
<script>
    $(document).ready(function (){
        $('#rqStatus').change( function() {
            table['table_myRequest'].draw();
        });
    });
</script>
<script type="text/javascript" src="{{ asset('myApp/Utils/SReDrawTables.js') }}"></script>
<script type="text/javascript" src="{{ asset('myApp/emp_vacations/vacations_utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('myApp/special_vacations/vue_specialVacation.js') }}"></script>
<script src="{{ asset('myApp/Utils/SDateRangePickerUtils.js') }}"></script>
<script>
    var aniversaryDay = '';
    var birthday = '';
    var oDateRangePickerForMyRequest  = new SDateRangePicker();
    var dateRangePickerArrayApplications = [];
    var dateRangePickerArraySpecialSeasons = [];
    var dateRangePickerValid = true;
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
    }
</script>
@endsection