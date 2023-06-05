@extends('layouts.principal')

@section('headStyles')

@endsection

@section('headJs')
    <script>
        function GlobalData(){
            this.lSpecialSeasonType = <?php echo json_encode($lSpecialSeasonType); ?>;
            this.SeasonTypeSaveRoute = <?php echo json_encode(route('specialSeasonTypes_save')); ?>;
            this.SeasonTypeUpdateRoute = <?php echo json_encode(route('specialSeasonTypes_update')); ?>;
            this.SeasonTypeDeleteRoute = <?php echo json_encode(route('specialSeasonTypes_delete')); ?>;
            this.indexes = {
                'id_special_season_type': 0,
                'color': 1,
                'name': 2,
                'key_code': 3,
                'priority': 4,
                'description': 5,
                'created_at': 6,
                'updated_by': 7,
            };
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content') 
<div class="card shadow mb-4" id="specialTempTypes">
    @include('Adm.modal_form_special_season_type')
    <div class="card-header">
        <h3>
            <b>Tipos temporadas especiales</b>
            <a href="http://192.168.1.251/dokuwiki/doku.php?id=wiki:planvacaciones" target="_blank">
                <span class="bx bx-question-mark btn3d" style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
            </a>
        </h3>
    </div>
    <div class="card-body">
        @include('layouts.table_buttons', [
            'crear' => true,
            'editar' => true,
            'delete' => true
        ])
        <br>
        <br>
        <table class="table table-bordered" id="table_special_season_types" style="width: 100%;">
            <thead class="thead-light">
                <th>id_special_season_type</th>
                <th>Color</th>
                <th>Nombre</th>
                <th>Clave</th>
                <th>Prioridad</th>
                <th>Descripción</th>
                <th>Fecha creación</th>
                <th>Modificado por</th>
            </thead>
            <tbody>
                <tr v-for = "oType in lSpecialSeasonType">
                    <td>@{{oType.id_special_season_type}}</td>
                    <td>@{{oType.color}}</td>
                    <td>@{{oType.name}}</td>
                    <td>@{{oType.key_code}}</td>
                    <td v-bind:style="{ 'color': oType.priority <= 3 ? 'white' : 'black', 'background-color': oType.color }">@{{oType.priority}}</td>
                    <td>@{{oType.description}}</td>
                    <td>@{{oDateUtils.formatDate(oType.created_at)}}</td>
                    <td>@{{oType.full_name_ui}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
@include('layouts.table_jsControll', [
                                        'table_id' => 'table_special_season_types',
                                        'colTargets' => [0, 1],
                                        'colTargetsSercheable' => [],
                                        'select' => true,
                                        'noSort' => true,
                                        // 'show' => true,
                                        'crear_modal' => true,
                                        'edit_modal' => true,
                                        'delete' => true,
                                    ])
<script type="text/javascript" src="{{ asset('myApp/Adm/vue_special_season_types.js') }}"></script>
@endsection