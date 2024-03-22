@extends('layouts.principal')

@section('headStyles')
<link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.min.css")}}">
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker.min.css" rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker-bs4.min.css" rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker-bulma.min.css" rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker-foundation.min.css" rel="stylesheet" />
<style>
    ol.ol_bold {
        list-style-type: none; /* Elimina la numeración por defecto */
        counter-reset: item; /* Reinicia el contador */
    }

    li.li_bold::before {
        content: counter(item) ". "; /* Agrega el número y un punto */
        counter-increment: item; /* Incrementa el contador */
        font-weight: bold; /* Aplica negrita al número */
    }

</style>
@endsection

@section('headJs')
<script src="{{ asset("daterangepicker/jquery.daterangepicker.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
            this.lEvents = <?php echo json_encode($lEvents) ?>;
            this.lEventsAssigns = <?php echo json_encode($lEventsAssigns); ?>;
            this.initialCalendarDate = <?php echo json_encode($initialCalendarDate); ?>;
            this.lHolidays = <?php echo json_encode($lHolidays); ?>;
            this.constants = <?php echo json_encode($constants); ?>;
            this.eventSaveRoute = <?php echo json_encode(route('events_save')); ?>;
            this.eventUpdateRoute = <?php echo json_encode(route('events_update')); ?>;
            this.eventDeleteRoute = <?php echo json_encode(route('events_delete')); ?>;
            this.getAssignedRoute = <?php echo json_encode(route('events_getAssigned')); ?>;
            this.saveAssignUserRoute = <?php echo json_encode(route('events_saveAssignUser')); ?>;
            this.saveAssignGroupRoute = <?php echo json_encode(route('events_saveAssignGroup')); ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:eventos" ); ?>;
            this.indexesEventsTable = {
                'id_event': 0,
                'event': 1,
                'startDate': 2,
                'endDate': 3,
                'priority': 4,
            };

            this.indexesEmpNoAssign = {
                'id_employee': 0,
                'employee': 1,
                'area': 2,
            }

            this.indexesEmpAssign = {
                'id_employee': 0,
                'employee': 1,
                'area': 2,
            }

            this.indexesGroupNoAssign = {
                'id_group': 0,
                'group': 1,
            }

            this.indexesGroupAssign = {
                'id_group': 0,
                'group': 1,
            }
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content')
<div id="eventsApp">
    <div class="card shadow mb-4">
        <div class="active" v-show="cardType == 'events'">
            @include('events.modal_events')
            @include('events.modal_events_assigns')
            <div class="card-header">
                <h3>
                    <b>Eventos</b>
                    @include('layouts.manual_button')
                </h3>
            </div>
            <div class="card-body">
                <div v-if="cardType == 'events'">
                    @include('layouts.table_buttons', ['crear' => true, 'editar' => true, 'delete' => true, 'asign' => true, 'asignVueMethod' => 'showModalEventAssign()'])
                </div>
                <br>
                <br>
                <table class="table table-bordered" id="events_table" style="width: 100%">
                    <thead class="thead-light">
                        <th>id_event</th>
                        <th>Evento</th>
                        <th>Fecha incio</th>
                        <th>Fecha fin</th>
                        <th>prioridad</th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var self;
    moment.locale('es');
</script>
{{-- Tabla de eventps --}}
@include('layouts.table_jsControll', [
                                        'table_id' => 'events_table',
                                        'colTargets' => [0],
                                        'colTargetsSercheable' => [],
                                        'select' => true,
                                        'crear_modal' => true,
                                        'edit_modal' => true,
                                        'delete' => true,
                                    ] )

{{-- Tabla de empleados no asignados --}}
@include('layouts.table_jsControll', [
                                        'table_id' => 'employeesNoAssignTable',
                                        'colTargets' => [0,2],
                                        'colTargetsSercheable' => [],
                                        'noDom' => true,
                                        'noPaging' => true,
                                        'noInfo' => true,
                                        'noColReorder' => true,
                                        'noSort' => true,
                                        'selectMulti' => true,
                                    ] )

{{-- Tabla de empleados asignados --}}
@include('layouts.table_jsControll', [
                                        'table_id' => 'employeesAssignTable',
                                        'colTargets' => [0,2],
                                        'colTargetsSercheable' => [],
                                        'noDom' => true,
                                        'noPaging' => true,
                                        'noInfo' => true,
                                        'noColReorder' => true,
                                        'noSort' => true,
                                        'selectMulti' => true,
                                    ] )

{{-- Tabla de grupos no asignados --}}
@include('layouts.table_jsControll', [
                                        'table_id' => 'groupsNoAssignTable',
                                        'colTargets' => [0],
                                        'colTargetsSercheable' => [],
                                        'noDom' => true,
                                        'noPaging' => true,
                                        'noInfo' => true,
                                        'noColReorder' => true,
                                        'noSort' => true,
                                        'selectMulti' => true,
                                    ] )

{{-- Tabla de grupos asignados --}}
@include('layouts.table_jsControll', [
                                        'table_id' => 'groupsAssignTable',
                                        'colTargets' => [0],
                                        'colTargetsSercheable' => [],
                                        'noDom' => true,
                                        'noPaging' => true,
                                        'noInfo' => true,
                                        'noColReorder' => true,
                                        'noSort' => true,
                                        'selectMulti' => true,
                                    ] )
@include('layouts.manual_jsControll')
<script type="text/javascript" src="{{ asset('myApp/emp_vacations/vacations_utils.js') }}"></script>
<script src="{{ asset('myApp/Utils/SDateRangePickerClass.js') }}"></script>
<script>
    var oDateRangePicker = null;
    var dateRangePickerArrayApplications = [];
    var dateRangePickerArrayIncidences = [];
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
            [],
            lHolidays,
            birthday,
            aniversaryDay,
            true
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
<script type="text/javascript" src="{{ asset('myApp/Adm/vue_events.js') }}"></script>
<script>
    $(document).ready(function(){
        app.drawEventsTable('events_table', oServerData.lEvents);
        // btnActive('bEvents');
    });
</script>
@endsection