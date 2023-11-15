@extends($layout)

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
            this.lPermissions = <?php echo json_encode($lPermissions); ?>;
            this.lTypes = <?php echo json_encode($lTypes); ?>;
            this.lClass = <?php echo json_encode($lClass); ?>;
            this.permissionsTodayGetRoute = <?php echo json_encode(route('permissions_today_get')) ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:visualizacionpermisos" ); ?>;
            this.indexes_permission = {
                'id': 0,
                'cl_permission_id': 1,
                'type_incident_id': 2,
                'empleado': 3,
                'Clase': 4,
                'Permiso': 5,
                'tiempo': 6,
            }
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="permissions_today_app">
    <div class="card-header">
        <h3>
            Permisos: {{$today}}
            @include('layouts.manual_button')
        </h3>
    </div>
    <div class="card-body">
        <div>
            <label for="permission_tp_filter">Filtrar por tipo: </label>
            <select class="select2-class form-control" name="permission_tp_filter" id="permission_tp_filter" style="width: 15%;"></select>
            <button class="btn btn-info" style="float: right;" v-on:click="refresh()" title="Recarga"><i class='bx bx-refresh'></i></button>
        </div>
        <br>
        <div class="table-responsive">
            <table class="table table-bordered" id="table_permissions" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <th>id</th>
                    <th>cl_permission_id</th>
                    <th>type_incident_id</th>
                    <th>Colaborador</th>
                    <th>Clase</th>
                    <th>Tipo</th>
                    <th>Tiempo</th>
                </thead>
                <tbody>
                    <tr v-for="permission in lPermissions">
                        <td>@{{permission.id_hours_leave}}</td>
                        <td>@{{permission.cl_permission_id}}</td>
                        <td>@{{permission.type_permission_id}}</td>
                        <td>@{{permission.full_name}}</td>
                        <td>@{{permission.permission_cl_name}}</td>
                        <td>@{{permission.permission_tp_name}}</td>
                        <td>@{{permission.time}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')

<script>
    moment.locale('es');
    $(document).ready(function () {
        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                let iType = parseInt( $('#permission_tp_filter').val(), 10 );
                let col_type = null;

                col_type = parseInt( data[oServerData.indexes_permission.type_incident_id] );
                return col_type == iType || iType == 0;
            }
        );
    });
</script>
@include('layouts.table_jsControll', [
                                        'table_id' => 'table_permissions',
                                        'colTargets' => [0],
                                        'colTargetsSercheable' => [1,2],
                                        'lengthMenu' => [
                                                            [ -1, 10, 25, 50, 100 ],
                                                            [ 'Mostrar todo', 'Mostrar 10', 'Mostrar 25', 'Mostrar 50', 'Mostrar 100' ]
                                                        ],
                                        'colTargetsNoOrder' => [4,5,6]
                                    ] )
@include('layouts.manual_jsControll')
<script>
    $(document).ready(function (){
        $('#permission_tp_filter').change( function() {
            table['table_permissions'].draw();
        });
    });
</script>
<script>
    var self;
</script>
<script type="text/javascript" src="{{ asset('myApp/permissions/vue_permissions_today.js') }}"></script>

@endsection