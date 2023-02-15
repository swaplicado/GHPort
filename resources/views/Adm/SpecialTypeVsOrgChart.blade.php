@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.select2-class').select2({
                dropdownParent: $('#modal_assign')
            });
        })
    </script>
    <script>
        function GlobalData(){
            this.lSpecialType = <?php echo json_encode($lSpecialType); ?>;
            this.lSpecialTypeVsOrgChart = <?php echo json_encode($lSpecialTypeVsOrgChart); ?>;
            this.lOrgChart = <?php echo json_encode($lOrgChart); ?>;
            this.lUsers = <?php echo json_encode($lUsers); ?>;
            this.lCompanies = <?php echo json_encode($lCompanies); ?>;
            this.routeSave = <?php echo json_encode(route('SpecialTypeVsOrgChart_save')); ?>;
            this.routeUpdate = <?php echo json_encode(route('SpecialTypeVsOrgChart_update')); ?>;
            this.routeDelete = <?php echo json_encode(route('SpecialTypeVsOrgChart_delete')); ?>;
            this.indexSpecialVsOrgChart = {
                'id': 0,
                'cat_special_id': 1,
                'user_id': 2,
                'org_chart_job_id': 3,
                'company_id': 4,
                'revisor_id': 5,
                'special_name': 6,
                'user_name': 7,
                'org_chart_name': 8,
                'company_name': 9,
                'revisor_name': 10,
            }
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="specialTypeVsOrgChart">

<div class="modal fade" id="modal_assign" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Solicitud especial: @{{specialName}}</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <label for="sol_type">Tipo de solicitud:</label>
                <select class="select2-class" id="sol_type" name="sol_type" style="width: 90%;"></select>
                <label for="assign_by">Asignar a:</label>
                <select class="select2-class" id="assign_by" name="assign_by" style="width: 90%;"></select>
                <label for="sel_option">Selecciona @{{option}}:</label>
                <select class="select2-class" id="sel_option" name="sel_option" style="width: 90%;"></select>
                <label for="sel_revisor">Revisado por:</label>
                <select class="select2-class" id="sel_revisor" name="sel_revisor" style="width: 90%;"></select>
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
            <b>Asignar tipos de solicitudes especiales</b>
            <a href="http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:areasfuncionales" target="_blank">
                <span class="bx bx-question-mark btn3d" style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
            </a>
        </h3>
    </div>
    <div class="card-body">
        @include('layouts.table_buttons', ['crear' => true, 'editar' => true, 'delete' => true])
        <br>
        <br>
        <div class="table-responsive">
            <table class="table table-bordered" id="table_special_vs_org_chart" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>id</th>
                        <th>special_id</th>
                        <th>user_id</th>
                        <th>org_chart_id</th>
                        <th>company_id</th>
                        <th>revisor_id</th>
                        <th>Sol. Esp.</th>
                        <th>Usuario</th>
                        <th>Area</th>
                        <th>Empresa</th>
                        <th>Revisor</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="special in lSpecialTypeVsOrgChart">
                        <td>@{{special.id}}</td>
                        <td>@{{special.cat_special_id}}</td>
                        <td>@{{special.user_id_n}}</td>
                        <td>@{{special.org_chart_job_id_n}}</td>
                        <td>@{{special.company_id_n}}</td>
                        <td>@{{special.revisor_id}}</td>
                        <td>@{{special.special_name}}</td>
                        <td>@{{special.user_name}}</td>
                        <td>@{{special.org_chart_name}}</td>
                        <td>@{{special.company_name}}</td>
                        <td>@{{special.revisor_name}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @include('layouts.table_jsControll', [
                                            'table_id' => 'table_special_vs_org_chart',
                                            'colTargets' => [0, 1, 2, 3, 4, 5],
                                            'colTargetsSercheable' => [],
                                            'select' => true,
                                            'crear_modal' => true,
                                            'edit_modal' => true,
                                            'delete' => true,
                                        ] )
    <script type="text/javascript" src="{{ asset('myApp/Adm/vue_special_type_vs_org_chart.js') }}"></script>
@endsection