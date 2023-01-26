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
        })
    </script>
    <script>
        function GlobalData(){
            this.lAreas = <?php echo json_encode($lAreas); ?>;
            this.lUsers = <?php echo json_encode($lUsers); ?>;
            this.updateRoute = <?php echo json_encode( route('update_assignArea') ); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="assignArea">

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
                {{--<label for="selUser" hidden>Usuario encargado:</label>
                <select class="select2-class" id="selUser" name="selUser" style="width: 90%;"></select>--}}
                <label for="selArea">Área superior:</label>
                <select class="select2-class" id="selArea" name="selArea" style="width: 90%;"></select>
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
            <b>ÁREAS FUNCIONALES</b>
            <a href="http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:areasfuncionales" target="_blank">
                <span class="bx bx-question-mark btn3d" style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
            </a>
        </h3>
    </div>
    <div class="card-body">
        @include('layouts.table_buttons', ['editar' => true])
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
                        <th>Responsable área</th>
                        <th>Área superior</th>
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
                                            'colTargets' => [0,1,2],
                                            'colTargetsSercheable' => [],
                                            'select' => true,
                                            'edit_modal' => true
                                        ] )
    <script type="text/javascript" src="{{ asset('myApp/Adm/vueAssignArea.js') }}"></script>
@endsection