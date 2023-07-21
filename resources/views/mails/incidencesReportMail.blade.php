<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Portal GH</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
</head>

<style>
    hr { 
        display: block;
        margin-top: 0.5em;
        margin-bottom: 0.5em;
        margin-left: auto;
        margin-right: auto;
        border-style: inset;
        border-width: 1px;
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }

    td {
        border: 1px solid black;
        padding: 10px;
        text-align: center;
        vertical-align: middle;
        word-wrap: break-word;
        width: 13%;
    }


</style>

<body>
    <h3>
        Sólo se muestran los colaboradores con incidencias de la semana del: {{$date_ini}} al {{$date_end}}
    </h3>
    <div style="width: 100%">
        @foreach ($lEmployees as $emp)
            <table>
                <thead>
                    <tr>
                        <th colspan="{{sizeof($week)}}" style="border: solid 1px black">{{$emp->full_name}}</th>
                    </tr>
                    <tr>
                        @foreach ($week as $w)
                            <th style="border: solid 1px black">
                                {{$w['day_name']}}
                                <br>
                                {{$w['day_num']}}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach ($emp->myWeek as $inc)
                            <td style="border: solid 1px black;">
                                @foreach ($inc['incidences'] as $item)
                                    {{ strtolower($item) }}
                                    <br>
                                @endforeach
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
            <br>
        @endforeach
        @if (count($lEmployees) == 0)
            <h2>No hay colaboradores con incidencias para la semana del: {{$date_ini}} al {{$date_end}}</h2>
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
</body>

</html>