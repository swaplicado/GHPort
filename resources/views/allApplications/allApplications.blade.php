@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
<link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.min.css")}}">
<link href="{{ asset('myApp/Utils/SDatePicker/css/datepicker.min.css') }}" rel="stylesheet" />
<link href="{{ asset('myApp/Utils/SDatePicker/css/datepicker-bs4.min.css') }}" rel="stylesheet" />
<link href="{{ asset('myApp/Utils/SDatePicker/css/datepicker-bulma.min.css') }}" rel="stylesheet" />
<link href="{{ asset('myApp/Utils/SDatePicker/css/datepicker-foundation.min.css') }}" rel="stylesheet" />
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
            this.lApplications = <?php echo json_encode($lApplications) ?>;
            this.lClases = <?php echo json_encode($lClases) ?>;
            this.lStatus = <?php echo json_encode($lStatus) ?>;
            this.lConstants = <?php echo json_encode($lConstants) ?>;
            this.lEmployees = <?php echo json_encode($lEmployees) ?>;
            this.getApplicationRoute = <?php echo json_encode(route('allApplications_getApplication')) ?>;
            this.vacationsRoute = <?php echo json_encode(route('requestVacations')) ?>;
            this.incidencesRoute = <?php echo json_encode(route('requestIncidences_index')) ?>;
            this.permisoLaboralRoute = <?php echo json_encode(route('requestPermission_index')) ?>;
            this.permisoPersonalRoute = <?php echo json_encode(route('requestPersonalPermission')) ?>;

            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:incidenciasglobales" ); ?>;
            this.indexesApplications = {
                'request_id': 0,
                'request_class_id': 1,
                'request_status_id': 2,
                'employee_id': 3,
                'start_date': 4,
                'end_date': 5,
                'folio_n': 6,
                'employee': 7,
                'request_class': 8,
                'request_type':  9,
                'start_date_format': 10,
                'end_date_format': 11,
                'time': 12,
                'status': 13,
                'date_send_n': 14,
                'revisor': 15
            };
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content')
<div id="applicationsApp">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h3>
                <b>Incidencias globales</b>
                @include('layouts.manual_button')
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="">Clase Solicitud:</label>
                        </div>
                        <div class="col-md-9">
                            <div class="form-check">
                                <select class="select2-class form-control" name="filtro_clase" id="filtro_clase" style="width: 90%;"></select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-md-2">
                            <label for="">Estatus:</label>
                        </div>
                        <div class="col-md-9">
                            <div class="form-check">
                                <select class="select2-class form-control" name="filtro_status" id="filtro_status" style="width: 90%;"></select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="">Colaborador:</label>
                        </div>
                        <div class="col-md-9">
                            <div class="form-check">
                                <select class="select2-class form-control" name="filtro_employee" id="filtro_employee" style="width: 90%;"></select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">
                    <label for="">Ir a la incidencia:</label>
                    <button id="" type="button" class="btn3d btn-info" style="display: inline-block; margin-right: 5px" title="Ir a la solicitud" v-on:click="goToRequest();">
                        <span class="bx bx-redo"></span>
                    </button>
                </div>
            </div>
            <br>
            <table class="table table-bordered" id="applications_table" style="width: 100%">
                <thead class="thead-light">
                    <th>request_id</th>
                    <th>request_class_id</th>
                    <th>request_status_id</th>
                    <th>employee_id</th>
                    <th>start_date</th>
                    <th>end_date</th>
                    <th>Folio</th>
                    <th>Colaborador</th>
                    <th>Solicitud</th>
                    <th>Tipo</th>
                    <th>Fecha inicio</th>
                    <th>Fecha fin</th>
                    <th>Tiempo</th>
                    <th>Estatus</th>
                    <th>Fecha envío</th>
                    <th>Revisor</th>
                </thead>
                <tbody>
                    <tr v-for="application in lApplications">
                        <td>@{{application.request_id}}</td>
                        <td>@{{application.request_class_id}}</td>
                        <td>@{{application.request_status_id}}</td>
                        <td>@{{application.employee_id}}</td>
                        <td>@{{application.start_date}}</td>
                        <td>@{{application.end_date}}</td>
                        <td>@{{application.folio_n}}</td>
                        <td>@{{application.employee}}</td>
                        <td>@{{application.request_class}}</td>
                        <td>@{{application.request_type}}</td>
                        <td style="white-space: nowrap;">@{{application.start_date_format}}</td>
                        <td style="white-space: nowrap;">@{{application.end_date_format}}</td>
                        <td>@{{application.time}}</td>
                        <td>@{{application.status}}</td>
                        <td style="white-space: nowrap;">@{{application.date_send_n}}</td>
                        <td>@{{application.revisor}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var self;
    $(document).ready(function () {
        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                let clase = parseInt( $('#filtro_clase').val(), 10 );
                let status = parseInt( $('#filtro_status').val(), 10 );
                let employee = parseInt( $('#filtro_employee').val(), 10 );

                if(settings.nTable.id == 'applications_table'){
                    return (
                        (clase != 0 ? clase == data[oServerData.indexesApplications.request_class_id] : true) &&
                        (employee != 0 ? employee == data[oServerData.indexesApplications.employee_id] : true) &&
                        (status == data[oServerData.indexesApplications.request_status_id])
                    )
                }
            }
        );

        $('#filtro_clase').change( function() {
            table['applications_table'].draw();
        });

        $('#filtro_status').change( function() {
            table['applications_table'].draw();
        });

        $('#filtro_employee').change( function() {
            table['applications_table'].draw();
        });
    });
</script>

@include('layouts.table_jsControll', [
                                        'table_id' => 'applications_table',
                                        'colTargets' => [0,4,5],
                                        'colTargetsSercheable' => [1,2,3,7],
                                        'colTargetsNoOrder' => [],
                                        'noDom' => true,
                                        'select' => true,
                                        'show' => true,
                                        'cancel' => true,
                                        'noSort' => true,
                                        'ordering' => true,
                                        'order' => [[7, 'asc']],
                                        'rowGroup' => [7],
                                        'rowGroupNotSelectable' => true,
                                        // 'exportOptions' => true,
                                    ] )

@include('layouts.manual_jsControll')
<script src="{{ asset('myApp/Utils/SDateRangePickerClass.js') }}"></script>
<script type="text/javascript" src="{{ asset('myApp/allApplication/vue_allApplication.js') }}"></script>
@endsection