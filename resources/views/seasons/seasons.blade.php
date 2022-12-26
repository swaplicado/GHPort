@extends('layouts.principal')

@section('headStyles')
    <link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
    <style>
        .table5rem td {
            height: 5rem;
        }

        /* (A) FLEX CONTAINER */
        .wrap-flex {
            display: flex;
            align-items: stretch; /* baseline | center | stretch */
            float: right;
        }
        
        /* (B) NOT REALLY IMPORTANT - COSMETICS */
        .wrap-flex > * {
            width: 33%;
        }
    </style>
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.select2-class').select2({});
        })
    </script>
    <script>
        var app;
        function GlobalData(){
            /*Ambas vistas*/
            this.lSpecialSeasonType = <?php echo json_encode($lSpecialSeasonType); ?>;
            this.year = <?php echo json_encode($year); ?>;

            /*Datos para la vista temporadas especiales*/
            this.lDeptos = <?php echo json_encode($lDeptos); ?>;
            this.lAreas = <?php echo json_encode($lAreas); ?>;
            this.lEmp = <?php echo json_encode($lEmp); ?>;
            this.lCompany = <?php echo json_encode($lCompany); ?>;
            this.getSpecialSeasonRoute = <?php echo json_encode(route('specialSeasons_getSpecialSeason')); ?>;
            this.saveSpecialSeasonRoute = <?php echo json_encode(route('specialSeasons_saveSpecialSeason')); ?>;

            /*Datos para la vista tipos temporadas especiales*/
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
<a class="btn btn-outline-secondary focus" id="TempEsp" onclick="btnActive('TempEsp');" href="#home" data-role="link">Temporadas especiales</a>
<a class="btn btn-outline-secondary" id="TipTempEsp" onclick="btnActive('TipTempEsp');" href="#other" data-role="link">Tipos temporadas especiales</a>
<div data-page="home" id="home" class="active">
    <div class="card shadow mb-4" id="specialSeason">
        <div class="card-header">
            <h3>
                <b>TEMPORADAS ESPECIALES</b>
                <a href="#" target="_blank">
                    <span class="bx bx-question-mark btn3d" style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
                </a>
            </h3>
        </div>
        <div class="card-body">
            <a class="btn btn-outline-success" v-on:click="SetDepto();" id="btn_depto">Departamento</a>
            <a class="btn btn-outline-secondary" v-on:click="SetArea();" id="btn_area">Area funcional</a>
            <a class="btn btn-outline-info" v-on:click="SetEmpleado();" id="btn_emp">Empleado</a>
            <a class="btn btn-outline-warning"v-on:click="SetEmpresa();" id="btn_comp">Empresa</a>
            <div class="card shadow mb-4">
                <div v-bind:class="['card-header', colorTitle]">
                    <h5>
                        <b>@{{title}}</b>
                    </h5>
                </div>
                <div v-bind:class="['card-body', colorBody]">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto">
                          <label for="selOptions" class="col-form-label">@{{title}}:</label>
                        </div>
                        <div class="col-md-4">
                            <select class="select2-class" name="selOptions[]" multiple="multiple" id="selOptions" style="width: 100%"></select>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary" v-on:click="init();">Iniciar</button>
                        </div>
                    </div>
                    <div class="wrap-flex">
                        <label style="max-width: 6rem;">Filtrar por año:</label>
                        &nbsp;
                        <button v-on:click="year = year - 1; cleanOptions();" class="btn btn-secondary form-control" type="button" style = "width: 3rem;">
                            <span class="bx bx-minus" ></span>
                        </button>
                        <input type="number" class="form-control" v-model="year" readonly style="width: 6rem;">
                        <button v-on:click="year = year + 1; cleanOptions();" class="btn btn-secondary form-control" type="button" style = "width: 3rem;">
                            <span class="bx bx-plus"></span>
                        </button>
                    </div>
                    <br>
                    <br>
                    <div v-for="opt in lOptions" style="overflow-x: auto;">
                        <br>
                        <table class="table table-bordered table5rem" style="width: 100%; background-color: white; margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th colspan="12">@{{opt.text}}</th>
                                </tr>
                                <tr>
                                    <th>Enero</th>
                                    <th>Febrero</th>
                                    <th>Marzo</th>
                                    <th>Abril</th>
                                    <th>Mayo</th>
                                    <th>Junio</th>
                                    <th>Julio</th>
                                    <th>Agosto</th>
                                    <th>Septiembre</th>
                                    <th>Octubre</th>
                                    <th>Noviembre</th>
                                    <th>Diciembre</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td v-bind:class="[ table_class[opt.text]['Enero'].class ]" v-on:click="setSpecialSeason(opt.text, 'Enero')">@{{table_class[opt.text]['Enero'].text}}</td>
                                    <td v-bind:class="[ table_class[opt.text]['Febrero'].class ]" v-on:click="setSpecialSeason(opt.text, 'Febrero')">@{{table_class[opt.text]['Febrero'].text}}</td>
                                    <td v-bind:class="[ table_class[opt.text]['Marzo'].class ]" v-on:click="setSpecialSeason(opt.text, 'Marzo')">@{{table_class[opt.text]['Marzo'].text}}</td>
                                    <td v-bind:class="[ table_class[opt.text]['Abril'].class ]" v-on:click="setSpecialSeason(opt.text, 'Abril')">@{{table_class[opt.text]['Abril'].text}}</td>
                                    <td v-bind:class="[ table_class[opt.text]['Mayo'].class ]" v-on:click="setSpecialSeason(opt.text, 'Mayo')">@{{table_class[opt.text]['Mayo'].text}}</td>
                                    <td v-bind:class="[ table_class[opt.text]['Junio'].class ]" v-on:click="setSpecialSeason(opt.text, 'Junio')">@{{table_class[opt.text]['Junio'].text}}</td>
                                    <td v-bind:class="[ table_class[opt.text]['Julio'].class ]" v-on:click="setSpecialSeason(opt.text, 'Julio')">@{{table_class[opt.text]['Julio'].text}}</td>
                                    <td v-bind:class="[ table_class[opt.text]['Agosto'].class ]" v-on:click="setSpecialSeason(opt.text, 'Agosto')">@{{table_class[opt.text]['Agosto'].text}}</td>
                                    <td v-bind:class="[ table_class[opt.text]['Septiembre'].class ]" v-on:click="setSpecialSeason(opt.text, 'Septiembre')">@{{table_class[opt.text]['Septiembre'].text}}</td>
                                    <td v-bind:class="[ table_class[opt.text]['Octubre'].class ]" v-on:click="setSpecialSeason(opt.text, 'Octubre')">@{{table_class[opt.text]['Octubre'].text}}</td>
                                    <td v-bind:class="[ table_class[opt.text]['Noviembre'].class ]" v-on:click="setSpecialSeason(opt.text, 'Noviembre')">@{{table_class[opt.text]['Noviembre'].text}}</td>
                                    <td v-bind:class="[ table_class[opt.text]['Diciembre'].class ]" v-on:click="setSpecialSeason(opt.text, 'Diciembre')">@{{table_class[opt.text]['Diciembre'].text}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="display_seasons" style="text-align: right">
                        <br>
                        <button class="btn btn-secondary" type="button" v-on:click="cleanOptions();">Cancelar</button>
                        <button class="btn btn-primary" type="button" v-on:click="saveSpecialSeasons();">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div data-page="other" id="other">
    <div class="card shadow mb-4" id="typeSpecialSeason">
        @include('seasons.modal_form_special_season_type')
        <div class="card-header">
            <h3>
                <b>TIPOS TEMPORADAS ESPECIALES</b>
                <a href="#" target="_blank">
                    <span class="bx bx-question-mark btn3d" style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
                </a>
            </h3>
        </div>
        <div class="card-body">
            @include('layouts.table_buttons', [
                'crear' => true,
                'editar' => true,
                // 'delete' => true
            ])
            <button id="btn_delete" type="button" class="btn3d btn-danger" style="display: inline-block; margin-right: 5px" title="Eliminar registro" v-on:click="deleteRegistry();">
                <span class="bx bxs-trash"></span>
            </button>
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
                        <td v-bind:class="[ oType.color ]">@{{oType.priority}}</td>
                        <td>@{{oType.description}}</td>
                        <td>@{{oDateUtils.formatDate(oType.created_at)}}</td>
                        <td>@{{oType.full_name_ui}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
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
                                        // 'delete' => true,
                                    ])
    <script type="text/javascript" src="{{ asset('myApp/seasons/vue_special_season.js') }}"></script>
    <script type="text/javascript" src="{{ asset('myApp/seasons/vue_type_special_season.js') }}"></script>
    <script>
        const btn_ids = ['TempEsp', 'TipTempEsp'];

        function btnActive(id) {
            let btn = document.getElementById(id);
            btn.style.backgroundColor = '#858796';
            btn.style.color = '#fff';

            for (const bt_id of btn_ids) {
                if(bt_id != id){
                    let bt = document.getElementById(bt_id);
                    bt.style.backgroundColor = '#fff';
                    bt.style.color = '#858796';
                    bt.style.boxShadow = '0 0 0';
                }
            }

            if(id == 'TempEsp'){
                app = appSpecialSeason;
                app.initView();
            }else if(id == 'TipTempEsp'){
                app = appTypeSpecialSeason;
                app.initView();
            }
        }
    </script>
    <script>
        var appSeason = (function(){
            let pages = [];
            let links = [];
            
            document.addEventListener("DOMContentLoaded", function(){
                pages = document.querySelectorAll('[data-page]');
                links = document.querySelectorAll('[data-role="link"]');
                [].forEach.call(links, function(link){
                    link.addEventListener("click", navigate)
                });
            });
            
            function navigate(ev){
                ev.preventDefault();
                let id = ev.currentTarget.href.split("#")[1];
                [].forEach.call(pages, function(page){
                if(page.id ===id){
                    page.classList.remove('noActive');
                    page.classList.add('active');
                }else{
                    page.classList.remove('active');
                    page.classList.add('noActive');
                } 
                });
                return false;
            }
        })();
    </script>
@endsection