@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            
            $('.select2-class-create').select2({
                dropdownParent: $('#createModal')
            });
            
            $('.select2-class-update').select2({
                dropdownParent: $('#editModal')
            });
        })
    </script>
    <script>
        function GlobalData(){
            this.lconfigAuth = <?php echo json_encode($lconfigAuth); ?>;
            this.lInci = <?php echo json_encode($lInci); ?>;
            this.lAreas = <?php echo json_encode($lAreas); ?>;
            this.lUsers = <?php echo json_encode($lUsers); ?>;
            this.lComp = <?php echo json_encode($lComp); ?>;
            this.updateRoute = <?php echo json_encode( route('update_authConf') ); ?>;
            this.createRoute = <?php echo json_encode( route('create_authConf') ); ?>;
            this.deleteRoute = <?php echo json_encode( route('delete_authConf') ); ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:confauth" ); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="configAuth">

<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 1140px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Insertar autorización de incidencias</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-2">
                        <label for="selIns">Tipo incidencia:*</label>
                    </div>
                    <div class="col-md-10">
                        <select class="select2-class-create" id="insTp" name="insTp" style="width: 90%;"></select>
                    </div>
                </div>
                <br>
                <div class="row">
                    <p>Seleccione al menos una de las opciones(Empresa, nodo org. o usuario)</p>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <label for="selIns">Empresa:</label>
                    </div>
                    <div class="col-md-10">
                        <select class="select2-class-create" id="comp" name="comp" style="width: 90%;"></select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <label for="selIns">Nodo org.:</label>
                    </div>
                    <div class="col-md-10">
                        <select class="select2-class-create" id="area" name="area" style="width: 90%;"></select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <label for="selIns">Usuario:</label>
                    </div>
                    <div class="col-md-10">
                        <select class="select2-class-create" id="usr" name="usr" style="width: 90%;"></select>
                    </div>
                </div>

                <br><br>

                <input type="checkbox" id="auth" name="auth" value="needauth" v-model="needauth">
                <label for="selIns">Necesita autorización</label> 
                
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" v-on:click="save();">Guardar</a>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Editar incidencia:</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
            <label for="selIns">Tipo incidencia:</label>
                <select class="select2-class-update" id="insTpU" name="insTpU" style="width: 90%;"></select>
                <label for="selIns">Empresa:</label>
                <select class="select2-class-update" id="compU" name="compU" style="width: 90%;"></select>
                <br>
                <label for="selIns">Nodo org.:</label>
                <br>
                <select class="select2-class-update" id="areaU" name="areaU" style="width: 90%;"></select>
                <label for="selIns">Usuario:</label>
                <select class="select2-class-update" id="usrU" name="usrU" style="width: 90%;"></select>
                <br><br>

                <input type="checkbox" id="authU" name="authU" value="needauth" v-model="needauth">
                <label for="selIns">Necesita autorización</label>
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
            <b> Configuración de autorización de incidencias</b>
            @include('layouts.manual_button')
        </h3>
    </div>
    <div class="card-body">
        @include('layouts.table_buttons', ['crear' => true, 'editar' => true, 'delete' => true ])
        <br>
        <br>
        <div class="table-responsive">
            <table class="table table-bordered" id="table_auth" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <th>id</th>
                    <th>tp_incidence</th>
                    <th>company_id</th>
                    <th>org_chart</th>
                    <th>user_id</th>
                    <th>need_auth</th>
                    <th>Tipo incidencia</th>
                    <th>Autorización</th>
                    <th>Usuario</th>
                    <th>Nodo org.</th>
                    <th>Empresa</th>
                </thead>
                <tbody>
                    <tr v-for="config in lconfigAuth">
                        <td>@{{config.id_config_auth}}</td>
                        <td>@{{config.tp_incidence_id}}</td>
                        <td>@{{config.company_id}}</td>
                        <td>@{{config.org_chart_id}}</td>
                        <td>@{{config.user_id}}</td>
                        <td>@{{config.need_auth}}</td>
                        <td>@{{config.incidence}}</td>
                        <td v-if="config.need_auth == 0">No</td>
                        <td v-else="config.need_auth == 1">Sí</td>
                        <td>@{{config.user}}</td>
                        <td>@{{config.job}}</td>
                        <td>@{{config.company}}</td>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@include('layouts.table_jsControll', [
                                        'table_id' => 'table_auth',
                                        'colTargets' => [0,1,2,3,4,5],
                                        'colTargetsSercheable' => [],
                                        'edit_modal' => true,
                                        'crear_modal' => true,
                                        'delete' => true,
                                        'noDom' => true,
                                        'select' => true,
                                    ] )
        @include('layouts.manual_jsControll')
    <script type="text/javascript" src="{{ asset('myApp/Adm/vue_configAuth.js') }}"></script>
@endsection
