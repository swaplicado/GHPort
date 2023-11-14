@extends('layouts.principal')

@section('headStyles')
<link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.min.css")}}">
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
{{-- <link href="myApp/Utils/SDatePicker/css/datepicker.min.css" rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker-bs4.min.css" rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker-bulma.min.css" rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker-foundation.min.css" rel="stylesheet" /> --}}
@endsection

@section('headJs')
<script src="{{ asset("daterangepicker/jquery.daterangepicker.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
            this.initialCalendarDate = <?php echo json_encode($initialCalendarDate); ?>;
            this.lPermissions = <?php echo json_encode($lPermissions); ?>;
            this.oPermission = <?php echo json_encode($oPermission); ?>;
            this.constants = <?php echo json_encode($constants); ?>;
            this.permission_time = <?php echo json_encode($permission_time); ?>;
            this.lTypes = <?php echo json_encode($lTypes); ?>;
            this.lClass = <?php echo json_encode($lClass); ?>;
            this.clase_permiso = <?php echo json_encode($clase_permiso); ?>;
            this.lHolidays = <?php echo json_encode($lHolidays); ?>;
            this.lTemp = <?php echo json_encode($lTemp); ?>;
            this.oUser = <?php echo json_encode($oUser); ?>;
            this.lSuperviser = <?php echo json_encode($lSuperviser); ?>;
            this.routeCreate = <?php echo json_encode(route('permission_create')) ?>;
            this.routeUpdate = <?php echo json_encode(route('permission_update')) ?>;
            this.routeGetPermission = <?php echo json_encode(route('permission_getPermission')) ?>;
            this.routeDelete = <?php echo json_encode(route('permission_delete')) ?>;
            this.routeGestionSendIncidence = <?php echo json_encode(route('permission_gestionSendIncidence')) ?>;
            this.routeCheckMail = <?php echo json_encode(route('permission_checkMail')) ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:mispermisos" ); ?>;
            this.indexes_permission = {
                'id': 0,
                'request_status_id': 1,
                'emp coment.': 2,
                'sup coment.': 3,
                'revisor_id': 4,
                'type_incident_id': 5,
                'cl_permission_id': 6,
                'empleado': 7,
                'Permiso': 8,
                'Clase': 9,
                'tiempo': 10,
                'Folio': 11,
                'Fecha solicitud': 12,
                'Revisor': 13,
                'Fecha revisión': 14,
                'Fecha': 15,
                'Estatus': 16,
            }
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="permissionsApp">
    
    @include('permissions.modal_permissions')

    <div class="card-header">
        <h3>
            @if ($clase_permiso == 1)
                <b>Permiso personal por horas</b>
            @else
                <b>Tema laboral por horas</b>    
            @endif
            @include('layouts.manual_button')
        </h3>
    </div>
    <div class="card-body">
        <div class="contenedor-elem-ini">
            <label for="permission_tp_filter">Filtrar por tipo: </label>
            <select class="select2-class form-control" name="permission_tp_filter" id="permission_tp_filter" style="width: 15%;"></select>
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
        @include('permissions.permissions_table', ['table_id' => 'table_permissions', 'table_ref' => 'table_permissions'])
    </div>
</div>
@endsection

@section('scripts')

<script>
    moment.locale('es');
    $(document).ready(function () {
        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                let iType = parseInt( $('#permission_tp_filter').val(), 10 );
                let iStatus = parseInt( $('#status_myPermission').val(), 10 );
                let col_type = null;
                let col_status = null;

                col_type = parseInt( data[oServerData.indexes_permission.request_status_id] );
                col_status = parseInt( data[oServerData.indexes_permission.request_status_id] );
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
                                        'table_id' => 'table_permissions',
                                        'colTargets' => [0,2,3,4,6],
                                        'colTargetsSercheable' => [1,5],
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
        $('#permission_tp_filter').change( function() {
            table['table_permissions'].draw();
        });

        $('#status_myPermission').change( function() {
            table['table_permissions'].draw();
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
        }else{
            app.startDate = '';
            app.endDate = '';
        }
    }

    function dateRangePickerGetValue(){
        if ($('#date-range-001').val() && $('#date-range-002').val() ){
            app.startDate = app.oDateUtils.formatDate($('#date-range-001').val());
            app.endDate = app.oDateUtils.formatDate($('#date-range-002').val());
        }
    }

    function dateRangePickerClearValue(){
        
    }
</script>

<script type="text/javascript" src="{{ asset('myApp/emp_vacations/vacations_utils.js') }}"></script>
<script>
    var self;
</script>
<script type="text/javascript" src="{{ asset('myApp/permissions/vue_permissions.js') }}"></script>
<script type="text/javascript" src="{{ asset('myApp/Utils/SDatePicker/js/datepicker-full.min.js') }}"></script>

@endsection