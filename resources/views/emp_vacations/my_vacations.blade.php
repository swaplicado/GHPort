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
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="myVacations">
    @include('emp_vacations.modal_myRequest')
    <div class="card-body">
        <div class="card shadow mb-4">
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
                    <table class="table table-bordered display" id="vacationsTable" style="width: 100%;">
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
            <div>
                <div class="card-body">
                    @include('layouts.table_buttons', ['crear' => true, 'editar' => true, 'delete' => true, 'send' => true])
                    <div class="col-md-3" style="float: right;">
                        <button v-on:click="year = year - 1;" class="btn btn-secondary" type="button" style = "display: inline;">
                            <span class="bx bx-minus" ></span>
                        </button>
                        <input type="number" class="form-control" v-model="year" readonly style="width: 10ch; display: inline;">
                        <button v-on:click="year = year + 1;" class="btn btn-secondary" type="button" style = "display: inline;">
                            <span class="bx bx-plus"></span>
                        </button>
                        <button type="button" class="btn btn-primary" style="float: right;"  v-on:click="filterYear();">
                            <span class="bx bx-search"></span>
                        </button>
                    </div>
                    <br>
                    <br>
                    <table class="table table-bordered" id="table_myRequest" style="width: 100%;">
                        <thead class="thead-light">
                            <th>id</th>
                            <th>start date</th>
                            <th>end date</th>
                            <th>Fecha solicitud</th>
                            <th style="max-width: 20%;">Fecha aprobado/rechazado</th>
                            <th>Fecha vac.</th>
                            <th>Dias efic.</th>
                            <th>Estatus</th>
                            <th>coment.</th>
                        </thead>
                        <tbody>
                            <tr v-for="rec in oUser.applications">
                                <td>@{{rec.id_application}}</td>
                                <td>@{{rec.start_date}}</td>
                                <td>@{{rec.end_date}}</td>
                                <td>@{{formatDate(rec.created_at)}}</td>
                                <td>
                                    @{{
                                        (rec.request_status_id == 3) ?
                                            rec.approved_date_n :
                                            ((rec.request_status_id == 4) ?
                                                rec.approved_date_n :
                                                '')
                                    }}
                                </td>
                                <td>@{{rec.start_date}} a @{{rec.end_date}}</td>
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
                                        'colTargets' => [0,1,2],
                                        'colTargetsSercheable' => [],
                                        'select' => true,
                                        'noSearch' => true,
                                        'noDom' => true,
                                        'noPaging' => true,
                                        'noInfo' => true,
                                        'noSort' => true,
                                        'edit_modal' => true,
                                        'crear_modal' => true,
                                        'delete' => true,
                                        'send' => true
                                    ] )
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
		getValue: function(){
			if ($('#date-range200').val() && $('#date-range201').val() ){
                app.startDate = $('#date-range200').val();
                app.endDate = $('#date-range201').val();
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
            app.startDate = $('#date-range200').val();
            app.endDate = $('#date-range201').val();
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