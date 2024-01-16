@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
    </script>
    <script>
        function GlobalData(){
            this.lUser = <?php echo json_encode($lUser); ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:directory" ); ?>;
            //Al modificar index no olvidar agregarlo en la funcion reDraw de vue
            this.indexesUserTable = {
                'idUser':0,
                'fullname':1,
                'personalMail':2,
                'institutionalMail':3,
                'directoryMail':4,
                'telArea':5,
                'telNum':6,
                'telExt':7,
                'nameOrg':8,
            };
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="user">
    <div class="card-header">
        <h3>
            <b>Directorio AETH</b>
            @include('layouts.manual_button')
        </h3>
    </div>
    <div class="card-body">
        <br>
        <br>
        <div class="table-responsive">
            <table class="table table-bordered" id="table_user" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>user_id</th>
                        <th>Colaborador</th>
                        <th>Área funcional</th>
                        <th>Correo institucional</th>
                        <th>Teléfono institucional</th>
                        <th>Extensión telefónica</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in lUser">
                        <td>@{{user.idUser}}</td>
                        <td>@{{user.fullname}}</td>
                        <td>@{{user.nameOrg}}</td>
                        <td>@{{user.directoryMail}}</td>
                        <td>@{{user.telNum}}</td>
                        {{--<td v-else="user.telArea != ''">@{{user.telArea}} - @{{user.telNum}}</td>--}}
                        <!--<td>@{{user.telArea}} - @{{user.telNum}}</td>-->
                        <td>@{{user.telExt}}</td>
                        <!--<td v-if="user.active == 0">No</td>-->
                        <!--<td v-else="user.active == 1">Sí</td>-->
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @include('layouts.table_jsControll', [
                                            'table_id' => 'table_user',
                                            'colTargets' => [0],
                                            'colTargetsSercheable' => [],
                                            'order' => [1,'asc'],
                                            'noColReorder' => true,
                                            'lengthMenu' => [-1]
                                        ] )
    @include('layouts.manual_jsControll')
    <script type="text/javascript" src="{{ asset('myApp/Adm/vue_directory.js') }}"></script>
@endsection