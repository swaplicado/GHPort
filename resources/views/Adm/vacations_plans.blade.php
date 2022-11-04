@extends('layouts.principal')

@section('headStyles')

@endsection

@section('headJs')
    <script>
        function GlobalData(){
            this.lVacationPlans = <?php echo json_encode($lVacationPlans); ?>;
            this.saveRoute = <?php echo json_encode(route('vacationPlans_save')); ?>;
            this.showVacationRoute = <?php echo json_encode(route('vacationPlans_show')); ?>;
            this.deleteVacationRoute = <?php echo json_encode(route('vacationPlans_delete')); ?>;
            this.updateRoute = <?php echo json_encode(route('vacationPlans_update')); ?>;
            this.indexes = {
                'id': 0,
                'payment_frec_id_n': 1,
                'is_unionized_n': 2,
                'vacation_plan_name': 3,
                'payment_frec_name':4,
                'unionized': 5,
                'start_date_n': 6
            };
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="vacationsPlans">
    @include('Adm.modal_form_vacation_plan')
    <div class="card-header">
        <h3>
            <b>PLAN DE VACACIONES</b>
            <a href="#" target="_blank">
                <span class="bx bx-question-mark btn3d" style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
            </a>
        </h3>
    </div>
    <div class="card-body">
        @include('layouts.table_buttons', ['crear' => true, 'editar' => true, 'show' => true, 'delete' => true])
        <br>
        <br>
        <table class="table table-bordered" id="table_vacationsPlans" style="width: 100%;">
            <thead class="thead-light">
                <th>id</th>
                <th>payment_frec_id_n</th>
                <th>is_unionized_n</th>
                <th>Plan</th>
                <th>Tipo de pago</th>
                <th>Sindicalizado</th>
                <th>Fecha de inicio</th>
            </thead>
            <tbody>
                <tr v-for="vac in lVacationPlans">
                    <td>@{{vac.id_vacation_plan}}</td>
                    <td>@{{vac.payment_frec_id_n}}</td>
                    <td>@{{vac.is_unionized_n}}</td>
                    <td>@{{vac.vacation_plan_name}}</td>
                    <td v-if="vac.payment_frec_id_n == null || vac.payment_frec_id_n == ''">AMBOS</td>
                    <td v-else>@{{vac.payment_frec_id_n == 1 ? 'SEMANA' : 'QUINCENA'}}</td>
                    <td>@{{vac.is_unionized_n == 1 ? 'SÍ' : 'NO'}}</td>
                    <td>@{{vac.start_date_n}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
@include('layouts.table_jsControll', [
                                        'table_id' => 'table_vacationsPlans',
                                        'colTargets' => [0,1,2],
                                        'colTargetsSercheable' => [],
                                        'select' => true,
                                        'noSort' => true,
                                        'show' => true,
                                        'crear_modal' => true,
                                        'delete' => true,
                                        'edit_modal' => true,
                                    ] )
                                    
<script type="text/javascript" src="{{ asset('myApp/Adm/vue_vacations_plans.js') }}"></script>
@endsection