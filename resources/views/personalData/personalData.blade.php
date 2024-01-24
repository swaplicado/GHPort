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
            margin-top: 0px !important;
            margin-bottom: 0px !important;
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
            margin-top: 0px !important;
            margin-bottom: 0px !important;
            font-weight: bold;
        }
    }

    .my-form-control {
        display: block;
        width: 100%;
        /* height: calc(1.5em + .75rem + 2px); */
        /* padding: .375rem .75rem; */
        /* font-size: 1rem; */
        font-weight: 400;
        /* line-height: 1.5; */
        color: #444;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #d1d3e2;
        border-radius: .35rem;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out
    }

    .my-form-control:disabled,
    .my-form-control[readonly] {
        background-color: #eaecf4;
        opacity: 1
    }

    .my-form-control::placeholder {
        color: #858796;
        opacity: 1
    }

    .my-form{

    }

    .my-form .row {
        padding-bottom: 5px;
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
            this.lParentesco = <?php echo json_encode($lParentesco); ?>;
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
                    <form action="#" class="my-form">
                        <div class="row">
                            <h4><b>Mis datos personales</b></h4>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="fullNmae">Nombre completo:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="fullName" v-model="fullName" class="my-form-control" disabled
                                            placeholder="Nombre completo">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="rfc">RFC:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="rfc" v-model="rfc" class="my-form-control" disabled
                                            placeholder="RFC">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="postalCode">CP domicilio fiscal:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="postalCode" v-model="postalCodeFiscal" class="my-form-control"
                                            placeholder="Codigo postal registrado ante el SAT" disabled>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="selSex">Sexo:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <select class="select2-class my-form-control" style="width: 100%;" name="selSex" id="selSex"></select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="selBlood">Tipo sangre:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <select class="select2-class my-form-control" style="width: 100%;" name="selBlood" id="selBlood"></select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="selSchooling">Escolaridad:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <select class="select2-class my-form-control" style="width: 100%;" name="selSchooling" id="selSchooling"></select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="selCivl">Estado civil:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <select class="select2-class my-form-control" style="width: 100%;" name="selCivl" id="selCivl"></select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="personalPhone">Teléfono personal:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="personalPhone" v-model="personalPhone" class="my-form-control"
                                            placeholder="Teléfono personal">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="companyPhone">Teléfono empresa:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="companyPhone" v-model="companyPhone" class="my-form-control"
                                            placeholder="Teléfono de la empresa">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="ext">Ext. conmutador:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="ext" v-model="ext" class="my-form-control"
                                            placeholder="Número de extensión de la empresa">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="personalMail">Correo-e personal:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="personalMail" v-model="personalMail" class="my-form-control"
                                            placeholder="Correo electronico personal">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="companyMail">Correo-e empresa:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="companyMail" v-model="companyMail" class="my-form-control"
                                            placeholder="correo electronico de la empresa">
                                    </div>
                                </div>
                            </div>
                        </div>

                       <br>
                        <div class="row">
                            <h4><b>Contacto de emergencia</b></h4>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-2 label-container">
                                        <label for="emergencyContac">Nombre</label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="emergencyContac" v-model="emergencyContac" class="my-form-control"
                                            placeholder="Nombre del contacto de emergencia">
                                    </div>
                                    <div class="col-md-2 label-container">
                                        <label for="emergencyContac">Teléfono contacto</label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="emergencyPhone" v-model="emergencyPhone" class="my-form-control"
                                            placeholder="Teléfono en caso de emergencia">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-2 label-container">
                                        <label for="SelEmergencyContac">Parentesco:</label>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="select2-class my-form-control" style="width: 100%;" name="SelEmergencyContac" id="SelEmergencyContac"></select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-2 label-container">
                                        <label for="beneficiary">Beneficiario(s):</label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="beneficiary" v-model="beneficiary" class="my-form-control"
                                            placeholder="Ej: 'Nombre de la persona beneficiaria' - 100%">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <h4><b>Domicilio actual</b></h4>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="selState">Estado:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <select class="select2-class my-form-control" style="width: 100%;" name="selState" id="selState"></select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="municipality">Municipio:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="municipality"  v-model="municipality" class="my-form-control"
                                            placeholder="Nombre del municipio">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="locality">Localidad:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="locality" v-model="locality" class="my-form-control"
                                            placeholder="Nombre de la localidad">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="postalCode">CP:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="postalCode" v-model="postalCode" class="my-form-control"
                                            placeholder="Codigo postal actual">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="colony">Colonia:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="colony" v-model="colony" class="my-form-control"
                                            placeholder="Nombre de la colonia">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="street">Calle:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="street" v-model="street" class="my-form-control"
                                            placeholder="Nombre de la calle">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="outsideNumber">Número exterior:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="outsideNumber" v-model="outsideNumber" class="my-form-control"
                                            placeholder="Número exterior">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="insideNumber">Número interior:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="insideNumber" v-model="insideNumber" class="my-form-control"
                                            placeholder="Número interior, solo en caso de ser necesario">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="reference">Referencia:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="reference" v-model="reference" class="my-form-control"
                                            placeholder="Ej: Entre calles 'Nombre de la calle 1' y 'Nombre de la calle 2'">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <br>
                        <div class="row">
                            <h4><b>Datos familiares</b></h4>
                        </div>
                        <div class="row">
                            <div class="col-md-2 label-container">
                                <label for="spouse">Cónyuge:</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="spouse" v-model="spouse" class="my-form-control"
                                    placeholder="Nombre completo del(la) cónyuge">
                            </div>
                            <div class="col-md-5">
                                <div class="row">
                                    <div class="col-md-2 label-container">
                                        <label for="birthdaySpouce">Nacimiento:</label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="date" name="birthdaySpouce" v-model="birthdaySpouce" class="my-form-control">
                                    </div>
                                    <div class="col-md-2 label-container">
                                        <label for="selSexSpouce">Sexo:</label>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="select2-class my-form-control" style="width: 100%;" name="selSexSpouce" id="selSexSpouce"></select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <h4 style="display: inline-block;"><b>Datos de mis hijos</b></h4>
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