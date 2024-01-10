@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
<style>
    /* Aplicar estilos cuando el ancho de la pantalla es mayor a 600px */
    @media only screen and (min-width: 600px) {
        .label-container {
            border-bottom: 1px solid #7b7b7b7b;
        }

        .label-container label {
            display: block;
            margin-top: 10px;
            margin-bottom: 0px;
            font-weight: bold;
        }
    }

    /* Aplicar estilos cuando el ancho de la pantalla es menor o igual a 600px */
    @media only screen and (max-width: 600px) {
        .label-container {
            border-bottom: 1px solid #ffffff;
        }

        .label-container label {
            display: block;
            margin-top: 10px;
            margin-bottom: 0px;
            font-weight: bold;
        }
    }
</style>
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
            this.personalData = <?php echo json_encode($personalData); ?>;
            this.config = <?php echo json_encode($config); ?>;
            this.updateRoute = <?php echo json_encode(route('personalData_updatePersonalData')); ?>;
            this.lSex = <?php echo json_encode($lSex); ?>;
            this.lBlood = <?php echo json_encode($lBlood); ?>;
            this.lCivil = <?php echo json_encode($lCivil); ?>;
            this.lSchooling = <?php echo json_encode($lSchooling); ?>;
            this.lState = <?php echo json_encode($lStates); ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:datapersonal" ); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content')
<div id="personalDataApp">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h3>
                <b>Datos personales @{{personalData.name}}</b>
                @include('layouts.manual_button')
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <form action="#">
                        <div class="row">
                            <h4>Datos del colaborador</h4>
                        </div>
                        <div class="row">
                            <div class="col-md-2 label-container">
                                <label for="lastName" style="display: block; margin-top: 10px; margin-bottom: 0px;">Apellido paterno</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="lastName" v-model="lastName" class="form-control" disabled>
                            </div>
                            <div class="col-md-2 label-container">
                                <label for="secondLastName">Apellido materno</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="secondLastName" v-model="secondLastName" class="form-control" disabled>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 label-container">
                                <label for="names" style="display: block; margin-top: 10px; margin-bottom: 0px;">Nombre(s)</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="names" v-model="names" class="form-control" disabled>
                            </div>
                            <div class="col-md-2 label-container">
                                <label for="rfc" style="display: block; margin-top: 10px; margin-bottom: 0px;">RFC</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="rfc" v-model="rfc" class="form-control" disabled>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 label-container">
                                <label for="personalMail">Correo e personal</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="personalMail" v-model="personalMail" class="form-control">
                            </div>
                            <div class="col-md-2 label-container">
                                <label for="companyMail">Correo e empresa</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="companyMail" v-model="companyMail" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 label-container">
                                <label for="selSex">Sexo</label>
                            </div>
                            <div class="col-md-4">
                                <select class="select2-class form-control" name="selSex" id="selSex"></select>
                            </div>
                            <div class="col-md-2 label-container">
                                <label for="selBlood">Tipo sangre</label>
                            </div>
                            <div class="col-md-4">
                                <select class="select2-class form-control" name="selBlood" id="selBlood"></select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 label-container">
                                <label for="selCivl">Estado civil</label>
                            </div>
                            <div class="col-md-4">
                                <select class="select2-class form-control" name="selCivl" id="selCivl"></select>
                            </div>
                            <div class="col-md-2 label-container">
                                <label for="selSchooling">Escolaridad</label>
                            </div>
                            <div class="col-md-4">
                                <select class="select2-class form-control" name="selSchooling" id="selSchooling"></select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 label-container">
                                <label for="personalPhone">Teléfono personal</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="personalPhone" v-model="personalPhone" class="form-control">
                            </div>
                            <div class="col-md-2 label-container">
                                <label for="companyPhone">Teléfono empresa</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="companyPhone" v-model="companyPhone" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 label-container">
                                <label for="emergencyPhone">Teléfono emergencia</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="emergencyPhone" v-model="emergencyPhone" class="form-control">
                            </div>
                            <div class="col-md-2 label-container">
                                <label for="emergencyContac">Contacto emergencia</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="emergencyContac" v-model="emergencyContac" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 label-container">
                                <label for="beneficiary">Beneficiario</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="beneficiary" v-model="beneficiary" class="form-control">
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <h4>Datos del domicilio del colaborador</h4>
                        </div>
                        <div class="row">
                            <div class="col-md-2 label-container">
                                <label for="selState">Estado</label>
                            </div>
                            <div class="col-md-4">
                                <select class="select2-class form-control" name="selState" id="selState"></select>
                            </div>
                            <div class="col-md-2 label-container">
                                <label for="postalCode">CP</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="postalCode" v-model="postalCode" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 label-container">
                                <label for="municipality">Municipio</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="municipality"  v-model="municipality" class="form-control">
                            </div>
                            <div class="col-md-2 label-container">
                                <label for="locality">Localidad</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="locality" v-model="locality" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 label-container">
                                <label for="colony">Colonia</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="colony" v-model="colony" class="form-control">
                            </div>
                            <div class="col-md-2 label-container">
                                <label for="street">Calle</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="street" v-model="street" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 label-container">
                                <label for="outsideNumber">Número exterior</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="outsideNumber" v-model="outsideNumber" class="form-control">
                            </div>
                            <div class="col-md-2 label-container">
                                <label for="insideNumber">Número interior</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="insideNumber" v-model="insideNumber" class="form-control">
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <h4>Datos del cónyuge del colaborador</h4>
                        </div>
                        <div class="row">
                            <div class="col-md-2 label-container">
                                <label for="spouse">Cónyuge</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="spouse" v-model="spouse" class="form-control">
                            </div>
                            <div class="col-md-2 label-container">
                                <label for="birthdaySpouce">Nacimiento</label>
                            </div>
                            <div class="col-md-4">
                                <input type="date" name="birthdaySpouce" v-model="birthdaySpouce" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 label-container">
                                <label for="selSexSpouce">Sexo</label>
                            </div>
                            <div class="col-md-4">
                                <select class="select2-class form-control" name="selSexSpouce" id="selSexSpouce"></select>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <h4 style="display: inline-block;">Datos de hijos del colaborador</h4>
                            <button id="btn_crear" type="button" class="btnRound btn-success" 
                                style="display: inline-block; margin-left: 10px" title="Crear solicitud" v-on:click="addChild();">
                                <span class="bx bx-plus"></span>
                            </button>
                        </div>
                        <br>
                        <div id="contenedor_hijos">

                        </div>

                        <br>
                        <button type="button" class="btn btn-primary" v-on:click="update()" style="float: right;">Actualizar datos</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@include('layouts.manual_jsControll')
<script>
    var self;
    $(document).ready(function () {

    });
</script>
<script type="text/javascript" src="{{ asset('myApp/personalData/vue_personalData.js') }}"></script>
@endsection