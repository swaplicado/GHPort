@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
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
    
    <a class="btn btn-outline-secondary focus" id="empCert" href="#" v-on:click="setViewMode('empCert');" v-show="oUser.rol_id != roles.ESTANDAR">Certificados mis colabs.</a>
    <a class="btn btn-outline-secondary" id="myCert" href="#" v-on:click="setViewMode('myCert');">Mis certificados</a>

    <div class="card shadow mb-4">
        <div class="card-header">
            <h3>
                <b>Certificados de colaboradores</b>
                @include('layouts.manual_button')
            </h3>
        </div>
        <div class="card-body">
            <div v-show="viewMode == 'empCert'">
                <div class="wrap">
                    <div class="elem">
                        <div class="ks-cboxtags">
                            <div class="ks-cbox">
                                <input type="checkbox" id="checkBoxAllEmployees" v-on:click="getAllEmployees();">
                                <label for="checkBoxAllEmployees">Todos los colaboradores</label>
                            </div>
                        </div>
                    </div>
                </div>
                <table class="table table-bordered" id="employees_table">
                    <thead class="thead-light">
                        <th>id_employee</th>
                        <th>Colaborador</th>
                        <th>Número colab.</th>
                        <th>Área</th>
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
                <br>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-check">
                            <select class="select2-class form-control" name="filter_withCertificate" id="filter_withCertificate" style="width: 90%;"></select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="checkCuadrants" v-on:change="filterCuadrantTable()" checked>
                            <label class="form-check-label" for="checkCuadrants">
                                Cuadrantes
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="checkModules" v-on:change="filterCuadrantTable()">
                            <label class="form-check-label" for="checkModules">
                                Modulos
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="checkCourses" v-on:change="filterCuadrantTable()">
                            <label class="form-check-label" for="checkCourses">
                                Cursos
                            </label>
                        </div>
                    </div>
                </div>
                <div>
                    <table class="table table-bordered" id="cuadrants_table">
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
                            <th>Modulo</th>
                            <th>Curso</th>
                            <th>Estatus</th>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <button class="btn btn-primary" v-on:click="getCertificates();">Imprimir certificados</button>
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
                    if(withCeretificate == 0){
                        return data[oServerData.indexesCuadrantsTable.withCertificate] == 1 && lTypes.includes(col_type);
                    }else{
                        return lTypes.includes(col_type);
                    }
                }
            }
        );

        $('#filter_withCertificate').change( function() {
            table['cuadrants_table'].draw();
        })
    });
</script>
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
                                        'colTargetsSercheable' => [2,6],
                                        'noPaging' => true,
                                        'noDom' => true,
                                        'selectMulti' => true,
                                    ] )

<script type="text/javascript" src="{{ asset('myApp/univCertificates/vue_univCertificates.js') }}"></script>
@endsection