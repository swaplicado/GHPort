@extends('layouts.principal')

@section('headStyles')
<link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.min.css")}}">
<link href="{{ asset('myApp/Utils/SDatePicker/css/datepicker.min.css') }}" rel="stylesheet" />
<link href="{{ asset('myApp/Utils/SDatePicker/css/datepicker-bs4.min.css') }}" rel="stylesheet" />
<link href="{{ asset('myApp/Utils/SDatePicker/css/datepicker-bulma.min.css') }}" rel="stylesheet" />
<link href="{{ asset('myApp/Utils/SDatePicker/css/datepicker-foundation.min.css') }}" rel="stylesheet" />
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.select2-class').select2({
                dropdownParent: $('#editModal')
            });

            $('.select2-class-create').select2({
                dropdownParent: $('#createModal')
            });
        })
    </script>
    <script src="{{ asset("daterangepicker/jquery.daterangepicker.min.js") }}" type="text/javascript"></script>
    <script>
        
        function GlobalData(){
            this.lDates = <?php echo json_encode($lDates); ?>;
            this.createRoute = <?php echo json_encode( route('create_closingDates') ); ?>;
            this.deleteRoute = <?php echo json_encode( route('delete_closingDates') ); ?>;
            this.initialCalendarDate = <?php echo json_encode( $initial); ?>;
            this.constants = <?php echo json_encode( $constants); ?>;
            this.lTypes = <?php echo json_encode( $lTypes ); ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:datespersonaldata" ); ?>;
            this.indexes_closeDates = {
                'id_closing_dates': 0,
                'date_ini': 1,
                'date_end': 2,
            }
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="closingDatesApp">

<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Fechas modificación datos personales</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3">
                        <label for="">Selecciona tipo:*</label>
                    </div>
                    <div class="col-md-9">
                        <select class="select2-class-modal form-control" name="closing_date_type" 
                            id="closing_date_type" style="width: 90%;"></select>
                    </div>
                </div>
                <br>
                <div style="text-align: center">
                    <div class="card">
                        <div class="card-body card-body-small">

                            <span id="two-inputs-calendar">
                                <span hidden>
                                    <input id="date-range-001" type="date" value="" class="form-control" style="width: 30%; display: inline" readonly> a <input id="date-range-002" type="date" value="" class="form-control" style="width: 30%; display: inline" readonly>
                                </span>
                                <table style="width: 100%;">
                                    <thead></thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                @include('layouts.Nomeclatura_calendario', ['id' => 'nomeclaturaMyRequest'])
                                            </td>
                                            <td>
                                                <input class="form-control" v-model="startDate" style="width: 40%; display: inline" readonly> a <input class="form-control" v-model="endDate" style="width: 40%; display: inline" readonly>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-primary inline" id="clear">Limpiar</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="myBreakLine"></div>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" v-on:click="save();">Guardar</a>
            </div>
        </div>
    </div>
</div>


    <div class="card-header">
        <h3>
            <b>Fechas para cambio de datos personales y curriculum vitae</b>
            @include('layouts.manual_button')
        </h3>
    </div>
    <div class="card-body">
        @include('layouts.table_buttons', ['crear' => true, 'editar' => true, 'delete' => true ])
        <br>
        <br>
        <div class="table-responsive">
            <table class="table table-bordered" id="table_dates" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>closing_dates_id</th>
                        <th>Fecha inicio</th>
                        <th>Fecha fin</th>
                        <th>Tipo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="date in lDates">
                        <td>@{{ date.id_closing_dates }}</td>
                        <td>@{{ oDateUtils.formatDate(date.start_date) }}</td>
                        <td>@{{ oDateUtils.formatDate(date.end_date) }}</td>
                        <td>@{{ date.name }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    moment.locale('es');
</script>
    @include('layouts.table_jsControll', [
                                            'table_id' => 'table_dates',
                                            'colTargets' => [0],
                                            'colTargetsSercheable' => [],
                                            'select' => true,
                                            'crear_modal' => true,
                                            'edit_modal' => true,
                                            'delete' => true,
                                        ] )
    @include('layouts.manual_jsControll')
    <script type="text/javascript" src="{{ asset('myApp/emp_vacations/vacations_utils.js') }}"></script>
    <script type="text/javascript" src="{{ asset('myApp/Adm/vue_closing_dates.js') }}"></script>
    <script src="{{ asset('myApp/Utils/SDateRangePickerClass.js') }}"></script>
    <script>
        var dateRangePickerArrayApplications = [];
        var dateRangePickerArrayIncidences = [];
        var lTemp = [];
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

@endsection