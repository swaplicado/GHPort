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
    }

    th {
        width: 13%;
    }


</style>

<body>
    <h5>
        <p> Sólo colaboradores con incidencias para la semana: </p> {{$sDate}}
    </h5>
    <div style="width: 100%">
        @foreach ($lOrgCharts as $org)
            <div style="width: 100%; background-color: #D4D4D4;">
                <h3 style="font-size: 15px;">
                    {{$org->job_name}} <span style="font-weight: normal;"> - {{$org->level_name}}&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    <span>
                        Lider:
                    </span>
                    @foreach ($org->superviser as $item)
                        <span style="font-weight: normal;"> {{$item}};&nbsp;&nbsp;</span>
                    @endforeach
                </h3>
            </div>
            @foreach ($org->lEmployees as $emp)
                <table>
                    <thead>
                        <tr>
                            @if (is_null($emp->returnDate))
                                <th colspan="{{sizeof($week)}}" style="border: solid 1px black; font-size: 15px;">{{$emp->full_name}}</th>
                            @else
                                <th colspan="{{sizeof($week) + 1}}" style="border: solid 1px black; font-size: 15px;">{{$emp->full_name}}</th>
                            @endif
                        </tr>
                        <tr>
                            @foreach ($week as $w)
                                <th style="border: solid 1px black">
                                    {{$w['day_name']}}
                                    <br>
                                    {{$w['day_num']}}
                                </th>
                            @endforeach
                            <th style="border: solid 1px black; background-color: #D4D4D4">
                                fecha regreso
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach ($emp->myWeek as $inc)
                                @if (count($inc['incidences']) > 0)
                                    <td colspan="{{$inc['span']}}" style="border: solid 1px black; background-color: #C8F9FF">
                                        @foreach ($inc['incidences'] as $item)
                                            {{ $item }}
                                            <br>
                                        @endforeach
                                        @foreach ($inc['comments'] as $item)
                                            {{ mb_strtolower($item) }}
                                            <br>
                                        @endforeach
                                    </td>
                                @elseif($inc['holiday'] != null || $inc['holiday'] != '')
                                    <td style="border: solid 1px black; background-color: #9072FF">
                                        {{$inc['holiday']}}
                                    </td>
                                @else
                                    <td style="border: solid 1px black;">
                                        
                                    </td>
                                @endif
                            @endforeach
                            <td style="border: solid 1px black;">
                                {{$emp->returnDate}}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <br>
            @endforeach
        @endforeach
        @if (count($lOrgCharts) == 0)
            <h2> <p> No hay colaboradores con incidencias para la semana: </p> {{$sDate}}</h2>
        @endif
        <hr>
        <div>
            <p style="transform: scale(0.6);">
            Favor de no responder este mail, fue generado de forma automática.<br>
            Portal GH 1.0 © Software Aplicado SA de CV<br>
            www.swaplicado.com.mx<br>
            Portal GH 1.0 086.0
            </p>
        </div>
    </div>
</body>

</html>