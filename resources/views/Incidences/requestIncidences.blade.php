@extends('layouts.principal')

@section('headStyles')
<link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.min.css")}}">
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
<link href="{{ asset('myApp/Utils/SDatePicker/css/datepicker.min.css') }}" rel="stylesheet" />
<link href="{{ asset('myApp/Utils/SDatePicker/css/datepicker-bs4.min.css') }}" rel="stylesheet" />
<link href="{{ asset('myApp/Utils/SDatePicker/css/datepicker-bulma.min.css') }}" rel="stylesheet" />
<link href="{{ asset('myApp/Utils/SDatePicker/css/datepicker-foundation.min.css') }}" rel="stylesheet" />
@endsection

@section('headJs')
<script src="{{ asset("daterangepicker/jquery.daterangepicker.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
            this.oApplication = <?php echo json_encode($oApplication); ?>;
            this.lEmployees = <?php echo json_encode($lEmployees); ?>;
            this.lIncidences = <?php echo json_encode($lIncidences); ?>;
            this.constants = <?php echo json_encode($constants); ?>;
            this.lClass = <?php echo json_encode($lClass); ?>;
            this.lTypes = <?php echo json_encode($lTypes); ?>;
            this.lTemp = <?php echo json_encode($lTemp); ?>;
            this.lHolidays = <?php echo json_encode($lHolidays); ?>;
            this.oUser = <?php echo json_encode($oUser); ?>;
            this.table_name = <?php echo json_encode('table_ReqIncidences'); ?>;
            this.routeCreate = <?php echo json_encode(route('incidences_create')); ?>;
            this.routeUpdate = <?php echo json_encode(route('incidences_update')); ?>;
            this.routeDelete = <?php echo json_encode(route('incidences_delete')); ?>;
            this.routeSend = <?php echo json_encode(route('incidences_send')); ?>;
            this.routeGetIncidence = <?php echo json_encode(route('incidences_getIncidence')); ?>;
            this.routeGetEmployee = <?php echo json_encode(route('requestIncidences_getEmployee')); ?>;
            this.routeApprobe = <?php echo json_encode(route('requestIncidences_approbe')); ?>;
            this.routeReject = <?php echo json_encode(route('requestIncidences_reject')); ?>;
            this.routeGetAllEmployees = <?php echo json_encode(route('requestIncidences_getAllEmployees')); ?>;
            this.routeGestionSendIncidence = <?php echo json_encode(route('incidences_gestionSendIncidence')); ?>;
            this.routeSendAuthorize = <?php echo json_encode(route('requestIncidences_sendAndAuthorize')); ?>;
            this.routeGetBirdthDayIncidences = <?php echo json_encode(route('incidences_getBirdthDayIncidences')); ?>;
            this.routeCheckMail = <?php echo json_encode(route('incidences_checkMail')); ?>;
            this.indexes_incidences = {
                'id_application': 0,
                'request_status_id': 1,
                'emp_comments_n': 2,
                'sup_comments_n': 3,
                'user_apr_rej_id': 4,
                'id_incidence_cl': 5,
                'id_incidence_tp': 6,
                'incidence_tp_name': 7,
                'folio_n': 8,
                'date_send_n': 9,
                'user_apr_rej_name': 10,
                'accept_reject_date': 11,
                'start_date': 12,
                'end_date': 13,
                'return_date': 14,
                'total_days': 15,
                'subtype': 16,
                'applications_st_name': 17,
            }
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<a class="btn btn-outline-secondary focus" id="ReqIncidences" onclick="btnActive('ReqIncidences');" href="#home"
        data-role="link">Solicitudes incidencias</a>
