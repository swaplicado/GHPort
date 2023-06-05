@extends('layouts.principal')

@section('headJs')
    <script>
        function GlobalData(){
            this.logs = <?php echo json_encode($logs); ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:bitacoras#bitacora_vacaciones_colaboradores" ); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="logs">
    <div class="card-header">
        <h3>
            <b>Bitácora vacaciones colaboradores</b>
            @include('layouts.manual_button')
        </h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered" id="table_vacations_users">
            <thead class="thead-light">
                <th>id_vacation_user_log</th>
                <th>Fecha bit.</th>
                <th>Colab.</th>
                <th>Aniversario</th>
                <th>Año</th>
                <th>Fecha ini.</th>
                <th>Fecha fin</th>
                <th>Días</th>
                <th>Cerrado</th>
                <th>Cerrado man.</th>
                <th>Cerrado por</th>
                <th>Expirado</th>
                <th>Expirado man.</th>
                <th>Expirado por</th>
                <th>Creado por</th>
                <th>Actualizado po</th>
                <th>Fecha creado</th>
                <th>Fecha actualizado</th>
            </thead>
            <tbody>
                <tr v-for="log in logs">
                    <td>@{{log.id_vacation_user_log}}</td>
                    <td>@{{log.date_log}}</td>
                    <td>@{{log.full_name_ui}}</td>
                    <td>@{{log.id_anniversary}}</td>
                    <td>@{{log.year}}</td>
                    <td>@{{log.date_start}}</td>
                    <td>@{{log.date_end}}</td>
                    <td>@{{log.vacation_days}}</td>
                    <td>@{{log.is_closed ? 'Sí' : 'No'}}</td>
                    <td>@{{log.is_closed_manually ? 'Sí' : 'No'}}</td>
                    <td>@{{log.closed_by_name}}</td>
                    <td>@{{log.is_expired ? 'Sí' : 'No'}}</td>
                    <td>@{{log.is_expired_manually ? 'Sí' : 'No'}}</td>
                    <td>@{{log.expired_by_name}}</td>
                    <td>@{{log.created_by_name}}</td>
                    <td>@{{log.updated_by_name}}</td>
                    <td>@{{log.created_at}}</td>
                    <td>@{{log.updated_at}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
@include('layouts.table_jsControll', [
                                        'table_id' => 'table_vacations_users',
                                        'colTargets' => [0],
                                        'colTargetsSercheable' => [],
                                        'noSort' => true,
                                        'lengthMenu' => [
                                            [50, 100, 150, 200, -1],
                                            [ 'Mostrar 50', 'Mostrar 100', 'Mostrar 150', 'Mostrar 200', 'Mostrar todo' ]
                                        ]
                                    ])
<script type="text/javascript" src="{{ asset('myApp/Adm/vue_vacation_user_log.js') }}"></script>
<script>
    $(document).ready(function(){
        table['table_vacations_users'].columns.adjust().draw();
    });
</script>
@include('layouts.manual_jsControll')
@endsection