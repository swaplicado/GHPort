@extends('layouts.principal')

@section('headStyles')
<link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.min.css")}}">
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker.min.css" rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker-bs4.min.css" rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker-bulma.min.css" rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker-foundation.min.css" rel="stylesheet" />
@endsection

@section('headJs')
<script src="{{ asset("daterangepicker/jquery.daterangepicker.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
            this.lIncidences = <?php echo json_encode($lIncidences); ?>;
            this.lSuperviser = <?php echo json_encode($lSuperviser); ?>;
            this.constants = <?php echo json_encode($constants); ?>;
            this.lClass = <?php echo json_encode($lClass); ?>;
            this.lTypes = <?php echo json_encode($lTypes); ?>;
            this.lTemp = <?php echo json_encode($lTemp); ?>;
            this.lHolidays = <?php echo json_encode($lHolidays); ?>;
            this.oUser = <?php echo json_encode($oUser); ?>;
            this.table_name = <?php echo json_encode('table_MyIncidences'); ?>;
            this.routeCreate = <?php echo json_encode(route('incidences_create')); ?>;
            this.routeUpdate = <?php echo json_encode(route('incidences_update')); ?>;
            this.routeDelete = <?php echo json_encode(route('incidences_delete')); ?>;
            this.routeSend = <?php echo json_encode(route('incidences_send')); ?>;
            this.routeGetIncidence = <?php echo json_encode(route('incidences_getIncidence')); ?>;
            this.routeGestionSendIncidence = <?php echo json_encode(route('incidences_gestionSendIncidence')); ?>;
            this.routeGetBirdthDayIncidences = <?php echo json_encode(route('incidences_getBirdthDayIncidences')); ?>;
            this.routeCheckMail = <?php echo json_encode(route('incidences_checkMail')); ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:misincidencias" ); ?>;
            this.indexes_incidences = {
                'id_application': 0,
                'request_status_id': 1,
                'emp_comments_n': 2,
                'sup_comments_n': 3,
                'user_apr_rej_id': 4,
                'id_incidence_cl': 5,
                'id_incidence_tp': 6,
                'employee': 7,
                'incidence_tp_name': 8,
                'folio_n': 9,
                'date_send_n': 10,
                'user_apr_rej_name': 11,
                'accept_reject_date': 12,
                'start_date': 13,
                'end_date': 14,
                'return_date': 15,
                'total_days': 16,
                'subtype': 17,
                'applications_st_name': 18,
            }
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="incidencesApp">
    
    @include('Incidences.modal_incidences')

    <div class="card-header">
        <h3>
            <b>Mis incidencias</b>
            @include('layouts.manual_button')
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
                                                'filterType' => 1,
                                                'status_id' => 'status_myIncidence',
                                                'status_name' => 'status_myIncidence',
                                                'width' => '20%'
                                                ])
        </div>
        <br>
        @include('layouts.table_buttons', ['crear' => true, 'editar' => true, 'delete' => true, 'send' => true ])
        <br>
        <br>
        @include('Incidences.incidences_table', ['table_id' => 'table_Incidences', 'table_ref' => 'table_Incidences'])
    </div>
</div>
@endsection

@section('scripts')

<script>
    moment.locale('es');
    $(document).ready(function () {
        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                let iClass = parseInt( $('#incident_cl_filter').val(), 10 );
                let iType = parseInt( $('#incident_tp_filter').val(), 10 );
                let iStatus = parseInt( $('#status_myIncidence').val(), 10 );
                let col_class = null;
                let col_type = null;
                let col_status = null;

                col_class = parseInt( data[oServerData.indexes_incidences.id_incidence_cl] );
                col_type = parseInt( data[oServerData.indexes_incidences.id_incidence_tp] );
                col_status = parseInt( data[oServerData.indexes_incidences.request_status_id] );
                if(col_type == iType || iType == 0){
                    return col_status == iStatus;
                }else{
                    return false;
                }
            }
        );
    });
</script>
@include('layouts.table_jsControll', [
                                        'table_id' => 'table_Incidences',
                                        'colTargets' => [0,2,3,4,7,16,17],
                                        'colTargetsSercheable' => [1,5,6],
                                        'noDom' => true,
                                        'select' => true,
                                        'crear_modal' => true,
                                        'edit_modal' => true,
                                        'delete' => true,
                                        'send' => true
                                    ] )
@include('layouts.manual_jsControll')
<script>
    $(document).ready(function (){
        $('#incident_cl_filter').change( function() {
            app.select_changed = true;
        });
        
        $('#incident_tp_filter').change( function() {
            table['table_Incidences'].draw();
        });

        $('#status_myIncidence').change( function() {
            table['table_Incidences'].draw();
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
            enable
        );
    }

    function dateRangePickerSetValue(){
        if($('#date-range-001').val() && $('#date-range-002').val()){
            app.startDate = app.oDateUtils.formatDate($('#date-range-001').val(), 'ddd DD-MMM-YYYY');
            app.endDate = app.oDateUtils.formatDate($('#date-range-002').val(), 'ddd DD-MMM-YYYY');
            // app.checkSelectDates();
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
            // app.getDataDays();
        }
    }

    function dateRangePickerClearValue(){
        app.returnDate = null;
    }
</script>
<script type="text/javascript" src="{{ asset('myApp/emp_vacations/vacations_utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('myApp/Incidences/vue_incidences.js') }}"></script>
<script type="text/javascript" src="{{ asset('myApp/Utils/SDatePicker/js/datepicker-full.min.js') }}"></script>
<script>
    var elem = document.querySelector('input[name="datepicker"]');
    var datepicker = new Datepicker(elem, {
        language: 'es',
        format: 'dd/mm/yyyy',
        // showOnFocus: true,
        // minDate: null,
    });

    elem.addEventListener('changeDate', function (e, details) { 
        app.setMyReturnDate();
    });
</script>
@endsection