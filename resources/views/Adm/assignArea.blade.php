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
            this.lAreas = <?php echo json_encode($lAreas); ?>;
            this.lUsers = <?php echo json_encode($lUsers); ?>;
            this.lLevels = <?php echo json_encode($lLevels); ?>;
            this.updateRoute = <?php echo json_encode( route('update_assignArea') ); ?>;
            this.createRoute = <?php echo json_encode( route('create_assignArea') ); ?>;
            this.deleteRoute = <?php echo json_encode( route('delete_assignArea') ); ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:areasfuncionales" ); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="assignArea">

<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Crear áreas funcionales</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <label for="selArea">Nombre área:</label>
                <input type="text" id="nomAreaC" name="nomAreaC" style="width: 90%;" v-model="area">
                <label for="selArea">Puestos área:</label>
                <input type="number" id="numAreaC" min="1" name="numAreaC" style="width: 90%;" v-model="job_num">
                <label for="selArea">Área superior:</label>
                <select class="select2-class-create" id="selAreaC" name="selAreaC" style="width: 90%;"></select>
                <label for="selLevel">Nivel jerarquico:</label>
                <select class="select2-class-create-nj" id="selOrgC" name="selOrgC" style="width: 90%"></select>
                <br><br>

                <input type="checkbox" id="leaderC" name="leaderC" value="leader" v-model="leader">
                <label for="selArea">Es líder de área</label>
                
                <br>
                
                <input type="checkbox" id="config_leaderC" name="config_leaderC" value="config_leader" v-model="config_leader">
                <label for="selArea">Realizará configuraciones</label>
                
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
                <h5 class="modal-title" id="exampleModalLabel">Área: @{{area}}</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <label for="selArea">Nombre área:</label>
                <input type="text" id="nomArea" name="nomArea" style="width: 90%;" v-model="area">
                <label for="selArea">Puestos área:</label>
                <input type="number" id="numArea" min="1" name="numArea" style="width: 90%;" v-model="job_num">
                <label for="selArea">Área superior:</label>
                <select class="select2-class" id="selArea" name="selArea" style="width: 90%;"></select>
                <label for="selLevel">Nivel jerarquico:</label>
                <select class="select2-class-edit-nj" id="selOrgE" name="selOrgE" style="width: 90%"></select>
                <br><br>

                <input type="checkbox" id="leaderC" name="leaderC" value="leader" v-model="leader">
                <label for="selArea">Es líder de área</label>
                
                <br>
                
                <input type="checkbox" id="config_leaderC" name="config_leaderC" value="config_leader" v-model="config_leader">
                <label for="selArea">Realizará configuraciones</label>
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
            <b>Áreas funcionales</b>
            @include('layouts.manual_button')
        </h3>
    </div>
    <div class="card-body">
        @include('layouts.table_buttons', ['crear' => true, 'editar' => true, 'delete' => true ])
        <br>
        <br>
        <div class="table-responsive">
            <table class="table table-bordered" id="table_areas" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>Area_id</th>
                        <th>father_area_id</th>
                        <th>user_id</th>
                        <th>Área</th>
                        <th>Responsable area</th>
                        <th>Área superior</th>
                        <th>Nivel jerárquico</th>
                        <th>Posiciones</th>
                        <th>is_leader_area</th>
                        <th>is_leader_config</th>
                        <th>Nodos hijos</th>
                        <th>es líder de area</th>
                        <th>Hace configuraciones</th>

                    </tr>
                </thead>
                <tbody>
                    <tr v-for="area in lAreas">
                        <td>@{{area.id_org_chart_job}}</td>
                        <td>@{{area.top_org_chart_job_id_n}}</td>
                        <td>@{{area.head_user_id}}</td>
                        <td>@{{area.job_name}}</td>
                        <td>@{{area.head_user}}</td>
                        <td>@{{area.top_org_chart_job}}</td>
                        <td>@{{area.org_level}}</td>
                        <td>@{{area.positions}}</td>
                        <td>@{{area.is_boss}}</td>
                        <td>@{{area.is_leader_config}}</td>
                        <td>@{{area.childs}}</td>
                        <td v-if="area.is_boss == 0">No</td>
                        <td v-else="area.is_boss == 1">Sí</td>
                        <td v-if="area.is_leader_config == 0">No</td>
                        <td v-else="area.is_leader_config == 1">Sí</td>
                        <td>@{{area.org_level_id}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @include('layouts.table_jsControll', [
                                            'table_id' => 'table_areas',
                                            'colTargets' => [0,1,2,4,8,9,13],
                                            'colTargetsSercheable' => [],
                                            'select' => true,
                                            'crear_modal' => true,
                                            'edit_modal' => true,
                                            'delete' => true,
                                        ] )
    @include('layouts.manual_jsControll')
    <script type="text/javascript" src="{{ asset('myApp/Adm/vueAssignArea.js') }}"></script>
@endsection