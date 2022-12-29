<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Portal GH</title>
    <link href="{{ asset('principal/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <!-- Custom styles for this template-->
    <link href="{{ asset('boxicons/css/boxicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset("datatables/app.css") }}">
    <link rel="stylesheet" href="{{ asset("datatables/datatables.css") }}">
</head>

<style>
    ul {
        -webkit-column-count: 3;
        -moz-column-count: 3;
        column-count: 3;
    }
</style>

<body>
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div>
                    <h3>Hola {{$employee->full_name}}</h3>
                    @if ($application->request_status_id == 3)
                        <h4>Su solicitud ha sido aprobada</h4>
                    @elseif($application->request_status_id == 4)
                        <h4>Su solicitud ha sido rechazada</h4>
                    @else
                        <h4>Comprobar el estatus de su solicitud presionando el botón "Ver mis solicitudes"</h4>
                    @endif
                </div>
                <br>
                <div>
                    <label class="form-label" for="start_date" style="display: inline;">Fecha inicio:</label>
                    <span>{{$application->start_date}}</span>
                </div>
                <div>
                    <label class="form-label" for="end_date" style="display: inline;">Fecha fin:</label>
                    <span>{{$application->end_date}}</span>
                </div>
                <div>
                    <label class="form-label" for="end_date" style="display: inline;">Fecha regreso:</label>
                    <span>{{$returnDate}}</span>
                </div>
                <div>
                    <label class="form-label" for="totalDays" style="display: inline;">Días efectivos:</label>
                    <span name="totalDays">{{$application->total_days}}</span>
                </div>
                <div>
                    <label class="form-label" for="calendarDays" style="display: inline;">Días calendario:</label>
                    <span name="calendarDays">{{$application->tot_calendar_days}}</span>
                </div>
                <div>
                    <label class="form-label" for="listDays">Dias de vacaciones:</label>
                    <ul name="listDays">
                        @foreach($lDays as $day)
                            <li>{{$day}}</li>
                        @endforeach
                    </ul>
                </div>
                <div style="text-align: center">
                    <label class="form-label">Haga click en el siguiente botón para revisar tus solicitudes:</label>
                    <br>
                    <a href="{{route('myVacations')}}" target="_blank">
                        <button  class="btn btn-primary">
                            Ver mis solicitudes
                        </button>
                    </a>
                </div>
                <div>
                    <p>
                        Si tiene algún problema al presionar el botón, copia y pega la siguiente dirección <br>
                        en tu navegador web: <br>
                        <a href="{{route('myVacations')}}" target="_blank">{{route('myVacations')}}</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
