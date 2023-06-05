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
            this.lPivot = <?php echo json_encode($lPivot); ?>;
            this.lInteractSystem = <?php echo json_encode($lInteractSystem); ?>;
            this.updateRoute = <?php echo json_encode( route('update_pivotIncidence') ); ?>;
            this.createRoute = <?php echo json_encode( route('create_pivotIncidence') ); ?>;
            this.deleteRoute = <?php echo json_encode( route('delete_pivotIncidence') ); ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:areasfuncionales" ); ?>;
            //Al modificar index no olvidar agregarlo en la funcion reDraw de vue
            this.indexesPivotTable= {
                'idPiv':0,
                'idTp':1,
                'nameTp':2,
                'tpExt':3,
                'clExt':4,
                'idSys':5,
                'nameSys':6,
            };
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="pivot_incidence">
    @include('Adm.modal_create_pivot')
    @include('Adm.modal_edit_pivot')
    <div class="card-header">
        <h3>
            <b>Configuración tipos de incidencia vs. sistemas externos</b>
            @include('layouts.manual_button')
        </h3>
    </div>
    <div class="card-body">
        @include('layouts.table_buttons', ['crear' => true, 'editar' => true, 'delete' => true ])
        <br>
        <br>
        <div class="table-responsive">
            <table class="table table-bordered" id="table_pivot" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>pivot_id</th>
                        <th>tp_incidence_id</th>
                        <th>Tipo incidencia</th>
                        <th>Tipo incidencia externo</th>
                        <th>Clase incidencia externo</th>
                        <th>interact_system_id</th>
                        <th>Sistema interacción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="pivot in lPivot">
                        <td>@{{pivot.idPiv}}</td>
                        <td>@{{pivot.idTp}}</td>
                        <td>@{{pivot.nameTp}}</td>
                        <td>@{{pivot.tpExt}}</td>
                        <td>@{{pivot.clExt}}</td>
                        <td>@{{pivot.idSys}}</td>
                        <td>@{{pivot.nameSys}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @include('layouts.table_jsControll', [
                                            'table_id' => 'table_pivot',
                                            'colTargets' => [0,1,5],
                                            'colTargetsSercheable' => [],
                                            'select' => true,
                                            'crear_modal' => true,
                                            'edit_modal' => true,
                                            'delete' => true,
                                        ] )
    @include('layouts.manual_jsControll')
    <script type="text/javascript" src="{{ asset('myApp/Adm/vue_pivot.js') }}"></script>
@endsection