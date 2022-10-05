@extends('layouts.principal')

@section('headJs')
    <script>
        function GlobalData(){
            this.lEmployees = <?php echo json_encode($lEmployees); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4">
    <div class="card-body">
        @foreach($lEmployees as $emp)
            <div class="card shadow mb-4">
                <a href="#id_{{$emp->employee_num}}" class="d-block card-header py-3" data-toggle="collapse"
                    role="button" aria-expanded="false" aria-controls="{{$emp->employee_num}}">
                    <h6 class="m-0 font-weight-bold text-primary">{{$emp->employee}} 
                        <span style="width: 0; border-right: 1px solid #bcbdc2; height: calc(4.375rem - 2rem); margin: auto 1rem"></span>
                        Vacaciónes pendientes: {{$emp->tot_vacation_remaining}} días
                        @if($emp->is_head_user)
                            <span style="width: 0; border-right: 1px solid #bcbdc2; height: calc(4.375rem - 2rem); margin: auto 1rem"></span>
                            Encargado de area
                            <span class="bx bxs-group"></span>
                        @endif
                    </h6>
                </a>
                <div class="collapse" id="id_{{$emp->employee_num}}">
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
                                        <td>{{$emp->full_name}}</td>
                                    </tr>
                                    <tr>
                                        <th>Fecha ingreso:</th>
                                        <td>{{$emp->last_admission_date}}</td>
                                    </tr>
                                    <tr>
                                        <th>Antigüedad:</th>
                                        <td>{{$emp->antiquity}} al día de hoy</td>
                                    </tr>
                                    <tr>
                                        <th>Departamento:</th>
                                        <td>{{$emp->department_name_ui}}</td>
                                    </tr>
                                    <tr>
                                        <th>Puesto:</th>
                                        <td>{{$emp->job_name_ui}}</td>
                                    </tr>
                                    <tr>
                                        <th>Plan de vacaciones:</th>
                                        <td>{{$emp->vacation_plan_name}}</td>
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
                                @foreach($emp->vacation as $vac)
                                    <tr>
                                        <td>{{$vac->date_start}} a {{$vac->date_end}}</td>
                                        <td>{{$vac->id_anniversary}}</td>
                                        <td>{{$vac->vacation_days}}</td>
                                        <td>{{$vac->num_vac_taken}}</td>
                                        <td>{{$vac->expired}}</td>
                                        <td>0</td>
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
                                    <td>{{$emp->tot_vacation_days}}</td>
                                    <td>{{$emp->tot_vacation_taken}}</td>
                                    <td>{{$emp->tot_vacation_expired}}</td>
                                    <td>0</td>
                                    <td>{{$emp->tot_vacation_remaining}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection