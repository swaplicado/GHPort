@extends('layouts.principal')

@section('headStyles')
<link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.min.css")}}">
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker.min.css" rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker-bs4.min.css" rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker-bulma.min.css" rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker-foundation.min.css" rel="stylesheet" />
<link href={{asset('myApp/Utils/singleDateRangePicker/daterangepicker.css')}} rel="stylesheet" />


<style>
    .swal2-title {
        font-size: 24px !important;
    }
</style>

@endsection

@section('headJs')
    <script src="{{ asset("daterangepicker/jquery.daterangepicker.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
           //rutas
            this.users = <?php echo json_encode($dataUser); ?>;
            this.startDate = <?php echo json_encode($startDate); ?>;
            this.endDate = <?php echo json_encode($endDate); ?>;
            this.rute_get_work_personal = <?php echo json_encode(route("word_record_log")); ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:conscolabslow" ); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="allDataPersonalLog">
    
    <div class="card-header">
        <h3>
            <b>Constancias laborales visualizadas y/o descargadas</b>
            @include('layouts.manual_button')
        </h3>
    </div>
    <br>
    <div class="input-group">
        <br>
        <div class="col-md-3" >
            <input type="text" name="daterange" id="daterange" class="form-control" />
        </div>
        <div class="col-md-1">
            <button class="btn btn-primary" onclick="filterMinutes();">
                <span class="bx bx-search"></span>
            </button>
        </div>        
        <br><br>
        @include('data_personal.log_view_record_table', ['table_id' => 'table_log_record', 'table_ref' => 'table_log_record'])
    </div>
</div>


@endsection

@section('scripts')
@include('layouts.manual_jsControll')
<script>
    moment.locale('es');
    $(document).ready(function () {
        
    });
</script>
@include('layouts.table_jsControll', [
                                        'table_id' => 'table_log_record',
                                        'colTargets' => [],
                                        'colTargetsSercheable' => [],
                                        'noDom' => true,
                                        'select' => true,
                                        'crear_modal' => true,
                                        'edit_modal' => true,
                                        'delete' => true,
                                        'send' => true,
                                        'order' => [[0, 'asc']],
                                    ] )
<script>
    $(document).ready(function () {
        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                let colStartDate = data[4];
                let oStartDate = moment(app.startDate);
                let oEndDate = moment(app.endDate);
                let oColStartDate = moment(colStartDate);

                return oColStartDate.isBetween(oStartDate, oEndDate);
            }
        );
        filterMinutes();

    });

</script>
<script type="text/javascript" src="{{ asset('myApp/DataPersonal/vue_data_personal_log.js') }}"></script>
<script type="text/javascript" src="{{ asset('myApp/Utils/singleDateRangePicker/daterangepicker.js') }}"></script>



<script>
    $(function() {
        var oDatePicker = $('input[name="daterange"]').daterangepicker({
                                opens: 'left',
                                locale: {
                                    format: 'DD [de] MMMM [de] YYYY',
                                    applyLabel: 'Aplicar',
                                    cancelLabel: 'Cancelar',
                                    fromLabel: 'Desde',
                                    toLabel: 'Hasta',
                                    customRangeLabel: 'Rango personalizado',
                                    weekLabel: 'S',
                                    daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
                                    monthNames: ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
                                    firstDay: 1
                                },
                                startDate: moment(oServerData.startDate),
                                endDate: moment(oServerData.endDate),
                            }, function(start, end, label) {
                                app.startDate = start.format('YYYY-MM-DD');
                                app.endDate = end.format('YYYY-MM-DD');
                                console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
                            });
    });
</script>


<script>
    function filterMinutes(){
        table['table_log_record'].draw();
    }
</script>

@endsection