@extends('layouts.principal')

@section('headJs')
    <script>
        function GlobalData(){
            this.lEmployees = <?php echo json_encode($lEmployees); ?>;
            this.getlEmployees_route = <?php echo json_encode(route('getlEmployees', ":OrgjobId")); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="allEmpVacationApp">
    <div class="card-header">
        <h3>
            <b>VACACIONES COLABORADORES</b>
            <a href="http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:vaccolaboradores" target="_blank">
                <span class="bx bx-question-mark btn3d" style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
            </a>
        </h3>
    </div>
    <div class="card-body">
        <div class="card shadow mb-4" v-for="(emp, index) in lEmployees">
            <a :href='"#id_"+emp.employee_num' class="d-block card-header py-3" data-toggle="collapse"
                role="button" aria-expanded="false" :aria-controls="emp.employee_num"
                v-on:click="getEmployees(index, emp.id, emp.org_chart_job_id, emp.is_head_user);"
            >
                <h6 class="m-0 font-weight-bold text-primary"> 
                    <div class="row">
                        <div class="col-md-3">
                            @{{emp.employee}}
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <span class="col-md-1" style="width: 0; border-right: 1px solid #bcbdc2; height: 1rem; margin: auto 1rem"></span>
                                <span class="col-md-3">
                                    Vacaciónes pendientes: @{{emp.tot_vacation_remaining}} días
                                </span>
                                <span v-if="emp.is_head_user" class="col-md-1" style="width: 0; border-right: 1px solid #bcbdc2; height: 1rem; margin: auto 1rem"></span>
                                <span v-if="emp.is_head_user" class="col-md-3">
                                    Encargado de area
                                    <span class="bx bxs-group"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </h6>
            </a>
            <div class="collapse" :id='"id_"+emp.employee_num'>
                <div class="card-body">
                    <div class="col-md-6 card border-left-primary">
                        <table style="margin-left: 10px;" :id="'table_info_'+emp.employee_num">
                            <thead>
                                <th></th>
                                <th></th>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Nombre:</th>
                                    <td>@{{emp.full_name}}</td>
                                </tr>
                                <tr>
                                    <th>Fecha ingreso:</th>
                                    <td>@{{oDateUtils.formatDate(emp.last_admission_date)}}</td>
                                </tr>
                                <tr>
                                    <th>Antigüedad:</th>
                                    <td>@{{emp.antiquity}} al día de hoy</td>
                                </tr>
                                <tr>
                                    <th>Departamento:</th>
                                    <td>@{{emp.department_name_ui}}</td>
                                </tr>
                                <tr>
                                    <th>Puesto:</th>
                                    <td>@{{emp.job_name_ui}}</td>
                                </tr>
                                <tr>
                                    <th>Plan de vacaciones:</th>
                                    <td>@{{emp.vacation_plan_name}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <br>
                    <table class="table table-bordered" :id="'table_'+emp.employee_num">
                        <thead class="thead-light">
                            <th>Periodo</th>
                            <th>Aniversario</th>
                            <th>Vac. ganadas</th>
                            <th>Vac. gozadas</th>
                            <th>Vac. vencidas</th>
                            <th>Vac. solicitadas</th>
                            <th>Vac. pendientes</th>
                        </thead>
                        <tbody>
                            <tr v-for="vac in emp.vacation">
                                <td>@{{oDateUtils.formatDate(vac.date_start)}} a @{{oDateUtils.formatDate(vac.date_end)}}</td>
                                <td>@{{vac.id_anniversary}}</td>
                                <td>@{{vac.vacation_days}}</td>
                                <td>@{{vac.num_vac_taken}}</td>
                                <td>@{{vac.expired}}</td>
                                <td>@{{vac.request}}</td>
                                <td style="color: red" v-if="vac.remaining < 0">@{{vac.remaining}}</td>
                                <td v-else>@{{vac.remaining}}</td>
                            </tr>
                            <tr class="thead-light">
                                <td></td>
                                <th>Total</th>
                                <td>@{{emp.tot_vacation_days}}</td>
                                <td>@{{emp.tot_vacation_taken}}</td>
                                <td>@{{emp.tot_vacation_expired}}</td>
                                <td>@{{emp.tot_vacation_request}}</td>
                                <td style="color: red" v-if="emp.tot_vacation_remaining < 0">@{{emp.tot_vacation_remaining}}</td>
                                <td v-else>@{{emp.tot_vacation_remaining}}</td>
                            </tr>
                        </tbody>
                    </table>
                    <br>
                    <span :id='"id_span_"+emp.id'></span></span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ asset('myApp/emp_vacations/vue_all_emp_vacations.js') }}"></script>
    <script type="text/javascript">
        function getEmployees(i, id, org_chart_job_id, is_head_user) {
            app.getEmployees(i, id, org_chart_job_id, is_head_user);
        }
    </script>
    @foreach($lEmployees as $emp)
        @include('layouts.table_jsControll', [
                                            'table_id' => 'table_info_'.$emp->employee_num,
                                            'colTargets' => [],
                                            'colTargetsSercheable' => [],
                                            'noSearch' => true,
                                            'noDom' => true,
                                            'noPaging' => true,
                                            'noInfo' => true,
                                            'noColReorder' => true,
                                            'noSort' => true
                                            ])

        @include('layouts.table_jsControll', [
                                            'table_id' => 'table_'.$emp->employee_num,
                                            'colTargets' => [],
                                            'colTargetsSercheable' => [],
                                            'noSearch' => true,
                                            'noDom' => true,
                                            'noPaging' => true,
                                            'noInfo' => true,
                                            'noColReorder' => true,
                                            'noSort' => true
                                            ])
    @endforeach
    <script id="jsDatatable">
        function load(id){
            $('#'+id).DataTable({
                "language": {
                        "sProcessing":     "Procesando...",
                        "sLengthMenu":     "Mostrar _MENU_ registros",
                        "sZeroRecords":    "No se encontraron resultados",
                        "sEmptyTable":     "Ningún dato disponible en esta tabla",
                        "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                        "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                        "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                        "sInfoPostFix":    "",
                        "sSearch":         "Buscar:",
                        "sUrl":            "",
                        "sInfoThousands":  ",",
                        "sLoadingRecords": "Cargando...",
                        "oPaginate": {
                            "sFirst":    "Primero",
                            "sLast":     "Último",
                            "sNext":     "Siguiente",
                            "sPrevious": "Anterior"
                        },
                        "oAria": {
                            "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                        }
                    },

                "responsive": false,
                "columnDefs": [
                    {
                        "targets": [],
                        "visible": false,
                        "searchable": false,
                    }
                ],
                "searching": false,
                "bSort": false,
                "paging": false,
                "info": false,
                "colReorder": false,
                "initComplete": function(){ 
                    $('#'+id).wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                }
            });
        }
    </script>
@endsection