<a class="btn btn-outline-secondary" id="gestionIncidences" onclick="btnActive('gestionIncidences');" href="#other"
    data-role="link">Gestión incidencias</a>
    <div class="card shadow mb-4" id="incidencesApp">
        
    @include('Incidences.modal_incidences')
        
    <div data-page="home" id="home" class="active">
        <div class="card-header">
            <h3>
                <b>Solicitudes incidencias</b>
            </h3>
        </div>
        <div class="card-body">
            <div class="contenedor-elem-ini">
                <label for="incident_cl_filter">Filtrar por clase: </label>
                <select class="select2-class form-control" name="incident_cl_filter" id="incident_cl_filter" style="width: 15%;"></select>
                &nbsp;&nbsp;
                <label for="incident_tp_filter">Filtrar por tipo: </label>
                <select class="select2-class form-control" name="incident_tp_filter" id="incident_tp_filter" style="width: 15%;"></select>
                &nbsp;&nbsp;
                @include('layouts.status_filter', [
                                                    'filterType' => 2,
                                                    'status_id' => 'status_incidence',
                                                    'status_name' => 'status_incidence',
                                                    'width' => '20%'
                                                    ])
            </div>
            <br>
            @include('layouts.table_buttons', ['show' => true])
            <br>
            <br>
            @include('Incidences.incidences_table', ['table_id' => 'table_ReqIncidences', 'table_ref' => 'table_ReqIncidences'])
        </div>
    </div>
    <div data-page="other" id="other">
        <div class="card-header">
            <h3>
                <b>Solicitudes incidencias</b>
            </h3>
        </div>
        <div class="card-body">
            <div class="contenedor-elem-ini">
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
                <div class="wrap">
                    <div class="elem">
                        <label for="" style="padding-top: 5px">Selecciona colaborador:</label>
                    </div>
                </div>
                <div class="wrap" style="min-width: 25rem">
                    <div class="elem">
                        <select class="select2-class" id="selectEmp" style="width: 100%"></select>
                    </div>
                </div>
                <div class="wrap">
                    <div class="elem">
                        <button class="btn btn-primary" v-on:click="setGestionIncidences();">Ver incidencias</button>
                    </div>
                </div>
            </div>
            <br>
            <div v-if="oUser != null">
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h3>Incidencias @{{oUser.full_name_ui}}</h3>
                    </div>
                    <div class="card-body">
                        <div class="contenedor-elem-ini">
                            <label for="myIncident_cl_filter">Filtrar por clase: </label>
                            <select class="select2-class form-control" name="myIncident_cl_filter" id="myIncident_cl_filter" style="width: 15%;"></select>
                            &nbsp;&nbsp;
                            <label for="myIncident_tp_filter">Filtrar por tipo: </label>
                            <select class="select2-class form-control" name="myIncident_tp_filter" id="myIncident_tp_filter" style="width: 15%;"></select>
                            &nbsp;&nbsp;
                            <label for="rqStatus">Filtrar por estatus: </label>
                            <select class="form-control inline" v-on:change="filterIncidenceTable();" name="status_myIncidence" id="status_myIncidence" style="width: 20%;">
                                <option value="1" selected>Creados</option>
                                <option value="2">Enviados</option>
                                <option value="3">Aprobados</option>
                                <option value="4">Rechazados</option>
                            </select>&nbsp;&nbsp;
                        </div>
                        <br>
                        @include('layouts.table_buttons', ['crear' => true, 'editar' => true, 'delete' => true ])
                        <button id="btn_sendAprov" type="button" v-on:click="sendAuthorize()" class="btn3d" style="display: inline-block; margin-right: 5px; background-color: #4DB6AC" title="Enviar y autorizar">
                            <span class="bx bxs-send"></span>
                        </button>
                        <br>
                        <br>
                        @include('Incidences.incidences_table', ['table_id' => 'table_Incidences', 'table_ref' => 'table_Incidences'])
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
    $(document).ready(function () {
        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                let col_class = null;
                let col_type = null;
                let col_status = null;

                col_class = parseInt( data[oServerData.indexes_incidences.id_incidence_cl] );
                col_type = parseInt( data[oServerData.indexes_incidences.id_incidence_tp] );
                col_status = parseInt( data[oServerData.indexes_incidences.request_status_id] );

                if(settings.nTable.id == 'table_ReqIncidences'){
                    let iClass = parseInt( $('#incident_cl_filter').val(), 10 );
                    let iType = parseInt( $('#incident_tp_filter').val(), 10 );
                    let iStatus = parseInt( $('#status_incidence').val(), 10 );
                    if(col_type == iType || iType == 0){
                        return col_status == iStatus;
                    }
                }

                if(settings.nTable.id == 'table_Incidences'){
                    let iClass = parseInt( $('#myIncident_cl_filter').val(), 10 );
                    let iType = parseInt( $('#myIncident_tp_filter').val(), 10 );
                    let iStatus = parseInt( $('#status_myIncidence').val(), 10 );
                    if(col_type == iType || iType == 0){
                        return col_status == iStatus;
                    }
                }
                return false;
            }
        );
    });
