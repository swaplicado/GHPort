@extends('layouts.principal')

@section('headJs')
    <script>
        function GlobalData(){
            this.lEmployees = <?php echo json_encode($lEmployees); ?>;
        }

        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="myEmplVac">
    <div class="card-header">
        <h3>
            <b>VACACIONES MIS COLABORADORES</b>
            <a href="http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:vacmiscolaboradores" target="_blank">
                <span class="bx bx-question-mark btn3d" style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
            </a>
        </h3>
    </div>
    <div class="card-body">
            <div class="card shadow mb-4" v-for="emp in lEmployees">
                <a :href="'#id_'+emp.employee_num" class="d-block card-header py-3" data-toggle="collapse"
                    role="button" aria-expanded="false" :aria-controls="emp.employee_num">
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
                <div class="collapse" :id="'id_'+emp.employee_num">
                    <div class="card-body">
                        <div class="col-md-6 card border-left-primary">
                            <table class="table" :id="'table_info_employee_'+emp.employee_num" style="margin-left: 10px; width: 90%">
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
                        <table class="table table-bordered" :id="'table_emp_vacation_'+emp.employee_num">
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
                                    <td v-if="vac.remaining < 0" style="color: red">@{{vac.remaining}}</td>
                                    <td v-else>@{{vac.remaining}}</td>
                                </tr>
                                <tr class="thead-light">
                                    <td></td>
                                    <th>Total</th>
                                    <td>@{{emp.tot_vacation_days}}</td>
                                    <td>@{{emp.tot_vacation_taken}}</td>
                                    <td>@{{emp.tot_vacation_expired}}</td>
                                    <td>@{{emp.tot_vacation_request}}</td>
                                    <td v-if="emp.tot_vacation_remaining < 0" style="color: red">@{{emp.tot_vacation_remaining}}</td>
                                    <td v-else>@{{emp.tot_vacation_remaining}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    </div>
</div>
@endsection

@section('scripts')
    @foreach($lEmployees as $emp)
        @include('layouts.table_jsControll', [
                                            'table_id' => 'table_info_employee_'.$emp->employee_num,
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
                                            'table_id' => 'table_emp_vacation_'.$emp->employee_num,
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

    <script type="text/javascript" src="{{ asset('myApp/emp_vacations/vue_my_emp_vacations.js') }}"></script>
@endsection