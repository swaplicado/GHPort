@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
<style>
    #sortable { list-style-type: none; margin: 0; padding: 0; }
    #sortable li { margin: 0 3px 3px 3px; padding-left: 1.5em; font-size: 1.4em; background-color: #C4C4C4; }
    #sortable li span { position: absolute; margin-left: -1.3em; }
</style>
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.select2-class').select2({
                dropdownParent: $('#modal_special_type')
            });
        })
    </script>
    <script>
        function GlobalData(){
            this.lSpecialType = <?php echo json_encode($lSpecialType); ?>;
            this.lSituation = <?php echo json_encode($lSituation); ?>;
            this.routeSave = <?php echo json_encode(route('specialType_save')); ?>;
            this.routeUpdate = <?php echo json_encode(route('specialType_update')); ?>;
            this.routeDelete = <?php echo json_encode(route('specialType_delete')); ?>;
            this.indexSpecialType = {
                'id': 0,
                'situation_id': 1,
                'name': 2,
                'code': 3,
                'situation': 4,
                'priority': 5,
            },
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:tiposolicitudesespeciales" ); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="specialType">

    <div class="modal fade" id="modal_special_type" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Tipo de solicitud especial</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="" class="form-label">Nombre de la solicitud especial: </label>
                            <input type="text" class="form-control" name="name_special" v-model="name" :disabled="is_edit">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <label for="" class="form-label">Situación de la solicitud especial: </label>
                            <select class="select2-class" name="" id="sel_situation" style="width: 100%;"></select>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <label for="" class="form-label">Clave de la solicitud especial: </label>
                            <input type="text" class="form-control" name="code_special" v-model="code">
                        </div>
                    </div>
                    <!-- <br>
                    <div class="row">
                        <div class="col-md-12">
                            <label for="" class="inline">Agregar a orden de prioridad:</label>
                            <button type="button" class="btn btn-primary" v-on:click="addToList();"><span class="bx bx-plus"></span></button>
                        </div>
                    </div> -->
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <label for="">Orden de prioridad:</label>
                            <ul id="sortable">
                                <template v-for="special in lSpecialTypeNewOrder">
                                    <li class="ui-state-default" :data-id="special.id_special_type"
                                        :data-text="special.name"
                                        title="Arrastre y suelte para reordenar">
                                    <span class="bx bx-move-vertical" style="margin-top: 3px;"></span>    
                                        @{{special.name}}
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" v-on:click="save();">Guardar</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card-header">
        <h3>
            <b>Tipos de solicitudes especiales</b>
            @include('layouts.manual_button')
        </h3>
    </div>
    <div class="card-body">
        @include('layouts.table_buttons', ['crear' => true, 'editar' => true, 'delete' => true])
        <br>
        <br>
        <div class="table-responsive">
            <table class="table table-bordered" id="table_special" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>id_special</th>
                        <th>id_situation</th>
                        <th>Nombre</th>
                        <th>Clave</th>
                        <th>Situación</th>
                        <th>Prioridad</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="special in lSpecialType">
                        <td>@{{special.id_special_type}}</td>
                        <td>@{{special.situation}}</td>
                        <td>@{{special.name}}</td>
                        <td>@{{special.code}}</td>
                        <td>@{{special.situation_name}}</td>
                        <td>@{{special.priority}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @include('layouts.table_jsControll', [
                                            'table_id' => 'table_special',
                                            'colTargets' => [0, 1],
                                            'colTargetsSercheable' => [],
                                            'select' => true,
                                            'crear_modal' => true,
                                            'edit_modal' => true,
                                            'delete' => true,
                                            'noSort' => true,
                                        ] )
    
    <script>
        var self;
    </script>
    <script type="text/javascript" src="{{ asset('myApp/Adm/vue_special_type.js') }}"></script>
    @include('layouts.manual_jsControll')
@endsection