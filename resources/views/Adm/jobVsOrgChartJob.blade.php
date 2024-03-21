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
            this.lOrgChartJobs = <?php echo json_encode($lOrgChartJobs); ?>;
            this.lJobs = <?php echo json_encode($lJobs); ?>;
            this.lJobVsOrgChartJob = <?php echo json_encode($lJobVsOrgChartJob); ?>;
            this.updateRoute = <?php echo json_encode(route('jobVsOrgChartJob_update')); ?>;
            this.indexesTableAreas = {
                'id': 0,
                'org_chart_id': 1,
                'job_id': 2,
                'job': 3,
                'orgChart': 4,
                'positions': 5,
            };
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:puestovsarea" ); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="jobVsArea">

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 1140px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Puesto: @{{job}}</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-2">
                        <label for="selArea">Área asignada:</label>
                    </div>
                    <div class="col-md-10">
                        <select class="select2-class" class="form-control" id="selArea" name="selArea" style="width: 90%;"></select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <label for="selArea">Número de colab:</label>
                    </div>
                    <div class="col-md-4">
                        <input type="number" class="form-control" min="1" max="50" step="1" v-model="positions">
                    </div>
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
            <b>Puestos vs Areas</b>
            @include('layouts.manual_button')
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
                        <th>JobVsArea_id</th>
                        <th>OrgChartJob_id</th>
                        <th>Job_id</th>
                        <th>Puesto</th>
                        <th>Área</th>
                        <th>Núm. Colab.</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="oJobOrg in lJobVsOrgChartJob">
                        <td>@{{oJobOrg.id}}</td>
                        <td>@{{oJobOrg.id_org_chart_job}}</td>
                        <td>@{{oJobOrg.id_job}}</td>
                        <td>@{{oJobOrg.job}}</td>
                        <td>@{{oJobOrg.orgChart}}</td>
                        <td>@{{oJobOrg.positions}}</td>
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
    @include('layouts.manual_jsControll')
    <script type="text/javascript" src="{{ asset('myApp/Adm/vue_jobVsOrgChartJob.js') }}"></script>
@endsection