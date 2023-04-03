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
            $('.select2-class-filter').select2({});
        })
    </script>
    <script>
        function GlobalData(){
            this.lUsers = <?php echo json_encode($lUsers) ?>;
            this.lOrgChart = <?php echo json_encode($lOrgChart) ?>;
            this.updateRoute = <?php echo json_encode(route('empVSArea_update')) ?>;
            this.indexes = {
                'user_id': 0,
                'org_chart_job_id': 1,
                'top_org_chart_job_id_n': 2,
                'full_name_ui': 3,
                'job_name_ui': 4,
                'job_name_ui_top': 5,
            };
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:colabvsarea" ); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="empVsOrgChartJobApp">

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">@{{user}}</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div>
                    <label for="selArea">Área:</label>
                    <select class="select2-class" id="selArea" name="selArea" style="width: 90%;"></select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" v-on:click="update();">Guardar</a>
            </div>
        </div>
    </div>
</div>

    <div class="card-header">
        <h3>
            <b>Colaboradores vs áreas</b>
            @include('layouts.manual_button')
        </h3>
    </div>
    <div class="card-body">
        <div class="inline">
        @include('layouts.table_buttons', ['editar' => true])
            <label for="selAreaFilter">Ver por área:</label>
            <select class="select2-class-filter" id="selAreaFilter" name="selAreaFilter" style="width: 40%;"></select>
        </div>
        <br>
        <br>
        <div class="table-responsive">
            <table class="table table-bordered" id="table_emp_vs_orgChart" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <th>user_id</th>
                    <th>orgChart_id</th>
                    <th>orgChartTop_id</th>
                    <th>Colaborador</th>
                    <th>Área</th>
                    <th>Área padre</th>
                </thead>
                <tbody>
                    <tr v-for="user in lUsers">
                        <td>@{{user.id}}</td>
                        <td>@{{user.org_chart_job_id}}</td>
                        <td>@{{user.top_org_chart_job_id_n}}</td>
                        <td>@{{user.full_name_ui}}</td>
                        <td>@{{user.job_name_ui}}</td>
                        <td>@{{user.job_name_ui_top}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @include('layouts.table_jsControll', [
                                            'table_id' => 'table_emp_vs_orgChart',
                                            'colTargets' => [0,1,2],
                                            'colTargetsSercheable' => [],
                                            'select' => true,
                                            'edit_modal' => true
                                        ] )
    <script type="text/javascript" src="{{ asset('myApp/Adm/vue_empVsOrgChartJob.js') }}"></script>
    @include('layouts.manual_jsControll')
@endsection