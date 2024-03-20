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
            this.saveDelegationRoute = <?php echo json_encode(route('delegation_saveDelegation')); ?>;
            this.updateDelegationRoute = <?php echo json_encode(route('delegation_updateDelegation')); ?>;
            this.deleteDelegationRoute = <?php echo json_encode(route('delegation_deleteDelegation')); ?>;
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
<div id="appDelegation">
    <a class="btn btn-outline-secondary focus" id="btnDelegCreadas" onclick="btnActive('btnDelegCreadas');" href="#DelegCreadas"
        data-role="link">Delegaciones creadas</a>
    <a class="btn btn-outline-secondary" id="btnDelegAsign" onclick="btnActive('btnDelegAsign');" href="#DelegAsign"
        data-role="link">Delegaciones asignadas</a>

    @include('delegations.modal_delegationForm')
    <div data-page="DelegCreadas" id="DelegCreadas" class="active">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h3>
                    <b>Mis delegaciones creadas</b>
                    @include('layouts.manual_button')
                </h3>
            </div>
            <div class="card-body">
                <button id="btn_delegation" v-on:click="showModalDelegations();" type="button" class="btn3d btn-info" style="display: inline-block; margin-right: 20px" title="Nueva delegación">
                    <span class="bx bx-user-plus"></span>
                </button>
                @include('layouts.table_buttons', ['crear' => true, 'editar' => true, 'delete' => true])
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
    </div>
    <div data-page="DelegAsign" id="DelegAsign">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h3>
                    Mis delegaciones asignadas
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="table_delegation_asigned" style="width: 100%;">
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
                        <tr v-for="oDel in lDelegations_asigned">
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

@include('layouts.table_jsControll', [
                                            'table_id' => 'table_delegation_asigned',
                                            'colTargets' => [0,1,2],
                                            'colTargetsSercheable' => [3],
                                        ] )

<script type="text/javascript" src="{{ asset('myApp/delegations/vue_delegations.js') }}"></script>
<script>
    const btn_ids = ['btnDelegCreadas', 'btnDelegAsign'];
    function btnActive(id) {
        let btn = document.getElementById(id);
        btn.style.backgroundColor = '#858796';
        btn.style.color = '#fff';

        for (const bt_id of btn_ids) {
            if (bt_id != id) {
                let bt = document.getElementById(bt_id);
                bt.style.backgroundColor = '#fff';
                bt.style.color = '#858796';
                bt.style.boxShadow = '0 0 0';
            }
        }
    }
</script>
<script>
    (function() {
        let pages = [];
        let links = [];

        document.addEventListener("DOMContentLoaded", function() {
            pages = document.querySelectorAll('[data-page]');
            links = document.querySelectorAll('[data-role="link"]');
            [].forEach.call(links, function(link) {
                link.addEventListener("click", navigate)
            });
        });

        function navigate(ev) {
            ev.preventDefault();
            let id = ev.currentTarget.href.split("#")[1];
            [].forEach.call(pages, function(page) {
                if (page.id === id) {
                    page.classList.remove('noActive');
                    page.classList.add('active');
                } else {
                    page.classList.remove('active');
                    page.classList.add('noActive');
                }
            });
            return false;
        }
    })();
</script>
@include('layouts.manual_jsControll')
@endsection