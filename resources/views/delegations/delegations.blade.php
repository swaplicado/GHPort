@extends('layouts.principal')

@section('headStyles')
<link href={{ asset('select2js/css/select2.min.css') }} rel="stylesheet" />
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2-class').select2({});
        })
    </script>
    <script>
        function GlobalData(){
            this.lUsers = <?php echo json_encode($lUsers); ?>;
            this.lDelegations = <?php echo json_encode($lDelegations); ?>;
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
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="appDelegation">
    @include('delegations.modal_delegationForm')
    <div class="card-header">
        <h3>
            Delegaciones
        </h3>
    </div>
    <div class="card-body">
        @include('layouts.table_buttons', ['crear' => true, 'editar' => true, 'delete' => true])
        <br>
        <br>
        <table class="table table-bordered" id="delegationsTable">
            <thead>
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
                <tr v-for="oDel in lDelegations">
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
                                            'table_id' => 'delegationsTable',
                                            'colTargets' => [0,1,2],
                                            'colTargetsSercheable' => [3],
                                            'select' => true,
                                            'edit_modal' => true,
                                            'crear_modal' => true,
                                            'delete' => true,
                                        ] )

<script type="text/javascript" src="{{ asset('myApp/delegations/vue_delegations.js') }}"></script>
@endsection