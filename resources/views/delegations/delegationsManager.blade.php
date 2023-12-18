@extends('layouts.principal')

@section('headStyles')
<link href={{ asset('select2js/css/select2.min.css') }} rel="stylesheet" />
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2-class').select2({
                dropdownParent: $('#modal_delegations')
            });

            $('.select2-class-myDelegation').select2({
                dropdownParent: $('#modal_my_delegation')
            });
        })
    </script>
    <script>
        function GlobalData(){
            this.lUsers = <?php echo json_encode($lUsers); ?>;
            this.lMyManagers = <?php echo json_encode($lMyManagers); ?>;
            this.lDelegations_created = <?php echo json_encode($lDelegations_created); ?>;
            this.lDelegations_asigned = <?php echo json_encode($lDelegations_asigned); ?>;
            this.saveDelegationRoute = <?php echo json_encode(route('delegationManager_saveDelegation')); ?>;
            this.updateDelegationRoute = <?php echo json_encode(route('delegationManager_updateDelegation')); ?>;
            this.deleteDelegationRoute = <?php echo json_encode(route('delegationManager_deleteDelegation')); ?>;
            this.indexesDelegation = {
                'id_delegation': 0,
                'user_delegation_id': 1,
                'user_delegated_id': 2,
                'is_active': 3,
                'user_delegated_name': 4,
                'user_delegation_name': 5,
                'start_date': 6,
                'end_date': 7,
            }
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:delegaciones" ); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="appDelegation">
    @include('delegations.modal_delegationForm')
    <div class="card-header">
        <h3>
            <b>Mis delegaciones creadas</b>
            @include('layouts.manual_button')
        </h3>
    </div>
    <div class="card-body">
        <button id="btn_delegation" v-on:click="showModalDelegations();" type="button" class="btn3d btn-info" style="display: inline-block; margin-right: 5px" title="Nueva delegación">
            <span class="bx bx-user-plus"></span>
        </button>
        @include('layouts.table_buttons', ['editar' => true, 'delete' => true])
        <br>
        <br>
        <table class="table table-bordered" id="table_delegation_created">
            <thead class="thead-light">
                <tr>
                    <th>id_delegation</th>
                    <th>user_delegation_id</th>
                    <th>user_delegated_id</th>
                    <th>is_active</th>
                    <th>Usuario ausente</th>
                    <th>Usuario encargado</th>
                    <th>Fecha inicio</th>
                    <th>Fecha fin</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="oDel in lDelegations_created">
                    <td>@{{oDel.id_delegation}}</td>
                    <td>@{{oDel.user_delegated_id}}</td>
                    <td>@{{oDel.user_delegation_id}}</td>
                    <td>@{{oDel.is_active}}</td>
                    <td>@{{oDel.user_delegated_name}}</td>
                    <td>@{{oDel.user_delegation_name}}</td>
                    <td>@{{oDel.start_date}}</td>
                    <td>@{{oDel.end_date}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
@include('layouts.table_jsControll', [
                                            'table_id' => 'table_delegation_created',
                                            'colTargets' => [0,1,2],
                                            'colTargetsSercheable' => [3],
                                            'select' => true,
                                            'edit_modal' => true,
                                            'crear_modal' => true,
                                            'delete' => true,
                                        ] )

<script type="text/javascript" src="{{ asset('myApp/delegations/vue_delegations.js') }}"></script>
@include('layouts.manual_jsControll')
@endsection