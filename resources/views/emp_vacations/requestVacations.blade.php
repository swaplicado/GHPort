@extends('layouts.principal')

@section('headStyles')
<link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.min.css")}}">
<style>
    ul {
        -webkit-column-count: 3;
        -moz-column-count: 3;
        column-count: 3;
    }
</style>
@endsection

@section('headJs')
    <script src="{{ asset("daterangepicker/jquery.daterangepicker.min.js") }}" type="text/javascript"></script>
    <script>
        function GlobalData(){
            this.lEmployees = <?php echo json_encode($lEmployees); ?>;
            this.year = <?php echo json_encode($year); ?>;
            this.lHolidays = <?php echo json_encode($lHolidays); ?>;
            this.acceptRequestRoute = <?php echo json_encode(route('requestVacations_acceptRequest')); ?>;
            this.rejectRequestRoute = <?php echo json_encode(route('requestVacations_rejectRequest')); ?>;
            this.filterYearRoute = <?php echo json_encode(route('requestVacations_filterYear')); ?>;
            this.checkMailRoute = <?php echo json_encode(route('requestVacations_checkMail')); ?>;
            this.const = <?php echo json_encode($constants); ?>;
            this.idApplication = <?php echo json_encode($idApplication); ?>;
            //Al agregar un nuevo index no olvidar agregarlo en la funcion reDraw de vue
            this.indexes = {
                'id':0,
                'user_id':1,
                'payment_frec_id':2,
                'request_status_id':3,
                'take_holidays':4,
                'take_rest_days':5,
                'sup_comments': 6,
                'folio':7,
                'employee':8,
                'created_at':9,
                'approved_date':10,
                'start_date':11,
                'end_date':12,
                'return_date':13,
                'total_days':14,
                'applications_st_name':15,
                'comments':16
            };
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
                <th>request_status_id</th>
                <th>take_holidays</th>
                <th>take_rest_days</th>
                <th>sup comments</th>
                <th>Folio</th>
                <th>Empleado</th>
                <th>Fecha solicitud</th>
                <th style="max-width: 15%;">Fecha aprobado/rechazado</th>
                <th>Fecha inicio</th>
                <th>Fecha fin</th>
                <th>Fecha regreso</th>
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
                        <td>@{{rec.request_status_id}}</td>
                        <td>@{{rec.take_holidays}}</td>
                        <td>@{{rec.take_rest_days}}</td>
                        <td>@{{rec.sup_comments_n}}</td>
                        <td>@{{rec.folio_n}}</td>
                        <td>@{{emp.employee}}</td>
                        <td>@{{oDateUtils.formatDate(rec.created_at, 'ddd DD-MMM-YYYY')}}</td>
                        <td>
                            @{{
                                (rec.request_status_id == oData.const.APPLICATION_APROBADO) ?
                                    oDateUtils.formatDate(rec.approved_date_n, 'ddd DD-MMM-YYYY') :
                                    ((rec.request_status_id == oData.const.APPLICATION_RECHAZADO) ?
                                        oDateUtils.formatDate(rec.rejected_date_n, 'ddd DD-MMM-YYYY') :
                                        '')
                            }}
                        </td>
                        <td>@{{oDateUtils.formatDate(rec.start_date, 'ddd DD-MMM-YYYY')}}</td>
                        <td>@{{oDateUtils.formatDate(rec.end_date, 'ddd DD-MMM-YYYY')}}</td>
                        <td>@{{oDateUtils.formatDate(rec.returnDate, 'ddd DD-MMM-YYYY')}}</td>
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
    moment.locale('es');
    $(document).ready(function () {
        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                let registerVal = parseInt( $('#rqStatus').val(), 10 );
                let filter = 0;

                switch (registerVal) {
                    case 0:
                        filter = parseInt( data[oServerData.indexes.request_status_id] );
                        return filter === 2;
                        
                    case 1:
                        filter = parseInt( data[oServerData.indexes.request_status_id] );
                        return filter === 3;

                    case 2:
                        filter = parseInt( data[oServerData.indexes.request_status_id] );
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
                                        'colTargets' => [1,2,4,5,6],
                                        'colTargetsSercheable' => [0,3],
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
<script type="text/javascript" src="{{ asset('myApp/emp_vacations/vacations_utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('myApp/emp_vacations/vue_request_vacations.js') }}"></script>
<script>
    $(document).ready(function (){

        $.dateRangePickerLanguages['es'] =
        {
            'selected': 'De:',
            'days': 'Dias',
            'apply': 'Cerrar',
            'week-1' : 'Lun',
            'week-2' : 'Mar',
            'week-3' : 'Mie',
            'week-4' : 'Jue',
            'week-5' : 'Vie',
            'week-6' : 'Sab',
            'week-7' : 'Dom',
            'month-name': ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','octubre','Noviembre','Diciembre'],
            'shortcuts' : 'Shortcuts',
            'past': 'Past',
            '7days' : '7 días',
            '14days' : '14 días',
            '30days' : '30 días',
            'previous' : 'Anterior',
            'prev-week' : 'Semana',
            'prev-month' : 'Mes',
            'prev-quarter' : 'Quincena',
            'prev-year' : 'Año',
            'less-than' : 'El rango de fecha debe ser mayor a %d días',
            'more-than' : 'El rango de fecha debe ser menor a %d días',
            'default-more' : 'Selecciona un rango de fecha mayor a %d días',
            'default-less' : 'Selecciona un rango de fecha menor a %d días',
            'default-range' : 'Selecciona un rango de fecha entre %d y %d días',
            'default-default': ''
        };
    
        $('#two-inputs').dateRangePicker(
        {
            // startDate: oServerData.initialCalendarDate,
            inline:true,
            container: '#two-inputs',
            alwaysOpen:true,
            language: 'es',
            separator : ' a ',
            showShortcuts: false,
            beforeShowDay: function(t)
            {
                var valid = false;
                var _class = '';
                var _tooltip = '';
                if(table['table_requestVac'].row('.selected').data() != undefined){
                    if(table['table_requestVac'].row('.selected').data()[oServerData.indexes.payment_frec_id] == oServerData.const.QUINCENA){
                        _class = (t.getDay() == 0 || t.getDay() == 6) ? 
                                    'restDay' : 
                                        (oServerData.lHolidays.includes(moment(t.getTime()).format('YYYY-MM-DD')) ? 
                                            'holiday' : '');
                    } else {
                        _class = (t.getDay() == 0) ? 
                                    'restDay' : 
                                        (oServerData.lHolidays.includes(moment(t.getTime()).format('YYYY-MM-DD')) ? 
                                            'holiday' : '');
                    }
                }
     
                return [valid,_class,_tooltip];
            },
            getValue: function(){
                if ($('#date-range200').val() && $('#date-range201').val() ){
                    return $('#date-range200').val() + ' a ' + $('#date-range201').val();
                }
                else{
                    return '';
                }
            },
            setValue: function(s,s1,s2){
                $('#date-range200').val(s1);
                $('#date-range201').val(s2);
            }
        });
    });
</script>
@endsection