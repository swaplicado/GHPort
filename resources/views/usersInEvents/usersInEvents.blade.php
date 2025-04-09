@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
<link href={{asset('myApp/Utils/singleDateRangePicker/daterangepicker.css')}} rel="stylesheet" />
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
            this.lEvents = <?php echo json_encode($lEvents) ?>;
            this.startDate = <?php echo json_encode($startDate) ?>;

            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:usersineventos" ); ?>;

            this.indexesEventsTable = {
                'event_id': 0,
                'user_id': 1,
                'start_date': 2,
                'end_date': 3,
                'event': 4,
                'colaborador': 5,
                'area': 6,
            };
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content')
<div id="usersInEventsApp">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h3>
                <b>Eventos de mis colaboradores</b>
                @include('layouts.manual_button')
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-5">
                    <div class="row">
                        <div class="col-md-2">
                            <label for="daterange">Ver a partir de: </label>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="daterange" id="daterange" class="form-control"/>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <table class="table table-bordered" id="eventsTable" style="width: 100%">
                <thead class="thead-light">
                    <th>event_id</th>
                    <th>user_id</th>
                    <th>start_date</th>
                    <th>end_date</th>
                    <th>Evento</th>
                    <th>Colaborador</th>
                    <th>Nodo org.</th>
                    <tbody>
                        <tr v-for="ev in lEvents">
                            <td>@{{ev.id_event}}</td>
                            <td>@{{ev.id_user}}</td>
                            <td>@{{ev.start_date}}</td>
                            <td>@{{ev.end_date}}</td>
                            <td>@{{ev.event}} de @{{ev.start_date_format}} a @{{ev.end_date_format}}</td>
                            <td>@{{ev.employee}}</td>
                            <td>@{{ev.area}}</td>
                        </tr>
                    </tbody>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript" src="{{ asset('myApp/Utils/singleDateRangePicker/daterangepicker.js') }}"></script>
<script>
    var self;
</script>
<script>
    var oDatePicker;
    var fromDate = moment(oServerData.startDate).format('YYYY-MM-DD');
    var filterDate = false;
    var mounths = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    
    var oShowAll = true;
    moment.locale('es');
    console.log(moment(oServerData.startDate).format('MM/DD/YYYY'));
    $(function() {
        oDatePicker = $('input[name="daterange"]').daterangepicker({
                                singleDatePicker: true,
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
                                    monthNames: mounths,
                                    firstDay: 1
                                },
                                startDate: moment(oServerData.startDate).format('DD')
                                            + ' de '
                                            + mounths[parseInt(moment(oServerData.startDate).format('MM'), 10) - 1]
                                            + ' de '
                                            + moment(oServerData.startDate).format('YYYY'),
                            }, function(start, end, label) {
                                fromDate = start.format('YYYY-MM-DD');
                                filterAniv();
                            });

        oDatePicker.on('apply.daterangepicker', function(ev, picker) {
            filterAniv();
        })

        $('#daterange').on('change', function() {
            filterAniv();
        });
    });

    function filterAniv(){
        oShowAll = false;
        filterDate = true;
        table['eventsTable'].draw();
    }

    function showAll(){
        oShowAll = true;
        filterDate = false;
        oDatePicker.val('');
        table['eventsTable'].draw();
    }
</script>
<script>
    $(document).ready(function () {
        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                let colDate = moment(data[oServerData.indexesEventsTable['end_date']]);
                let oFromDate = moment(fromDate);

                return colDate.isSameOrAfter(oFromDate);
            }
        );
    });
</script>
@include('layouts.manual_jsControll')
@include('layouts.table_jsControll', [
                                        'table_id' => 'eventsTable',
                                        'colTargets' => [0,1],
                                        'colTargetsSercheable' => [2,3,4],
                                        'rowGroup' => [4],
                                        'colToExport' => [4,5,6],
                                        'order' => [[2, 'asc']],
                                    ] )

<script type="text/javascript" src="{{ asset('myApp/usersInEvents/vue_usersInEvents.js') }}"></script>
@endsection