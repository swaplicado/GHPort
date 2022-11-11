@extends('layouts.principal')
@section('headStyles')
<link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.min.css")}}">
<style>
    ul {
        -webkit-column-count: 3;
        -moz-column-count: 3;
        column-count: 3;
    }

    input:invalid {
        color: red;
    }

    [type="date"]::-webkit-inner-spin-button {
    display: none;
    }
    [type="date"]::-webkit-calendar-picker-indicator {
    display: none;
    }
</style>
@endsection
@section('headJs')
    <script src="{{ asset("daterangepicker/jquery.daterangepicker.min.js") }}" type="text/javascript"></script>
    <script>
        function GlobalData(){
            this.oUser = <?php echo json_encode($user); ?>;
            this.initialCalendarDate = <?php echo json_encode($initialCalendarDate); ?>;
            this.lHolidays = <?php echo json_encode($lHolidays); ?>;
            this.year = <?php echo json_encode($year); ?>;
            this.requestVacRoute = <?php echo json_encode(route('myVacations_setRequestVac')); ?>;
            this.updateRequestVacRoute = <?php echo json_encode(route('myVacations_updateRequestVac')); ?>;
            this.filterYearRoute = <?php echo json_encode(route('myVacations_filterYear')); ?>;
            this.deleteRequestRoute = <?php echo json_encode(route('myVacations_delete_requestVac')); ?>;
            this.sendRequestRoute = <?php echo json_encode(route('myVacations_send_requestVac')); ?>;
            this.checkMailRoute = <?php echo json_encode(route('myVacations_checkMail')); ?>;
            this.const = <?php echo json_encode($constants); ?>;
            //Al modificar index no olvidar agregarlo en la funcion reDraw de vue
            this.indexes = {
                'id':0,
                'request_status_id':1,
                'take_holidays':2,
                'take_rest_days':3,
                'comments':4,
                'request_date':5,
                'folio':6,
                'accept_reject_date':7,
                'start_date':8,
                'end_date':9,
                'return_date':10,
                'taked_days':11,
                'status':12,
                'sup_comments':13,
            };
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="myVacations">
    @include('emp_vacations.modal_myRequest')
    <div class="card-body">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h3>
                    <b>MIS VACACIONES</b>
                    <a href="http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:misvacaciones#mis_vacaciones" target="_blank">
                        <span class="bx bx-question-mark btn3d" style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
                    </a>
                </h3>
            </div>
            <div>
                <div class="card-body">
                    <div class="col-md-6 card border-left-primary">
                        <table style="margin-left: 10px;">
                            <thead>
                                <th></th>
                                <th></th>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Nombre:</th>
                                    <td>@{{oUser.full_name}}</td>
                                </tr>
                                <tr>
                                    <th>Fecha ingreso:</th>
                                    <td>@{{oUser.last_admission_date}}</td>
                                </tr>
                                <tr>
                                    <th>Antigüedad:</th>
                                    <td>@{{oUser.antiquity}} al día de hoy</td>
                                </tr>
                                <tr>
                                    <th>Departamento:</th>
                                    <td>@{{oUser.department_name_ui}}</td>
                                </tr>
                                <tr>
                                    <th>Puesto:</th>
                                    <td>@{{oUser.job_name_ui}}</td>
                                </tr>
                                <tr>
                                    <th>Plan de vacaciones:</th>
                                    <td>@{{oUser.vacation_plan_name}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <br>
                    <table class="table table-bordered" id="vacationsTable" style="width: 100%;">
                        <thead class="thead-light">
                            <th>Periodo</th>
                            <th>Aniversario</th>
                            <th>Vac. ganadas</th>
                            <th>Vac. gozadas</th>
                            <th>Vac. vencidas</th>
                            <th>Vac. solicitadas</th>
                            <th>Vac. pendientes</th>
                        </thead>
                        <tbody>
                            <tr v-for="vac in oUser.vacation">
                                <td>@{{vac.date_start}} a @{{vac.date_end}}</td>
                                <td>@{{vac.id_anniversary}}</td>
                                <td>@{{vac.vacation_days}}</td>
                                <td>@{{vac.num_vac_taken}}</td>
                                <td>@{{vac.expired}}</td>
                                <td>@{{vac.request}}</td>
                                <td v-if="vac.remaining >= 0">@{{vac.remaining}}</td>
                                <td v-else style="color: red">@{{vac.remaining}}</td>
                            </tr>
                            <tr class="thead-light">
                                <td></td>
                                <th>Total</th>
                                <td>@{{oUser.tot_vacation_days}}</td>
                                <td>@{{oUser.tot_vacation_taken}}</td>
                                <td>@{{oUser.tot_vacation_expired}}</td>
                                <td>@{{oUser.tot_vacation_request}}</td>
                                <td v-if="oUser.tot_vacation_remaining >= 0">@{{oUser.tot_vacation_remaining}}</td>
                                <td v-else style="color: red">@{{oUser.tot_vacation_remaining}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card shadow mb-4">
            <div class="card-header">
                <h3>
                    <b>SOLICITUDES VACACIONES</b>
                    <a href="http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:misvacaciones#solicitud_de_vacaciones" target="_blank">
                        <span class="bx bx-question-mark btn3d" style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
                    </a>
                </h3>
            </div>
            <div>
                <div class="card-body">
                    @include('layouts.table_buttons', ['crear' => true, 'editar' => true, 'delete' => true, 'send' => true])
                    <div class="col-md-7" style="float: right; text-align: right; padding-right: 0 !important;">
                        <label for="rqStatus">Filtrar por estatus: </label>
                        <select class="form-control inline" name="rqStatus" id="rqStatus" style="width: 30%;">
                            <option value="0" selected>Creados</option>
                            <option value="1">Enviados</option>
                            <option value="2">Aprobados</option>
                            <option value="3">Rechazados</option>
                            <option value="4">Consumidos</option>
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
                    <table class="table table-bordered" id="table_myRequest" style="width: 100%;">
                        <thead class="thead-light">
                            <th>id</th>
                            <th>request_status_id</th>
                            <th>take_holidays</th>
                            <th>take_rest_days</th>
                            <th>emp coment.</th>
                            <th>Fecha solicitud</th>
                            <th>Folio</th>
                            <th style="max-width: 20%;">Fecha aprobado/rechazado</th>
                            <th>Fecha incio</th>
                            <th>Fecha fin</th>
                            <th>Fecha regreso</th>
                            <th>Dias efic.</th>
                            <th>Estatus</th>
                            <th>sup coment.</th>
                        </thead>
                        <tbody>
                            <tr v-for="rec in oUser.applications">
                                <td>@{{rec.id_application}}</td>
                                <td>@{{rec.request_status_id}}</td>
                                <td>@{{rec.take_holidays}}</td>
                                <td>@{{rec.take_rest_days}}</td>
                                <td>@{{rec.emp_comments_n}}</td>
                                <td>@{{formatDate(rec.created_at)}}</td>
                                <td>@{{rec.folio_n}}</td>
                                <td>
                                    @{{
                                        (rec.request_status_id == oData.APPLICATION_APROBADO) ?
                                            rec.approved_date_n :
                                            ((rec.request_status_id == oData.APPLICATION_RECHAZADO) ?
                                                rec.approved_date_n :
                                                '')
                                    }}
                                </td>
                                <td>@{{formatDate(rec.start_date)}}</td>
                                <td>@{{formatDate(rec.end_date)}}</td>
                                <td>@{{formatDate(rec.returnDate)}}</td>
                                <td>@{{rec.total_days}}</td>
                                <td>@{{rec.applications_st_name}}</td>
                                <td>@{{rec.sup_comments_n}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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
                        return filter === 1;
                        
                    case 1:
                        filter = parseInt( data[oServerData.indexes.request_status_id] );
                        return filter === 2;

                    case 2:
                        filter = parseInt( data[oServerData.indexes.request_status_id] );
                        return filter === 3;

                    case 3:
                        filter = parseInt( data[oServerData.indexes.request_status_id] );
                        return filter === 4;

                    case 4:
                        filter = parseInt( data[oServerData.indexes.request_status_id] );
                        return filter === 5;

                    default:
                        break;
                }

                return false;
            }
        );
    });
