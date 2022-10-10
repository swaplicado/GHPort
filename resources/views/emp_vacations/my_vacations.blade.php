@extends('layouts.principal')

@section('content') 
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="card shadow mb-4">
            <div id="id_vac_{{$user->employee_num}}">
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
                                    <td>{{$user->full_name}}</td>
                                </tr>
                                <tr>
                                    <th>Fecha ingreso:</th>
                                    <td>{{$user->last_admission_date}}</td>
                                </tr>
                                <tr>
                                    <th>Antigüedad:</th>
                                    <td>{{$user->antiquity}} al día de hoy</td>
                                </tr>
                                <tr>
                                    <th>Departamento:</th>
                                    <td>{{$user->department_name_ui}}</td>
                                </tr>
                                <tr>
                                    <th>Puesto:</th>
                                    <td>{{$user->job_name_ui}}</td>
                                </tr>
                                <tr>
                                    <th>Plan de vacaciones:</th>
                                    <td>{{$user->vacation_plan_name}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <br>
                    <table class="table table-bordered">
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
                            @foreach($user->vacation as $vac)
                                <tr>
                                    <td>{{$vac->date_start}} a {{$vac->date_end}}</td>
                                    <td>{{$vac->id_anniversary}}</td>
                                    <td>{{$vac->vacation_days}}</td>
                                    <td>{{$vac->num_vac_taken}}</td>
                                    <td>{{$vac->expired}}</td>
                                    <td>{{$vac->request}}</td>
                                    @if($vac->remaining < 0)
                                        <td style="color: red">{{$vac->remaining}}</td>
                                    @else
                                        <td>{{$vac->remaining}}</td>
                                    @endif
                                </tr>
                            @endforeach
                            <tr class="thead-light">
                                <td></td>
                                <th>Total</th>
                                <td>{{$user->tot_vacation_days}}</td>
                                <td>{{$user->tot_vacation_taken}}</td>
                                <td>{{$user->tot_vacation_expired}}</td>
                                <td>{{$user->tot_vacation_request}}</td>
                                @if($user->tot_vacation_remaining < 0)
                                    <td style="color: red">{{$user->tot_vacation_remaining}}</td>
                                @else
                                    <td>{{$user->tot_vacation_remaining}}</td>
                                @endif
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card shadow mb-4">
            <div id="id_vac_req_{{$user->employee_num}}">
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <th>Fecha solicitud</th>
                            <th>Fecha aprobado/rechazado</th>
                            <th>Fecha vac.</th>
                            <th>Dias efic.</th>
                            <th>Estatus</th>
                            <th>coment.</th>
                        </thead>
                        <tbody>
                            @foreach($user->vacation as $vac)
                                @foreach ($vac->oRequest as $rec)
                                    <tr>
                                        <td>{{\Carbon\Carbon::parse($rec->created_at)->toDateString()}}</td>
                                        <td>
                                            {{
                                                ($rec->request_status_id == 3) ?
                                                    \Carbon\Carbon::parse($rec->approved_date_n)->toDateString() :
                                                    (($rec->request_status_id == 4) ?
                                                        \Carbon\Carbon::parse($rec->approved_date_n)->toDateString() :
                                                        '')
                                            }}
                                        </td>
                                        <td>{{$rec->start_date}} a {{$rec->end_date}}</td>
                                        <td>{{$rec->days_effective}}</td>
                                        <td>{{$rec->applications_st_name}}</td>
                                        <td></td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection