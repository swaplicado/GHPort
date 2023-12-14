@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.select2-class').select2({
                dropdownParent: $('#editModal')
            });
            $('.select2-class-filter').select2({});
        })
    </script>
    <script>
        function GlobalData(){
            this.lUsers = <?php echo json_encode($lUsers) ?>;
            this.saveRoute = <?php echo json_encode(route('recoveredVacations_managment_save')) ?>;
            this.indexes = {
                'user_id': 0,
                'colaborador': 1,
                'dias_vencidos': 2,
                'dias_reactivados': 3,
            };
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:recovervac" ); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="recoveredVacationsApp">
    @include('emp_vacations.modal_recover_vac')
    <div class="card-header">
        <h3>
            <b>Vacaciones vencidas de todos los colaboradores</b>
            @include('layouts.manual_button')
        </h3>
    </div>
    <div class="card-body">
        <div class="inline">
        @include('layouts.table_buttons', ['editar' => true])
        </div>
        <br>
        <br>
        <div class="table-responsive">
            <table class="table table-bordered" id="table_expiredVac" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <th>user_id</th>
                    <th>Colaborador</th>
                    <th>Días vencidos</th>
                    <th>Días reactivados</th>
                </thead>
                <tbody>
                    <tr v-for="user in lUsers">
                        <td>@{{user.id}}</td>
                        <td>@{{user.full_name_ui}}</td>
                        <td>@{{user.TotVacRemaining}}</td>
                        <td>@{{user.TotVacRecovered}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @include('layouts.table_jsControll', [
                                            'table_id' => 'table_expiredVac',
                                            'colTargets' => [0],
                                            'colTargetsSercheable' => [],
                                            'select' => true,
                                            'edit_modal' => true,
                                            'noSort' => true,
                                        ] )

    @include('layouts.table_jsControll', [
                                            'table_id' => 'table_modal_expiredVac',
                                            'colTargets' => [0],
                                            'colTargetsSercheable' => [],
                                            'noDom' => true,
                                            'noInfo' => true,
                                            'noColReorder' => true,
                                            'noSort' => true,
                                            'noSearch' => true,
                                        ] )
    <script type="text/javascript" src="{{ asset('myApp/emp_vacations/vue_recovered_vacation_managment.js') }}"></script>
    @include('layouts.manual_jsControll')
@endsection