</script>
@include('layouts.table_jsControll', [
                                        'table_id' => 'table_ReqIncidences',
                                        'colTargets' => [0,2,3,4,16],
                                        'colTargetsSercheable' => [1,5,6],
                                        'noDom' => true,
                                        'select' => true,
                                        'show' => true,
                                    ] )
<script>
    $(document).ready(function (){
        $('#incident_cl_filter').change( function() {
            app.select_changed = true;
        });
        
        $('#incident_tp_filter').change( function() {
            table[oServerData.table_name].draw();
        });

        $('#status_incidence').change( function() {
            table[oServerData.table_name].draw();
        });
    });
</script>
<script src="{{ asset('myApp/Utils/SDateRangePickerClass.js') }}"></script>
<script>
    var oDateRangePicker = null;
    function initCalendar(
        sStart_date,
        bSingleMonth,
        bSingleDate,
        payment,
        lTemp,
        lHolidays,
        birthday,
        aniversaryDay,
        enable,
    ){
        if(oDateRangePicker != null){
            let oCalendar = $('#two-inputs-calendar').data('dateRangePicker');
            oCalendar.destroy();
        }
        oDateRangePicker = new SDateRangePicker();
        oDateRangePicker.setDateRangePicker(
            'two-inputs-calendar',
            'date-range-001',
            'date-range-002',
            'clear',
            oServerData.constants,
            sStart_date,
            bSingleMonth,
            bSingleDate,
            payment,
            lTemp,
            lHolidays,
            birthday,
            aniversaryDay,
            enable,
        );
    }

    function dateRangePickerSetValue(){
        if($('#date-range-001').val() && $('#date-range-002').val()){
            app.startDate = app.oDateUtils.formatDate($('#date-range-001').val(), 'ddd DD-MMM-YYYY');
            app.endDate = app.oDateUtils.formatDate($('#date-range-002').val(), 'ddd DD-MMM-YYYY');
        }else{
            app.startDate = '';
            app.endDate = '';
        }
        app.getDataDays();
    }

    function dateRangePickerGetValue(){
        if ($('#date-range-001').val() && $('#date-range-002').val() ){
            app.startDate = app.oDateUtils.formatDate($('#date-range-001').val());
            app.endDate = app.oDateUtils.formatDate($('#date-range-002').val());
        }
    }

    function dateRangePickerClearValue(){
        app.returnDate = null;
    }
</script>
<script type="text/javascript" src="{{ asset('myApp/emp_vacations/vacations_utils.js') }}"></script>
<script>
    var self;
</script>
<script type="text/javascript" src="{{ asset('myApp/Incidences/vue_incidences.js') }}"></script>
<script>
    const btn_ids = ['ReqIncidences', 'gestionIncidences'];

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

        if (id == 'ReqIncidences') {
            app.initRequestincidences();
        } else if (id == 'gestionIncidences') {
            app.initGestionIncidences();
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
    app.isRevision = true;
</script>
<script type="text/javascript" src="{{ asset('myApp/Utils/SDatePicker/js/datepicker-full.min.js') }}"></script>
<script>
    var elem = document.querySelector('input[name="datepicker"]');
    var datepicker = new Datepicker(elem, {
        language: 'es',
        format: 'dd/mm/yyyy',
    });

    elem.addEventListener('changeDate', function (e, details) { 
        app.setMyReturnDate();
    });
</script>
@endsection