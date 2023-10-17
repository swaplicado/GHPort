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

        })
    </script>
    <script>
        function GlobalData(){
            this.lUser = <?php echo json_encode($lUser); ?>;
            this.lOrgChart = <?php echo json_encode($lOrgChart); ?>;
            this.lPlan = <?php echo json_encode($lPlan); ?>;
            this.lRol = <?php echo json_encode($lRol); ?>;
            this.updateRoute = <?php echo json_encode( route('update_user') ); ?>;
            this.deleteRoute = <?php echo json_encode( route('delete_user') ); ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:areasfuncionales" ); ?>;
            //Al modificar index no olvidar agregarlo en la funcion reDraw de vue
            this.indexesUserTable = {
                'idUser':0,
                'username':1,
                'fullname':2,
                'mail':3,
                'numUser':4,
                'benDate':5,
                'nameRol':6,
                'idRol':7,
                'nameOrg':8,
                'idOrg':9,
                'nameVp':10,
                'idPlan':11,
                'active':12,
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
                        <th># usuario</th>
                        <th>Inicio beneficios</th>
                        <th>Rol</th>
                        <th>id_rol</th>
                        <th>Organigrama</th>
                        <th>id_org</th>
                        <th>Plan de vacaciones</th>
                        <th>id_plan</th>
                        <th>is_active</th>
                        <th>Esta activo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in lUser">
                        <td>@{{user.idUser}}</td>
                        <td>@{{user.username}}</td>
                        <td>@{{user.fullname}}</td>
                        <td>@{{user.mail}}</td>
                        <td>@{{user.numUser}}</td>
                        <td>@{{user.benDate}}</td>
                        <td>@{{user.nameRol}}</td>
                        <td>@{{user.idRol}}</td>
                        <td>@{{user.nameOrg}}</td>
                        <td>@{{user.idOrg}}</td>
                        <td>@{{user.nameVp}}</td>
                        <th>@{{user.idPlan}}</th>
                        <td>@{{user.active}}</td>
                        <td v-if="user.active == 0">No</td>
                        <td v-else="user.active == 1">Sí</td>
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
                                            'colTargets' => [0,7,9,11,12],
                                            'colTargetsSercheable' => [],
                                            'select' => true,
                                            'edit_modal' => true,
                                            'order' => [1,'asc'],
                                            'noColReorder' => true,
                                        ] )
    @include('layouts.manual_jsControll')
    <script type="text/javascript" src="{{ asset('myApp/Adm/vue_user.js') }}"></script>
@endsection