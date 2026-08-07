<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal GH - Notificación a Gestión Humana</title>
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
</head>
<style>
    hr { 
        display: block;
        margin-top: 0.5em;
        margin-bottom: 0.5em;
        border-style: inset;
        border-width: 1px;
    }
</style>
<body>
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div>
                    <h3 style="color: #d9534f;">
                        ⚠️ Notificación a Gestión Humana: Solicitud descartada
                    </h3>
                </div>
                <br>
                <div>
                    <p>
                        Se informa que se ha <b>descartado</b> la siguiente incidencia/solicitud que requiere su revisión (posiblemente correspondiente a una nómina pagada o en proceso):
                    </p>
                    <table class="table table-bordered" style="width: 100%; max-width: 600px;">
                        <tbody>
                            <tr>
                                <td style="text-align: left; font-weight: bold;">Colaborador:</td>
                                <td style="text-align: left;">{{$oEmployee->full_name}}</td>
                            </tr>
                            <tr>
                                <td style="text-align: left; font-weight: bold;">Tipo de Solicitud:</td>
                                <td style="text-align: left;">{{$oApplication->type_name}}</td>
                            </tr>
                            <tr>
                                <td style="text-align: left; font-weight: bold;">Período / Fecha:</td>
                                <td style="text-align: left;">
                                    @if ($oApplication->start_date == $oApplication->end_date)
                                        {{$oApplication->start_date}}
                                    @else
                                        Del {{$oApplication->start_date}} al {{$oApplication->end_date}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: left; font-weight: bold;">Fecha de descarte:</td>
                                <td style="text-align: left;">{{$oApplication->updated_at}}</td>
                            </tr>
                            <tr>
                                <td style="text-align: left; font-weight: bold;">Usuario que descartó:</td>
                                <td style="text-align: left;">{{$oSuperviser->full_name}}</td>
                            </tr>
                            @if(!empty($oApplication->sup_comments_n))
                            <tr>
                                <td style="text-align: left; font-weight: bold;">Comentarios:</td>
                                <td style="text-align: left;">{{$oApplication->sup_comments_n}}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <br>
                <hr>
                <div>
                    <p style="transform: scale(0.6); transform-origin: left top;">
                    Favor de no responder este mail, fue generado de forma automática.<br>
                    Portal GH 1.0 © Software Aplicado SA de CV<br>
                    www.swaplicado.com.mx
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>