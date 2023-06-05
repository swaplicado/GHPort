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

            $('.select2-class-create').select2({
                dropdownParent: $('#createModal')
            });
        })
    </script>
    <script>
        function GlobalData(){
            this.lTpIncidence = <?php echo json_encode($lTpIncidence); ?>;
            this.lClIncidence = <?php echo json_encode($lClIncidence); ?>;
            this.lInteractSystem = <?php echo json_encode($lInteractSystem); ?>;
            this.updateRoute = <?php echo json_encode( route('update_tpIncidence') ); ?>;
            this.createRoute = <?php echo json_encode( route('create_tpIncidence') ); ?>;
            this.deleteRoute = <?php echo json_encode( route('delete_tpIncidence') ); ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:areasfuncionales" ); ?>;
            //Al modificar index no olvidar agregarlo en la funcion reDraw de vue
            this.indexesTpIncidenceTable = {
                'idTp':0,
                'nameTp':1,
                'idCl':2,
                'nameCl':3,
                'active':4,
                'activeS':5,
                'auth':6,
                'authS':7,
                'idSys':8,
                'nameSys':9,
                'deleted':10,
            };
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="tp_incidence">
    @include('Adm.modal_create_tp_incidence')
    @include('Adm.modal_edit_tp_incidence')
    <div class="card-header">
        <h3>
            <b>Tipos de incidencias</b>
            @include('layouts.manual_button')
        </h3>
    </div>
    <div class="card-body">
        @include('layouts.table_buttons', ['crear' => true, 'editar' => true, 'delete' => true ])
        <br>
        <br>
        <div class="table-responsive">
            <table class="table table-bordered" id="table_tp_incidence" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>incidence_tp_id</th>
                        <th>Tipo incidencia</th>
                        <th>incidence_cl_id</th>
                        <th>Clase incidencia</th>
                        <th>is_active</th>
                        <th>Esta activo</th>
                        <th>need_auth</th>
                        <th>Requiere autorización</th>
                        <th>interact_system_id</th>
                        <th>Sistema interacción</th>
                        <th>is_deleted</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="incidence in lTpIncidence">
                        <td>@{{incidence.idTp}}</td>
                        <td>@{{incidence.nameTp}}</td>
                        <td>@{{incidence.idCl}}</td>
                        <td>@{{incidence.nameCl}}</td>
                        <td>@{{incidence.active}}</td>
                        <td v-if="incidence.active == 0">No</td>
                        <td v-else="incidence.active == 1">Sí</td>
                        <td>@{{incidence.auth}}</td>
                        <td v-if="incidence.auth == 0">No</td>
                        <td v-else="incidence.auth == 1">Sí</td>
                        <td>@{{incidence.idSys}}</td>
                        <td>@{{incidence.nameSys}}</td>
                        <td>@{{incidence.deleted}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @include('layouts.table_jsControll', [
                                            'table_id' => 'table_tp_incidence',
                                            'colTargets' => [0,2,4,6,8,10],
                                            'colTargetsSercheable' => [],
                                            'select' => true,
                                            'crear_modal' => true,
                                            'edit_modal' => true,
                                            'delete' => true,
                                        ] )
    @include('layouts.manual_jsControll')
    <script type="text/javascript" src="{{ asset('myApp/Adm/vue_tp_incidence.js') }}"></script>
@endsection