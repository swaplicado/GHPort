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
            this.lPermissions = <?php echo json_encode($lPermissions); ?>;
            this.oPermission = <?php echo json_encode($oPermission); ?>;
            this.constants = <?php echo json_encode($constants); ?>;
            this.permission_time = <?php echo json_encode($permission_time); ?>;
            this.lTypes = <?php echo json_encode($lTypes); ?>;
            this.lHolidays = <?php echo json_encode($lHolidays); ?>;
            this.lTemp = <?php echo json_encode($lTemp); ?>;
            this.oUser = <?php echo json_encode($oUser); ?>;
            this.lEmployees = <?php echo json_encode($lEmployees); ?>;
            this.routeCreate = <?php echo json_encode(route('permission_create')) ?>;
            this.routeUpdate = <?php echo json_encode(route('permission_update')) ?>;
            this.routeGetPermission = <?php echo json_encode(route('permission_getPermission')) ?>;
            this.routeDelete = <?php echo json_encode(route('permission_delete')) ?>;
            this.routeApprobe = <?php echo json_encode(route('requestPermission_approbe')) ?>;
            this.routeReject = <?php echo json_encode(route('requestPermission_reject')) ?>;
            this.routeSendAuthorize = <?php echo json_encode(route('requestPermission_sendAndAuthorize')) ?>;
            this.routeGetEmployee = <?php echo json_encode(route('requestPermission_getEmployee')) ?>;
            this.routeGetAllEmployees = <?php echo json_encode(route('requestPermission_getAllEmployees')) ?>;
            this.routeGetDirectEmployees = <?php echo json_encode(route('requestPermission_getDirectEmployees')) ?>;
            this.routeCheckMail = <?php echo json_encode(route('permission_checkMail')) ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:solicitudesvacaciones" ); ?>;
            this.manualRoute[1] = <?php echo json_encode( "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:gestionvacaciones" ); ?>;
            this.manualRoute[2] = <?php echo json_encode( "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:misvacaciones#solicitud_de_vacaciones" ); ?>;
            this.indexes_permission = {
                'id': 0,
                'request_status_id': 1,
                'emp coment.': 2,
                'sup coment.': 3,
                'revisor_id': 4,
                'type_incident_id': 5,
                'empleado': 6,
                'Permiso': 7,
                'tiempo': 8,
                'Folio': 9,
                'Fecha solicitud': 10,
                'Revisor': 11,
                'Fecha revisión': 12,
                'Fecha': 13,
                'Estatus': 14,
            }
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<a class="btn btn-outline-secondary focus" id="ReqPermissions" onclick="btnActive('ReqPermissions');" href="#home"
    data-role="link">Solicitudes incidencias</a>
<a class="btn btn-outline-secondary" id="gestionPermissions" onclick="btnActive('gestionPermissions');" href="#other"
    data-role="link">Gestión incidencias</a>
<div class="card shadow mb-4" id="permissionsApp">
        
    @include('permissions.modal_permissions')
        
    <div data-page="home" id="home" class="active">
        <div class="card-header">
            <h3>
                <b>solicitudes de permisos de horas</b>
                @include('layouts.manual_button')
            </h3>
        </div>
        <div class="card-body">
            <div class="contenedor-elem-ini">
                <label for="permission_tp_filter">Filtrar por tipo: </label>
                <select class="select2-class form-control" name="permission_tp_filter" id="permission_tp_filter" style="width: 15%;"></select>
                &nbsp;&nbsp;
                @include('layouts.status_filter', [
                                                    'filterType' => 2,
                                                    'status_id' => 'status_ReqPermission',
                                                    'status_name' => 'status_ReqPermission',
                                                    'width' => '20%'
                                                    ])
            </div>
            <br>
            @include('layouts.table_buttons', ['show' => true ])
            <br>
            <br>
            @include('permissions.permissions_table', ['table_id' => 'table_ReqPermissions', 'table_ref' => 'table_ReqPermissions'])
        </div>
    </div>
    <div data-page="other" id="other">
        <div class="card-header">
            <h3>
                <b>Solicitudes incidencias</b>
                @include('layouts.manual_button')
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
                        <button class="btn btn-primary" v-on:click="setGestionPermissions();">Ver incidencias</button>
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
                            <label for="permission_tp_filter">Filtrar por tipo: </label>
                            <select class="select2-class form-control" name="myPermission_tp_filter" id="myPermission_tp_filter" style="width: 15%;"></select>
                            &nbsp;&nbsp;
                            @include('layouts.status_filter', [
                                                                'filterType' => 1,
                                                                'status_id' => 'myStatus_myPermission',
                                                                'status_name' => 'myStatus_myPermission',
                                                                'width' => '20%'
                                                                ])
                        </div>
                        <br>
                        @include('layouts.table_buttons', ['crear' => true, 'editar' => true, 'delete' => true])
                        <button id="btn_sendAprov" type="button" v-on:click="sendAuthorize()" class="btn3d" style="display: inline-block; margin-right: 5px; background-color: #4DB6AC" title="Enviar y autorizar">
                            <span class="bx bxs-send"></span>
                        </button>
                        <br>
                        <br>
                        @include('permissions.permissions_table', ['table_id' => 'table_permissions', 'table_ref' => 'table_permissions'])
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
                let col_type = null;
                let col_status = null;

                if(settings.nTable.id == 'table_ReqPermissions'){
                    let iType = parseInt( $('#permission_tp_filter').val(), 10 );
                    let iStatus = parseInt( $('#status_ReqPermission').val(), 10 );

                    col_type = parseInt( data[oServerData.indexes_permission.type_incident_id] );
                    col_status = parseInt( data[oServerData.indexes_permission.request_status_id] );
                    if(col_type == iType || iType == 0){
                        return col_status == iStatus;
                    }else{
                        return false;
                    }
                }

                if(settings.nTable.id == 'table_permissions'){
                    let iType = parseInt( $('#myPermission_tp_filter').val(), 10 );
                    let iStatus = parseInt( $('#myStatus_myPermission').val(), 10 );

                    col_type = parseInt( data[oServerData.indexes_permission.type_incident_id] );
                    col_status = parseInt( data[oServerData.indexes_permission.request_status_id] );
                    if(col_type == iType || iType == 0){
                        return col_status == iStatus;
                    }else{
                        return false;
                    }
                }
                return false;
            }
        );
    });
</script>
@include('layouts.table_jsControll', [
                                        'table_id' => 'table_ReqPermissions',
                                        'colTargets' => [0,2,3,4],
                                        'colTargetsSercheable' => [1,5],
                                        'noDom' => true,
                                        'select' => true,
                                        'show' => true,
                                    ] )
@include('layouts.manual_jsControll')
<script>
    $(document).ready(function (){
        $('#permission_tp_filter').change( function() {
            table['table_ReqPermissions'].draw();
        });

        $('#status_ReqPermission').change( function() {
            table['table_ReqPermissions'].draw();
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
<script>
    const btn_ids = ['ReqPermissions', 'gestionPermissions'];

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

        if (id == 'ReqPermissions') {
            app.initRequestPermissions();
        } else if (id == 'gestionPermissions') {
            app.initGestionPermissions();
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
@endsection