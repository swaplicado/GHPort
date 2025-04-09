@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
<style>
    table.dataTable tr.dtrg-group.dtrg-level-0 th::before {
        content: 'Colaborador: ';
    }

    table.dataTable tr.dtrg-group.dtrg-level-0 th {
        background-color: #049dc99e !important;
        color: black !important;
        font-weight: bold !important;
    }

    /* .dtrg-level-1 td::before {
        content: 'Cuadrante: ';
    } */

    .dtrg-level-1 td {
        background-color: #04867a9e !important;
        color: black !important;
        font-weight: bold !important;
        font-size: 1.1em !important;
        padding-left: 2em !important;
    }

    /* .dtrg-level-2 td::before {
        content: 'Módulo: ';
    } */

    .dtrg-level-2 td {
        background-color: #fbdf439e !important;
        color: black !important;
        font-weight: bold !important;
        font-size: 1.1em !important;
        padding-left: 4em !important;
    }

    td {
        color: black !important;
    }
</style>
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
            this.lEmployees = <?php echo json_encode($lEmployees) ?>;
            this.getCuadrantsRoute = <?php echo json_encode(route('univCertificates_getCuadrants')) ?>;
            this.getCertificatesRoute = <?php echo json_encode(route('univCertificates_getCertificates')) ?>;
            this.getAllEmployeesRoute = <?php echo json_encode(route('univCertificates_getAllEmployees')) ?>;
            this.getMyEmployeesRoute = <?php echo json_encode(route('univCertificates_getMyEmployees')) ?>;
            this.getAllMyEmployeesRoute = <?php echo json_encode(route('univCertificates_getAllMyEmployees')) ?>;
            this.oUser = <?php echo json_encode($oUser) ?>;
            this.roles = <?php echo json_encode($roles) ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:certificadosuniv" ); ?>;
            this.manualRoute[1] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:certificadosuniv#mis_certificados" ); ?>;

            this.indexesEmployeesTable = {
                'id_employee': 0,
                'employee': 1,
                'employee_num': 2,
                'area': 3,
                'depto': 4,
                'job': 5
            };

            this.indexesCuadrantsTable = {
                'id_employee_univ': 0,
                'id_assigment': 1,
                'id_type': 2,
                'id_cuadrant': 3,
                'id_module': 4,
                'id_course': 5,
                'withCertificate': 6,
                'Colaborador': 7,
                'Cuadrante': 8,
                'Modulo': 9,
                'Curso': 10,
                'status': 11
            }
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content')
<div id="certificatesApp">
    
    <a class="btn btn-outline-secondary focus" id="myCert" href="#" v-on:click="setViewMode('myCert');">Mis certificados</a>
    <a class="btn btn-outline-secondary" id="empCert" href="#" v-on:click="setViewMode('empCert');" v-show="oUser.rol_id != roles.ESTANDAR">Certificados mis colabs.</a>

    <div class="card shadow mb-4">
        <div class="card-header">
            <h3>
                <b v-if="viewMode == 'empCert'">Certificados de colaboradores</b>
                <b v-else="viewMode == 'myCert'">Mis certificados</b>
                @include('layouts.manual_button')
                <button style="float: right" class="btn btn-info" onclick="document.getElementById('guia').style.display == 'none' ? document.getElementById('guia').style.display = 'block' : document.getElementById('guia').style.display = 'none';">
                    Ayuda rápida
                </button>
            </h3>
        </div>

<div class="inline">
    <div class="col-xs-12 col-md-8" id="guia" style="display: none; z-index: 2; position: absolute; background-color: white; border: solid 1px black;">
        <label><b>Guía rápida:</b></label>
        <a href="#" style="float: right; color: black;" onclick="document.getElementById('guia').style.display = 'none'"><b>X</b></a>
        <div>
            <ol>
                <li>
                    Colaboradores:
                    <ul>
                        <li>Por defecto se muestran solo tus colaboradores directos, si deseas ver todos tus colaboradores presiona el botón "Todos los colaboradores"</li>
                        <li>Seleccionar a los colaboradores para obtener sus certificados</li>
                        <li>Presionar el botón "Obtener cursos"</li>
                        <li>Ir a la sección Certificados</li>
                    </ul>
                </li>
                <li>
                    Certificados:
                    <ul>
                        <li>De la parte superior seleccionar lo que se desee mostrar (Cuadrantes, Módulos, Cursos)</li>
                        <li>Seleccionar los renglones correspondientes a los cuadrantes, módulos y/o cursos que se desee 
                            (Se pueden seleccionar varios renglones de diferentes colaboradores, 
                            al hacer la descarga los certificados se agruparán por colaborador)</li>
                        <li>Presionar el botón "Descargar certificados" para descargar un ZIP con todos los certificados adjuntos</li>
                    </ul>
                </li>
            </ol>
        </div>
    </div>
