@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
<style>
    /* Estilo por defecto */
    label {
        margin-bottom: 5px; /* Establece el margen por defecto */
    }

    /* Media query para dispositivos móviles */
    @media screen and (max-width: 768px) {
        label {
            margin-bottom: 0; /* Establece el margen a 0 para dispositivos móviles */
        }
    }

</style>
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
            this.lUser = <?php echo json_encode($users); ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:catalogousuarios" ); ?>;
            //Al modificar index no olvidar agregarlo en la funcion reDraw de vue
            this.indexesUserTable = {
                'idUser':0,
                'username':1,
                'fullname':2,
                'is_active':3,
                'is_active_filter':4,
                'id_config_report':5,
                'id_config_report_filter':6,
                'all_employees':7,
                'organization_level_id':8

            };
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="user">
    <div class="card-header">
        <h3>
            <b>Usuarios configurados</b>
            @include('layouts.manual_button')
        </h3>
    </div>
    <div class="card-body">
        &nbsp;&nbsp;
        <label for="incident_tp_filter">Filtrar por activos: </label>
        <select class="select2-class form-control" name="filterActive" id="filterActive" style="width: 25%;">
            <option value="1" selected="selected">Activos</option>
            <option value="0">Inactivos</option>    
        </select>
        &nbsp;&nbsp;
        <label for="incident_tp_filter">Filtrar por configuración: </label>
        <select class="select2-class form-control" name="filterConfig" id="filterConfig" style="width: 25%;">
            <option value="1" selected="selected">Configurado</option>
            <option value="0">Sin configuración</option>    
        </select>
        <br>
        <br>
        <div class="table-responsive">
            <table class="table table-bordered" id="table_user" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>idUser</th>
                        <th>Usuario</th>
                        <th>Nombre completo</th>
                        <th>Esta activo</th>
                        <th>is_active</th>
                        <th>Reporte configurado</th>
                        <th>is_config</th>
                        <th>Todos los empleados</th>
                        <th>Nivel organización</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in lUser">
                        <td>@{{user.id}}</td>
                        <td>@{{user.username}}</td>
                        <td>@{{user.full_name}}</td>
                        <td>@{{ user.is_active ? 'Activo' : 'Dado de baja' }}</td>
                        <td>@{{ user.is_active ? 1 : 0 }}</td>
                        <td>@{{ user.id_config_report ? 'Configurado' : 'Pendiente de configuración' }}</td>
                        <td>@{{ user.id_config_report ? 1 : 0 }}</td>
                        <td>@{{ user.all_employees ? 'Sí' : 'No' }}</td>
                        <td>@{{user.organization_level_id}}</td>
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
        $(document).ready(function() {
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {

                    let activeFilter = parseInt($('#filterActive').val());

                    let configFilter = parseInt($('#filterConfig').val());

                    let isActiveEmployee = parseInt(data[oServerData.indexesUserTable.is_active_filter], 10); // Asegúrate de que is_direct esté en el índice correcto

                    let isConfigEmployee = parseInt(data[oServerData.indexesUserTable.id_config_report_filter], 10);

                    // Verificar si los valores de los filtros y de los datos son números válidos
            if (isNaN(activeFilter) || isNaN(configFilter) || isNaN(isActiveEmployee) || isNaN(isConfigEmployee)) {
                return true; // Si no es un número válido, no se aplica el filtro
            }

            // Filtrar por "activo" y "configurado"
            if (activeFilter !== isActiveEmployee || configFilter !== isConfigEmployee) {
                return false; // Ocultar si no coinciden
            }

            return true; // Mostrar si ambos coinciden
                                
                }
            );
        });
    </script>
    @include('layouts.table_jsControll', [
                                            'table_id' => 'table_user',
                                            'colTargets' => [],
                                            'colTargetsSercheable' => [0,4,6],
                                            'select' => false,
                                            'edit_modal' => false,
                                            'order' => [1,'asc'],
                                            'noColReorder' => true,
                                        ] )
    @include('layouts.manual_jsControll')
    <script>
        $(document).ready(function() {
            $('#filterActive').change(function() {
                table['table_user'].draw();
            });
        });
    </script>
    <script type="text/javascript" src="{{ asset('myApp/configReportsIncs/vue_config.js') }}"></script>
@endsection