</script>
@include('layouts.table_jsControll', [
                                        'table_id' => 'vacationsTable',
                                        'colTargets' => [],
                                        'colTargetsSercheable' => [],
                                        'noSearch' => true,
                                        'noDom' => true,
                                        'noPaging' => true,
                                        'noInfo' => true,
                                        'noColReorder' => true,
                                        'noSort' => true
                                    ] )

@include('layouts.table_jsControll', [
                                        'table_id' => 'table_myRequest',
                                        'colTargets' => [0,2,3,4],
                                        'colTargetsSercheable' => [1],
                                        'select' => true,
                                        // 'noSearch' => true,
                                        'noDom' => true,
                                        // 'noPaging' => true,
                                        // 'noInfo' => true,
                                        // 'noSort' => true,
                                        'order' => [[4, 'desc']],
                                        'edit_modal' => true,
                                        'crear_modal' => true,
                                        'delete' => true,
                                        'send' => true
                                    ] )
<script>
    $(document).ready(function (){
        $('#rqStatus').change( function() {
            table['table_myRequest'].draw();
        });
    });
</script>
<script type="text/javascript" src="{{ asset('myApp/emp_vacations/vacations_utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('myApp/emp_vacations/vue_my_vacations.js') }}"></script>
<script>
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
        startDate: oServerData.initialCalendarDate,
        inline:true,
		container: '#two-inputs',
		alwaysOpen:true,
        language: 'es',
		separator : ' a ',
        beforeShowDay: function(t)
        {
            var valid = true;
            var _class = '';
            var _tooltip = '';
            if(oServerData.oUser.payment_frec_id == oServerData.const.QUINCENA){
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
 
            return [valid,_class,_tooltip];
        },
		getValue: function(){
			if ($('#date-range200').val() && $('#date-range201').val() ){
                app.startDate = moment($('#date-range200').val()).format("ddd DD-MM-YYYY");
                app.endDate = moment($('#date-range201').val()).format("ddd DD-MM-YYYY");
                app.getDataDays();
				return $('#date-range200').val() + ' a ' + $('#date-range201').val();
            }
			else{
				return '';
            }
		},
		setValue: function(s,s1,s2){
			$('#date-range200').val(s1);
			$('#date-range201').val(s2);
            if($('#date-range200').val() && $('#date-range201').val()){
                app.startDate = moment($('#date-range200').val()).format("ddd DD-MM-YYYY");
                app.endDate = moment($('#date-range201').val()).format("ddd DD-MM-YYYY");
            }else{
                app.startDate = '';
                app.endDate = '';
            }
            app.getDataDays();
		}
	});

    $('#clear').click(function(evt){
        evt.stopPropagation();
        $('#two-inputs').data('dateRangePicker').clear();
        app.returnDate = null;
    });
</script>
@endsection