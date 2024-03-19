@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
            this.lOrgChartJob = <?php echo json_encode($lOrgChartJobs) ?>;
            this.updateOrgChartJobRoute = <?php echo json_encode(route('updateOfficeOrgChartJob')) ?>;

            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:showareas" ); ?>;

            this.indexesUsersTable = {
                'id': 0,
                'is_office': 1,
                'area': 2,
                'mostrar': 3
            };
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content')
<div id="officeOrgChartJobApp">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h3>
                <b>Áreas mostradas en directorio</b>
                @include('layouts.manual_button')
            </h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered" id="areasTable" style="width: 100%">
                <thead class="thead-light">
                    <th>id</th>
                    <th>is_office</th>
                    <th>Área</th>
                    <th>Mostrar en directorio</th>
                    <tbody>
                        <tr v-for="org in lOrgChartJob">
                            <td>@{{org.id_org_chart_job}}</td>
                            <td>@{{org.is_office}}</td>
                            <td>@{{org.job_name}}</td>
                            <td style="text-align: center">
                                <div class="checkbox-wrapper-22">
                                    <label class="switch" :for="'checkbox'+org.id_org_chart_job">
                                        <input type="checkbox" :id="'checkbox'+org.id_org_chart_job" :checked="org.is_office == 1" 
                                        v-on:click="updateOrgChartJob(org.id_org_chart_job, org.job_name, 'checkbox'+org.id_org_chart_job)"/>
                                        <div class="slider round"></div>
                                    </label>
                                </div>
                                <p hidden>@{{org.id_org_chart_job == 1 ? 'sí' : 'No'}}</p>
                            </td>
                        </tr>
                    </tbody>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var self;
</script>
{{-- Mi tabla --}}
@include('layouts.table_jsControll', [
                                        'table_id' => 'areasTable',
                                        'colTargets' => [0,1],
                                        'colTargetsSercheable' => [],
                                        'noSort' => true,
                                    ] )
@include('layouts.manual_jsControll')
<script type="text/javascript" src="{{ asset('myApp/Utils/toastNotifications.js') }}"></script>
<script type="text/javascript" src="{{ asset('myApp/Adm/vue_officeOrgChartJob.js') }}"></script>
@endsection