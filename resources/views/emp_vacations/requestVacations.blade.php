@extends('layouts.principal')

@section('headStyles')
<style>
    ul {
        -webkit-column-count: 3;
        -moz-column-count: 3;
        column-count: 3;
    }
</style>
@endsection

@section('headJs')
    <script>
        function GlobalData(){
            this.lEmployees = <?php echo json_encode($lEmployees); ?>;
            this.year = <?php echo json_encode($year); ?>;
            this.lHolidays = <?php echo json_encode($lHolidays); ?>;
            this.acceptRequestRoute = <?php echo json_encode(route('requestVacations_acceptRequest')); ?>;
            this.rejectRequestRoute = <?php echo json_encode(route('requestVacations_rejectRequest')); ?>;
            this.filterYearRoute = <?php echo json_encode(route('requestVacations_filterYear')); ?>;
            this.const = <?php echo json_encode($constants); ?>;
            this.idApplication = <?php echo json_encode($idApplication); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="requestVacations">
    @include('emp_vacations.modal_requests')
    <div class="card-header">
        <h3>
            <b>SOLICITUDES VACACIONES</b>
            <a href="http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:solicitudesvacaciones" target="_blank">
                <span class="bx bx-question-mark btn3d" style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
            </a>
        </h3>
    </div>
    <div class="card-body">
        @include('layouts.table_buttons', ['accept' => true, 'reject' => true])
        <div class="col-md-7" style="float: right; text-align: right; padding-right: 0 !important;">
            <label for="rqStatus">Filtrar por estatus: </label>
            <select class="form-control inline" name="rqStatus" id="rqStatus" style="width: 30%;">
                <option value="0" selected>Nuevos</option>
                <option value="1">Aprobados</option>
                <option value="2">Rechazados</option>
            </select>&nbsp;&nbsp;
            <label>Filtrar por año:</label>
            <button v-on:click="year = year - 1;" class="btn btn-secondary" type="button" style = "display: inline;">
                <span class="bx bx-minus" ></span>
            </button>
            <input type="number" class="form-control" v-model="year" readonly style="width: 10ch; display: inline;">
            <button v-on:click="year = year + 1;" class="btn btn-secondary" type="button" style = "display: inline;">
                <span class="bx bx-plus"></span>
            </button>
            <button type="button" class="btn btn-primary"  v-on:click="filterYear();">
                <span class="bx bx-search"></span>
            </button>
        </div>
        <br>
        <br>
        <table class="table table-bordered" id="table_requestVac" style="width: 100%;">
            <thead class="thead-light">
                <th>id</th>
                <th>user_id</th>
                <th>emp_frecuency_pay</th>
                <th>start date</th>
                <th>end date</th>
                <th>request_status_id</th>
                <th>Empleado</th>
                <th>Fecha solicitud</th>
                <th style="max-width: 20%;">Fecha aprobado/rechazado</th>
                <th>Fecha vac.</th>
                <th>Dias efic.</th>
                <th>Estatus</th>
                <th>coment.</th>
            </thead>
            <tbody>
                <template v-for="emp in lEmployees">
                    <tr v-for="rec in emp.applications">
                        <td>@{{rec.id_application}}</td>
                        <td>@{{rec.user_id}}</td>
                        <td>@{{emp.payment_frec_id}}</td>
                        <td>@{{rec.start_date}}</td>
                        <td>@{{rec.end_date}}</td>
                        <td>@{{rec.request_status_id}}</td>
                        <td>@{{emp.employee}}</td>
                        <td>@{{formatDate(rec.created_at)}}</td>
                        <td>
                            @{{
                                (rec.request_status_id == oData.const.APPLICATION_APROBADO) ?
                                    rec.approved_date_n :
                                    ((rec.request_status_id == oData.const.APPLICATION_RECHAZADO) ?
                                        formatDate(rec.updated_at) :
                                        '')
                            }}
                        </td>
                        <td>@{{rec.start_date}} a @{{rec.end_date}}</td>
                        <td>@{{rec.total_days}}</td>
                        <td>@{{rec.request_status_id == 2 ? 'NUEVO' : rec.applications_st_name}}</td>
                        <td>@{{rec.emp_comments_n}}</td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                let registerVal = parseInt( $('#rqStatus').val(), 10 );
                let filter = 0;

                switch (registerVal) {
                    case 0:
                        filter = parseInt( data[5] );
                        return filter === 2;
                        
                    case 1:
                        filter = parseInt( data[5] );
                        return filter === 3;

                    case 2:
                        filter = parseInt( data[5] );
                        return filter === 4;

                    default:
                        break;
                }

                return false;
            }
        );
    });
</script>
@include('layouts.table_jsControll', [
                                        'table_id' => 'table_requestVac',
                                        'colTargets' => [1,2,3,4],
                                        'colTargetsSercheable' => [0,5],
                                        'select' => true,
                                        'noSort' => true,
                                        'accept' => true,
                                        'reject' => true
                                    ] )
<script>
    $(document).ready(function (){
        $('#rqStatus').change( function() {
            table['table_requestVac'].draw();
        });
        
        var search = document.querySelectorAll('input[type=search]');
        if(app.idApplication != null){
            table['table_requestVac'].columns(0).search( "(^"+app.idApplication+"$)",true,false ).draw();
            table['table_requestVac'].columns(0).search( "", true, true );
            search[0].value = app.idApplication;
        }

    });
</script>
<script type="text/javascript" src="{{ asset('myApp/emp_vacations/vue_request_vacations.js') }}"></script>
@endsection