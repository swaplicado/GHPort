@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />

<style>
    .myCheckbox {
        -ms-transform: scale(1.5); /* IE */
        -moz-transform: scale(1.5); /* FF */
        -webkit-transform: scale(1.5); /* Safari y Chrome */
        -o-transform: scale(1.5); /* Opera */
        padding: 20px; /* Ajusta el padding según tus necesidades */
    }

</style>
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
            this.lUsers = <?php echo json_encode($lUsers) ?>;
            this.updateShowUserRoute = <?php echo json_encode(route('showUsers_updateShowInSystem')) ?>;

            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:usuariosensistema" ); ?>;

            this.indexesUsersTable = {
                'id': 0,
                'show_in_system': 1,
                'is_active': 2,
                'full_name': 3,
                'area': 4,
                'activo': 5,
                'mostrar': 6
            };
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content')
<div id="usersShowInSystemApp">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h3>
                <b>Usuarios mostrados en sistema</b>
                @include('layouts.manual_button')
            </h3>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-md-5">
                    <input type="checkbox" class="myCheckbox" id="FilterIsActive" checked="true">
                    <label for="FilterIsActive" style="padding-left: 10px;"><h4>Ver solo usuarios en activo en la organización</h4></label>
                </div>
            </div>

            <table class="table table-bordered" id="usersTable" style="width: 100%">
                <thead class="thead-light">
                    <th>id</th>
                    <th>show_in_system</th>
                    <th>is_active</th>
                    <th>Colaborador</th>
                    <th>Nodo org.</th>
                    <th>Estatus</th>
                    <th>Mostrar en sistema</th>
                    <tbody>
                        <tr v-for="user in lUsers">
                            <td>@{{user.id}}</td>
                            <td>@{{user.show_in_system}}</td>
                            <td>@{{user.is_active}}</td>
                            <td>@{{user.full_name}}</td>
                            <td>@{{user.area}}</td>
                            <td>@{{user.is_active == 1 ? 'Activo' : 'Inactivo'}}</td>
                            <td style="text-align: center">
                                <div class="checkbox-wrapper-22">
                                    <label class="switch" :for="'checkbox'+user.id">
                                        <input type="checkbox" :id="'checkbox'+user.id" :checked="user.show_in_system == 1" 
                                        v-on:click="updateShowUserInSystem(user.id, user.full_name, 'checkbox'+user.id)"/>
                                        <div class="slider round"></div>
                                    </label>
                                </div>
                                <p hidden>@{{user.show_in_system == 1 ? 'sí' : 'No'}}</p>
                            </td>
                        </tr>
                    </tbody>
                </thead>
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
                let showInSystem = $('#FilterShowInSystem').is(':checked');
                let usersIsActive = $('#FilterIsActive').is(':checked');
                let val2 = parseInt(data[oServerData.indexesUsersTable.is_active]);

                if(usersIsActive){
                    return val2;
                }else{
                    return true;
                }
            }
        );

        $('#FilterShowInSystem').change( function() {
            table['usersTable'].draw();
        });

        $('#FilterIsActive').change( function() {
            table['usersTable'].draw();
        });
    });
</script>
{{-- Mi tabla --}}
@include('layouts.table_jsControll', [
                                        'table_id' => 'usersTable',
                                        'colTargets' => [0,1],
                                        'colTargetsSercheable' => [2],
                                        'noSort' => true,
                                    ] )
@include('layouts.manual_jsControll')
<script type="text/javascript" src="{{ asset('myApp/Utils/toastNotifications.js') }}"></script>
<script type="text/javascript" src="{{ asset('myApp/Adm/vue_usersShowInSystem.js') }}"></script>
@endsection