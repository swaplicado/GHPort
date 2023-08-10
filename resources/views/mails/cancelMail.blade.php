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

    hr { 
        display: block;
        margin-top: 0.5em;
        margin-bottom: 0.5em;
        margin-left: auto;
        margin-right: auto;
        border-style: inset;
        border-width: 1px;
    }
</style>

<body>
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div>
                    <h3>
                        {{$oEmployee->full_name}}
                    </h3>
                </div>
                <br>
                <div>
                    <div>
                        @if ($oApplication->type == 'VACACIONES' || $oApplication->type == 'INCIDENCIA')
                            <span>
                                <b>
                                    Su solicitud {{$oApplication->type_name}} para las fechas {{$oApplication->start_date}} a {{$oApplication->end_date}} ha sido cancelada
                                </b>
                            </span>
                        @endif
                        @if ($oApplication->type == 'CUMPLEAÑOS' || $oApplication->type == 'PERMISO')
                            <span>
                                <b>
                                    Su solicitud {{$oApplication->type_name}} para la fecha {{$oApplication->start_date}} ha sido cancelada
                                </b>
                            </span>
                        @endif
                    </div>
                    <table>
                        <thead>
                            <th></th>
                            <th></th>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align: left;">Fecha cancelación:</td>
                                <td style="text-align: left;">{{$oApplication->updated_at}}</td>
                            </tr>
                            <tr>
                                <td style="text-align: left;">Usuario cancelación:</td>
                                <td style="text-align: left;">{{$oSuperviser->full_name}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <br>
                @if($oApplication->type == 'VACACIONES')
                    <div style="text-align: left">
                        <label class="form-label">Haz clic en la siguiente liga para revisar tus solicitudes:</label>
                        <br>
                        <a href="{{route('myVacations')}}" target="_blank">
                            <button  class="btn btn-primary">
                                Ver mis solicitudes
                            </button>
                        </a>
                    </div>
                    <div>
                        <p>
                            Si se presenta algún problema con la liga, copia y pega la siguiente dirección en tu navegador web: 
                            <a href="{{route('myVacations')}}" target="_blank">{{route('myVacations')}}</a>
                        </p>
                    </div>
                @endif
                @if($oApplication->type == 'INCIDENCIA' || $oApplication->type == 'CUMPLEAÑOS')
                    <div style="text-align: left">
                        <label class="form-label">Haz clic en la siguiente liga para revisar tus solicitudes:</label>
                        <br>
                        <a href="{{route('incidences_index')}}" target="_blank">
                            <button  class="btn btn-primary">
                                Ver mis solicitudes
                            </button>
                        </a>
                    </div>
                    <div>
                        <p>
                            Si se presenta algún problema con la liga, copia y pega la siguiente dirección en tu navegador web: 
                            <a href="{{route('incidences_index')}}" target="_blank">{{route('incidences_index')}}</a>
                        </p>
                    </div>
                @endif
                @if($oApplication->type == 'PERMISO')
                    <div style="text-align: left">
                        <label class="form-label">Haz clic en la siguiente liga para revisar tus solicitudes:</label>
                        <br>
                        <a href="{{route('permission_index')}}" target="_blank">
                            <button  class="btn btn-primary">
                                Ver mis solicitudes
                            </button>
                        </a>
                    </div>
                    <div>
                        <p>
                            Si se presenta algún problema con la liga, copia y pega la siguiente dirección en tu navegador web: 
                            <a href="{{route('permission_index')}}" target="_blank">{{route('permission_index')}}</a>
                        </p>
                    </div>
                @endif
                <hr>
                <div>
                    <p style="transform: scale(0.6);">
                    Favor de no responder este mail, fue generado de forma automática.<br>
                    PGH 1.0 © Software Aplicado SA de CV<br>
                    www.swaplicado.com.mx<br>
                    PGH 1.0 086.0
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>