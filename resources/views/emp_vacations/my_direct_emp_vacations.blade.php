@extends('layouts.principal')

@section('headJs')
    <script>
        function GlobalData(){
            this.lEmployees = <?php echo json_encode($lEmployees); ?>;
            this.getVacationHistoryRoute = <?php echo json_encode(route('myEmplVacations_getVacationHistory')); ?>;
            this.hiddeHistoryRoute = <?php echo json_encode(route('myEmplVacations_hiddeHistory')); ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:vacmiscolaboradores" ); ?>;
        }

        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="myEmplVac">
    <div class="card-header">
        <h3>
            <b>Vacaciones mis colaboradores directos</b>
            @include('layouts.manual_button')
        </h3>
    </div>
    <div class="card-body">
            <div class="card shadow mb-4" v-for="emp in lEmployees">
                <a :href="'#id_'+emp.employee_num" class="d-block card-header py-3" data-toggle="collapse"
                    role="button" aria-expanded="false" :aria-controls="emp.employee_num">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <table style="width: 100%">
                            <tbody>
                                <tr>
                                    <td style="width: 20%">@{{emp.employee}}</td>
                                    <td style="width: 10%; border-left: solid 1px rgb(172, 172, 172); text-align: center;">
                                        <img v-if="emp.company_id == 1" src="img/aeth.png" width="80vmax" height="25vmax" alt="">
                                        <img v-else-if="emp.company_id == 3" src="img/tron.png" width="40vmax" height="35vmax" alt="">
                                        <img v-else-if="emp.company_id == 4" src="img/swap_logo_22.png" width="50vmax" height="20vmax" alt="">
                                        <img v-else-if="emp.company_id == 5" src="img/ame.png" width="70vmax" height="30vmax" alt="">
                                    </td>
                                    <td style="width: 20%; border-left: solid 1px rgb(172, 172, 172); border-right: solid 1px rgb(172, 172, 172); text-align: center;">Vacaciónes pendientes: @{{emp.tot_vacation_remaining}} días</td>
                                    <td v-if="emp.is_head_user" style="width: 20%; border-left: solid 1px rgb(172, 172, 172); text-align: center;">
                                        Encargado de area
                                        <span class="bx bxs-group"></span>
                                    </td>
                                    <td v-else style="width: 20%;"></td>
                                    <td>
                                        <template v-if="emp.photo64 != null">
                                            <img class="rounded-circle" :src="'data:image/jpg;base64,'+emp.photo64" style="width:5vmax;height:5vmax;">
                                        </template>
                                        <template v-else>
                                            <img class="rounded-circle" src="{{ asset('img/avatar/profile2.png') }}" style="width:5vmax;height:5vmax;">
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
                                        <td>@{{oDateUtils.formatDate(emp.benefits_date)}}</td>
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
                        <div class="row">
                            <div class="col-md-12">
                                <div style="float: right;">
                                    <button class="btn btn-primary" v-on:click="getHistoryVac('table_emp_vacation_'+emp.employee_num, emp.id);">Ver historial</button>
                                    <button class="btn btn-secondary" v-on:click="hiddeHistory('table_emp_vacation_'+emp.employee_num, emp.id);">Ocultar historial</button>
                                </div>
                            </div>
                        </div>
                        <br>
                        <table class="table table-bordered" :id="'table_emp_vacation_'+emp.employee_num">
                            <thead class="thead-light">
                                <th class="no-sort">Periodo</th>
                                <th>Aniversario</th>
                                <th class="no-sort">Vac. ganadas</th>
                                <th class="no-sort">Vac. gozadas</th>
                                <th class="no-sort">Vac. vencidas</th>
                                <th class="no-sort">Vac. solicitadas</th>
                                <th class="no-sort">Vac. pendientes</th>
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
                                <tfoot>
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
                                </tfoot>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    moment.locale('es');
</script>
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
                                            'noSort' => true,
                                            'ordering' => true,
                                            'order' => [[1, $config->orderVac]],
                                            ])
    @endforeach
    @include('layouts.manual_jsControll')
    <script type="text/javascript" src="{{ asset('myApp/Utils/SReDrawTables.js') }}"></script>
    <script type="text/javascript" src="{{ asset('myApp/emp_vacations/vue_my_emp_vacations.js') }}"></script>
@endsection