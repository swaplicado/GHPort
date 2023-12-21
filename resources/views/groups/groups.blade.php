@extends('layouts.principal')

@section('headStyles')
<link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.min.css")}}">
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker.min.css" rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker-bs4.min.css" rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker-bulma.min.css" rel="stylesheet" />
<link href="myApp/Utils/SDatePicker/css/datepicker-foundation.min.css" rel="stylesheet" />
@endsection

@section('headJs')
<script src="{{ asset("daterangepicker/jquery.daterangepicker.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
            this.lGroups = <?php echo json_encode($lGroups) ?>;
            this.saveRoute = <?php echo json_encode(route('groups_save')) ?>;
            this.updateRoute = <?php echo json_encode(route('groups_update')) ?>;
            this.deleteRoute = <?php echo json_encode(route('groups_delete')) ?>;
            this.getUsersAssignRoute = <?php echo json_encode(route('groups_getUsersAssign')) ?>;
            this.setAssignRoute = <?php echo json_encode(route('groups_setAssign')) ?>;

            this.indexesGroupsTable = {
                'id_group': 0,
                'groupName': 1,
            };

            this.indexesEmpNoAssign = {
                'id_employee': 0,
                'employee': 1,
            }

            this.indexesEmpAssign = {
                'id_employee': 0,
                'employee': 1,
            }
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content')
<div id="groupsApp">
    <div class="card shadow mb-4">
        @include('groups.modal_groups')
        @include('groups.modal_groups_assign')
        <div class="card-header">
            <h3>
                <b>Grupos</b>
                @include('layouts.manual_button')
            </h3>
        </div>
        <div class="card-body">
            @include('layouts.table_buttons', ['crear' => true, 'editar' => true, 'delete' => true])
            <button id="btn_asign" type="button" class="btn3d bg-gradient-light" 
                style="display: inline-block; margin-right: 5px" title="Asignaciones" v-on:click="showModalGroupAssign();">
                <span class="bx bx-transfer-alt"></span>
            </button>
            <br>
            <br>
            <table class="table table-bordered" id="groups_table">
                <thead class="thead-light">
                    <th>id_group</th>
                    <th>Grupo</th>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var self;
    moment.locale('es');
</script>
{{-- Tabla de grupos --}}
@include('layouts.table_jsControll', [
                                        'table_id' => 'groups_table',
                                        'colTargets' => [0],
                                        'colTargetsSercheable' => [],
                                        'select' => true,
                                        'crear_modal' => true,
                                        'edit_modal' => true,
                                        'delete' => true,
                                    ] )

{{-- Tabla de empleados no asignados --}}
@include('layouts.table_jsControll', [
                                        'table_id' => 'employeesNoAssignTable',
                                        'colTargets' => [0],
                                        'colTargetsSercheable' => [],
                                        'noDom' => true,
                                        'noPaging' => true,
                                        'noInfo' => true,
                                        'noColReorder' => true,
                                        'noSort' => true,
                                        'selectMulti' => true,
                                    ] )

{{-- Tabla de empleados asignados --}}
@include('layouts.table_jsControll', [
                                        'table_id' => 'employeesAssignTable',
                                        'colTargets' => [0],
                                        'colTargetsSercheable' => [],
                                        'noDom' => true,
                                        'noPaging' => true,
                                        'noInfo' => true,
                                        'noColReorder' => true,
                                        'noSort' => true,
                                        'selectMulti' => true,
                                    ] )

<script type="text/javascript" src="{{ asset('myApp/emp_vacations/vacations_utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('myApp/Adm/vue_groups.js') }}"></script>
<script>
    $(document).ready(function(){
        app.drawGroupsTable(oServerData.lGroups);
    });
</script>
@endsection