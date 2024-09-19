@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
            this.lUser = <?php echo json_encode($lUser) ?>;
            this.lastDateUpdateDP = <?php echo json_encode($lastDateUpdateDP) ?>;
            this.lastDateUpdateCV = <?php echo json_encode($lastDateUpdateCV) ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:userdatalogs" ); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="curriculumLogsApp">
    <div class="card-header">
        <h3>
            <b>Registro de actualización de datos</b>
            @include('layouts.manual_button')
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <div>
                    <b>Última  campaña de actualización de datos personales (DP):</b>
                    <span v-if="lastDateUpdateDP != null">
                        @{{ oDateUtils.formatDate(lastDateUpdateDP.start_date) }} a @{{ oDateUtils.formatDate(lastDateUpdateDP.end_date) }}
                    </span>
                    <span v-else>
                        ND
                    </span>
                </div>
                <div>
                    <b>Última  campaña de actualización de curriculum vitae (CV):</b>
                    <span v-if="lastDateUpdateCV != null">
                        @{{ oDateUtils.formatDate(lastDateUpdateCV.start_date) }} a @{{ oDateUtils.formatDate(lastDateUpdateCV.end_date) }}
                    </span>
                    <span v-else>
                        ND
                    </span>
                </div>
            </div>
            <div class="col-md-4">
                <div>
                    <span style="background-color: #fad7a0">
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </span>
                    &nbsp;La fecha de actualización es anterior a la última campaña
                </div>
                <div>
                    <span style="background-color: #f5b7b1">
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </span>
                    &nbsp;No existe actualización de datos
                </div>
            </div>
        </div>
        <br>
        <div class="table-responsive">
            <table class="table table-bordered" id="table_curriculum" ref="table_curriculum" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <th>Colaborador</th>
                    <th>Última act. DP</th>
                    <th>Última act. CV</th>
                </thead>
                <tbody>
                    <tr v-for="user in lUser">
                        <td>@{{ user.full_name }}</td>
                        <td v-bind:style="{ 'background-color': user.colorDP }">
                            @{{ user.DP_updated_at ? oDateUtils.formatDate(user.DP_updated_at) : '' }}
                        </td>
                        <td v-bind:style="{ 'background-color': user.colorCV }">
                            @{{ user.CV_updated_at ? oDateUtils.formatDate(user.CV_updated_at) : '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')

<script>
    moment.locale('es');
</script>
@include('layouts.table_jsControll', [
                                    'table_id' => 'table_curriculum',
                                    'colTargets' => [],
                                    'colTargetsSercheable' => [],
                                    'noDom' => true,
                                ] )
@include('layouts.manual_jsControll')
<script type="text/javascript" src="{{ asset('myApp/curriculum/vue_curriculumLogs.js') }}"></script>
@endsection