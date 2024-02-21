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

<body>
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-body">
                @if($type == 2)
                    <h3>Se actualizó el usuario:</h3>
                @else
                    <h3>Se insertó el usuairo:</h3>
                @endif
                <ul>
                    <li>Usuario: {{$oUser->username}}</li>
                    <li>Nombre: {{$oUser->full_name}}</li>
                    <li>Email: {{$oUser->institutional_mail}}</li>
                </ul>
            </div>
        </div>
    </div>
</body>

</html>