@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
<style>
    /* Estilo por defecto */
    label {
        margin-bottom: 5px; /* Establece el margen por defecto */
    }

    /* Media query para dispositivos móviles */
    @media screen and (max-width: 768px) {
        label {
            margin-bottom: 0; /* Establece el margen a 0 para dispositivos móviles */
        }
    }

</style>
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.select2-class').select2({
                dropdownParent: $('#editModal')
            });

        })
    </script>
    <script>
        function GlobalData(){
            this.lUser = <?php echo json_encode($lUser); ?>;
            this.lOrgChart = <?php echo json_encode($lOrgChart); ?>;
            this.lPlan = <?php echo json_encode($lPlan); ?>;
            this.lRol = <?php echo json_encode($lRol); ?>;
            this.lSchedules = <?php echo json_encode($schedules); ?>;
            this.updateRoute = <?php echo json_encode( route('update_user') ); ?>;
            this.deleteRoute = <?php echo json_encode( route('delete_user') ); ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:catalogousuarios" ); ?>;
            //Al modificar index no olvidar agregarlo en la funcion reDraw de vue
            this.indexesUserTable = {
                'idUser':0,
                'username':1,
                'fullname':2,
                'mail':3,
                'scheduleId':4,
                'schedule':5,
                'benDate':6,
                'nameOrg':7,
                'idOrg':8,
                'nameVp':9,
                'idPlan':10,
                'active':11,
                'isActive':12,
                'idRol':13,
                'can_change_dp':14,
                'can_change_cv':15
            };
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="user">
    @include('Adm.modal_edit_user')
    <div class="card-header">
        <h3>
            <b>Usuarios</b>
            @include('layouts.manual_button')
        </h3>
    </div>
    <div class="card-body">
        @include('layouts.table_buttons', ['editar' => true ])
        <br>
        <br>
        <div class="table-responsive">
            <table class="table table-bordered" id="table_user" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>user_id</th>
                        <th>Nombre usuario</th>
                        <th>Nombre completo</th>
                        <th>Correo</th>
                        <th>scheduleId</th>
                        <th>Horario</th>
                        <th>Inicio beneficios</th>
                        <th>Organigrama</th>
                        <th>id_org</th>
                        <th>Plan de vacaciones</th>
                        <th>id_plan</th>
                        <th>is_active</th>
                        <th>Esta activo</th>
                        <th>id_rol</th>
                        <th>can_change_dp</th>
                        <th>can_change_cv</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in lUser">
                        <td>@{{user.idUser}}</td>
                        <td>@{{user.username}}</td>
                        <td>@{{user.fullname}}</td>
                        <td>@{{user.mail}}</td>
                        <td>@{{user.schedule_id}}</td>
                        <td>@{{user.schedule_name != null ? user.schedule_name : 'NA'}}</td>
                        <td>@{{user.benDate}}</td>
                        <td>@{{user.nameOrg}}</td>
                        <td>@{{user.idOrg}}</td>
                        <td>@{{user.nameVp}}</td>
                        <th>@{{user.idPlan}}</th>
                        <td>@{{user.active}}</td>
                        <td v-if="user.active == 0">No</td>
                        <td v-else="user.active == 1">Sí</td>
                        <td>@{{user.idRol}}</td>
                        <td>@{{user.can_change_dp}}</td>
                        <td>@{{user.can_change_cv}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @include('layouts.table_jsControll', [
                                            'table_id' => 'table_user',
                                            'colTargets' => [0,4,8,10,11,13,14,15],
                                            'colTargetsSercheable' => [],
                                            'select' => true,
                                            'edit_modal' => true,
                                            'order' => [1,'asc'],
                                            'noColReorder' => true,
                                        ] )
    @include('layouts.manual_jsControll')
    <script type="text/javascript" src="{{ asset('myApp/Adm/vue_user.js') }}"></script>
@endsection