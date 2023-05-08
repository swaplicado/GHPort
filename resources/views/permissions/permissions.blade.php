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
            this.lPermissions = <?php echo json_encode($lPermissions); ?>;
            this.constants = <?php echo json_encode($constants); ?>;
            this.lTypes = <?php echo json_encode($lTypes); ?>;
            this.lHolidays = <?php echo json_encode($lHolidays); ?>;
            this.oUser = <?php echo json_encode($oUser); ?>;
            this.table_name = <?php echo json_encode('table_MyPermissions'); ?>;
            this.routeCreate = <?php echo json_encode(route('permissions_create')); ?>;
            this.routeUpdate = <?php echo json_encode(route('permissions_update')); ?>;
            this.routeDelete = <?php echo json_encode(route('permissions_delete')); ?>;
            this.routeSend = <?php echo json_encode(route('permissions_send')); ?>;
            this.routeGetIncidence = <?php echo json_encode(route('permissions_getPermissions')); ?>;
            this.routeGestionSendIncidence = <?php echo json_encode(route('permissions_gestionSendPermissions')); ?>;
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
<div class="card shadow mb-4" id="incidencesApp">
    
    @include('permissions.modal_permissions')

    <div class="card-header">
        <h3>
            <b>Mis permisos de horas</b>
        </h3>
    </div>
    <div class="card-body">
        <div class="contenedor-elem-ini">
            <label for="permissions_tp_filter">Filtrar por tipo: </label>
            <select class="select2-class form-control" name="permissions_tp_filter" id="permissions_tp_filter" style="width: 15%;"></select>
            &nbsp;&nbsp;
            @include('layouts.status_filter', [
                                                'filterType' => 1,
                                                'status_id' => 'status_myPermission',
                                                'status_name' => 'status_myPermission',
                                                'width' => '20%'
                                                ])
        </div>
        <br>
        @include('layouts.table_buttons', ['crear' => true, 'editar' => true, 'delete' => true, 'send' => true ])
        <br>
        <br>
        @include('Incidences.incidences_table', ['table_id' => 'table_´Permissions', 'table_ref' => 'table_Permissions'])
    </div>
</div>
@endsection

@section('scripts')

<script>
    moment.locale('es');
    $(document).ready(function () {
        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                let iType = parseInt( $('#permissions_tp_filter').val(), 10 );
                let iStatus = parseInt( $('#status_myPermission').val(), 10 );
                let col_class = null;
                let col_type = null;
                let col_status = null;

                col_type = parseInt( data[oServerData.indexes_permissions.id_permission_tp] );
                col_status = parseInt( data[oServerData.indexes_permissions.request_status_id] );
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
                                        'table_id' => 'table_Permissions',
                                        'colTargets' => [0,2,3,4,16],
                                        'colTargetsSercheable' => [1,5,6],
                                        'noDom' => true,
                                        'select' => true,
                                        'crear_modal' => true,
                                        'edit_modal' => true,
                                        'delete' => true,
                                        'send' => true
                                    ] )
<script>
    $(document).ready(function (){
        
        $('#permission_tp_filter').change( function() {
            table['table_Permissions'].draw();
        });

        $('#status_myPermission').change( function() {
            table['table_Permissions'].draw();
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
<script type="text/javascript" src="{{ asset('myApp/Incidences/vue_permissions.js') }}"></script>
<script type="text/javascript" src="{{ asset('myApp/Utils/SDatePicker/js/datepicker-full.min.js') }}"></script>

@endsection