</div>

        <div class="card-body">
            <div v-show="viewMode == 'empCert'">
                <h4 style="text-align:center; background-color: #e9ecef">Colaboradores</h4>
                <div class="wrap" style="height: 0; padding: 0;">
                    <div class="elem">
                        <div class="ks-cboxtags">
                            <div class="ks-cbox">
                                <input type="checkbox" id="checkBoxAllEmployees" v-on:click="getAllEmployees();">
                                <label for="checkBoxAllEmployees">Todos los colaboradores</label>
                            </div>
                        </div>
                    </div>
                </div>
                <table class="table table-bordered" id="employees_table" style="width: 100%">
                    <thead class="thead-light">
                        <th>id_employee</th>
                        <th>Colaborador</th>
                        <th>Número colab.</th>
                        <th>Nodo org.</th>
                        <th>Departamento</th>
                        <th>Puesto</th>
                        <tbody>
                            <tr v-for="emp in lEmployees">
                                <td>@{{emp.id}}</td>
                                <td>@{{emp.full_name}}</td>
                                <td>@{{emp.employee_num}}</td>
                                <td>@{{emp.area}}</td>
                                <td>@{{emp.department_name_ui}}</td>
                                <td>@{{emp.job_name_ui}}</td>
                            </tr>
                        </tbody>
                    </thead>
                </table>
                <button class="btn btn-primary" v-on:click="getCuadrants()">Obtener cursos</button>
            </div>
            <div>
                <br>
                <h4 style="text-align:center; background-color: #e9ecef">Certificados</h4>
                <div class="row" hidden>
                    <div class="col-md-3">
                        <div class="form-check">
                            <select class="select2-class form-control" name="filter_withCertificate" id="filter_withCertificate" style="width: 90%;"></select>
                        </div>
                    </div>
                </div>
                <br>
                <div>
                    <table class="table table-bordered" id="cuadrants_table" style="width: 100%">
                        <thead class="thead-light">
                            <th>id_employee_univ</th>
                            <th>id_assigment</th>
                            <th>id_type</th>
                            <th>id_cuadrant</th>
                            <th>id_module</th>
                            <th>id_course</th>
                            <th>withCertificate</th>
                            <th>Colaborador</th>
                            <th>Cuadrante</th>
                            <th>Módulo</th>
                            <th></th>
                            <th>Estatus</th>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <button class="btn btn-primary" v-on:click="getCertificates();">Descargar certificados</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var self;

    $(document).ready(function () {
        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                let lTypes = app.filterType;
                let col_type = parseInt( data[oServerData.indexesCuadrantsTable.id_type] );
                let withCeretificate = parseInt( $('#filter_withCertificate').val(), 10 );

                if(settings.nTable.id == 'employees_table'){
                    return true;
                }

                if(settings.nTable.id == 'cuadrants_table'){
                    // if(withCeretificate == 0){
                    //     // return data[oServerData.indexesCuadrantsTable.withCertificate] == 1 && lTypes.includes(col_type);
                    //     return data[oServerData.indexesCuadrantsTable.withCertificate] == 1;
                    // }else{
                    //     return true;
                    // }
                    return true;
                }
            }
        );

        $('#filter_withCertificate').change( function() {
            table['cuadrants_table'].draw();
        })
    });
</script>
@include('layouts.manual_jsControll')
{{-- Tabla de empleados --}}
@include('layouts.table_jsControll', [
                                        'table_id' => 'employees_table',
                                        'colTargets' => [0],
                                        'colTargetsSercheable' => [],
                                        // 'noPaging' => true,
                                        'noDom' => true,
                                        'selectMulti' => true,
                                        // 'noSort' => true,
                                        'order' => [[1, 'asc']],
                                    ] )

@include('layouts.table_jsControll', [
                                        'table_id' => 'cuadrants_table',
                                        'colTargets' => [0,1,3,4,5],
                                        'colTargetsSercheable' => [2,6,7,8,9],
                                        // 'noPaging' => true,
                                        'noDom' => true,
                                        'selectMulti' => true,
                                        'order' => [[7, 'asc'], [8, 'asc'], [9, 'asc'], [10, 'asc']],
                                        'rowsGroup' => [7],
                                        'noSort' => true,
                                        // 'selectRowGroup' => true,
                                        'noSelectableRow' => true,
                                    ] )

<script type="text/javascript" src="{{ asset('myApp/univCertificates/vue_univCertificates.js') }}"></script>
@endsection