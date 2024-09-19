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
        font-weight: 400;
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

    .select2-container--default.select2-container--disabled .select2-selection--single {
        background-color: #eaecf4; /* Color de fondo */
        color: #999999;            /* Color del texto */
    }

    textarea {
        resize: none; /* Deshabilita el redimensionamiento */
    }

</style>
@endsection

@section('headJs')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData(){
            this.curriculumOptions = <?php echo json_encode($curriculumOptions) ?>;
            this.maxWorkExperience = <?php echo json_encode($maxWorkExperience) ?>;
            this.maxEducation = <?php echo json_encode($maxEducation) ?>;
            this.maxSkills = <?php echo json_encode($maxSkills) ?>;
            this.maxLanguage = <?php echo json_encode($maxLanguage) ?>;
            this.maxAspect = <?php echo json_encode($maxAspect) ?>;
            this.minWorkExperience = <?php echo json_encode($minWorkExperience) ?>;
            this.minEducation = <?php echo json_encode($minEducation) ?>;
            this.minSkills = <?php echo json_encode($minSkills) ?>;
            this.minLanguage = <?php echo json_encode($minLanguage) ?>;
            this.minAspect = <?php echo json_encode($minAspect) ?>;
            this.enabledEdition = <?php echo json_encode($enabledEdition) ?>;
            this.saveCurriculumRoute = <?php echo json_encode($saveCurriculumRoute) ?>;
            this.o_curriculum = <?php echo json_encode($o_curriculum) ?>;
            this.full_name = <?php echo json_encode($full_name) ?>;
            this.birthday = <?php echo json_encode($birthday) ?>;
            this.manualRoute = [];
            this.manualRoute[0] = <?php echo json_encode( "http://192.168.1.251/dokuwiki/doku.php?id=wiki:curriculum" ); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content')
<div id="curriculumApp">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h3>
                <b>Curriculum vitae</b>
                @include('layouts.manual_button')
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <form action="#" class="my-form">

                        <div class="row">
                            <div class="col-md-12" style="text-align: center">
                                <h4><b>Datos personales</b></h4>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="fullNmae">Nombre completo:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="fullNmae"  class="my-form-control" disabled
                                            placeholder="" style="text-transform:uppercase;" v-model="full_name">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-4 label-container">
                                        <label for="">Fecha nacimiento:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name=""  class="my-form-control" disabled
                                            placeholder="" style="text-transform:uppercase;" v-model="birthday">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-4 label-container d-flex align-items-end">
                                        <label for="fullNmae">Objetivo profesional:</label>
                                    </div>
                                    <div class="col-md-8">
                                        <textarea name="" id="" style="width: 100%" rows="5" class="my-form-control"
                                            :disabled="!enabledEdition" v-model="professional_objective">
                                        </textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <br>
                        <div class="row">
                            <div class="col-md-12" style="text-align: left">
                                <h4>
                                    <b>Experiencia laboral</b>
                                </h4>
                                <button id="btn_crear" type="button" class="btn3d btn-success" v-show="enabledEdition"
                                    style="display: inline-block; margin-right: 20px" title="Añadir experiencia laboral"
                                    @click="addWorkExperience" :disabled="!enabledEdition">
                                        <span class="bx bx-plus"></span>
                                </button>
                                <button id="btn_reject" type="button" class="btn3d btn-danger" v-show="enabledEdition"
                                    style="display: inline-block; margin-right: 20px" title="Eliminar renglon"
                                    @click="lessWorkExperience" :disabled="!enabledEdition">
                                        <span class="bx bx-minus"></span>
                                </button>
                                <br>
                                <br>
                            </div>
                        </div>

                        <div v-if="arrayWorkExperience.length == 0">
                            Sin datos capturados
                        </div>
                        <div v-for="(oExperience, index) in arrayWorkExperience" 
                            style="border-bottom: 1px solid #7b7b7b7b; margin-bottom: 2rem;">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-1 label-container">
                                            <label for="fullNmae">Empresa:</label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name=""  class="my-form-control"
                                                placeholder="" style="text-transform:uppercase;"
                                                v-model="arrayWorkExperience[index].company"
                                                :disabled="!enabledEdition">
                                        </div>
                                        <div class="col-md-1 label-container">
                                            <label for="fullNmae">Periodo:</label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name=""  class="my-form-control"
                                                placeholder="" style="text-transform:uppercase;"
                                                v-model="arrayWorkExperience[index].period"
                                                :disabled="!enabledEdition">
                                        </div>
                                        <div class="col-md-1 label-container">
                                            <label for="fullNmae">Puesto:</label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name=""  class="my-form-control"
                                                placeholder="" style="text-transform:uppercase;"
                                                v-model="arrayWorkExperience[index].position"
                                                :disabled="!enabledEdition">
                                        </div>
                                    </div>
                                </div>
                            </div>
    
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-1 label-container d-flex align-items-end">
                                            <label for="fullNmae">Actividades:</label>
                                        </div>
                                        <div class="col-md-5">
                                            <textarea name="" id="" rows="5" style="width: 100%" class="my-form-control"
                                                v-model="arrayWorkExperience[index].activities"
                                                :disabled="!enabledEdition">
                                            </textarea>
                                        </div>
                                        <div class="col-md-1 label-container d-flex align-items-end">
                                            <label for="fullNmae">Logros:</label>
                                        </div>
                                        <div class="col-md-5">
                                            <textarea name="" id="" rows="5" style="width: 100%" class="my-form-control"
                                                v-model="arrayWorkExperience[index].achievements"
                                                :disabled="!enabledEdition">
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <br>
                        <div class="row">
                            <div class="col-md-12" style="text-align: left">
                                <h4>
                                    <b>Educación</b>
                                </h4>
                                <button id="btn_crear" type="button" class="btn3d btn-success" v-show="enabledEdition"
                                    style="display: inline-block; margin-right: 20px" title="Añadir experiencia laboral"
                                    @click="addEducation" :disabled="!enabledEdition">
                                        <span class="bx bx-plus"></span>
                                </button>
                                <button id="btn_reject" type="button" class="btn3d btn-danger" v-show="enabledEdition"
                                    style="display: inline-block; margin-right: 20px" title="Eliminar renglon"
                                    @click="lessEducation" :disabled="!enabledEdition">
                                        <span class="bx bx-minus"></span>
                                </button>
                                <br>
                                <br>
                            </div>
                        </div>

                        <div v-if="arrayEducation.length == 0">
                            Sin datos capturados
                        </div>
                        <div class="row" v-for="(oEducation, index) in arrayEducation" :key="index"
                            style="border-bottom: 1px solid #7b7b7b7b; margin-bottom: 2rem;">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-2 label-container">
                                        <label for="fullNmae">Nivel educativo:</label>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="select2-class my-form-control" id="educationLevel" name="educationLevel" 
                                            v-model="arrayEducation[index].level" style="width: 100%;"
                                            :disabled="!enabledEdition">
                                            <option v-for="educationLevel in curriculumOptions.educationLevel" :value="educationLevel">@{{educationLevel}}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 label-container">
                                        <label for="fullNmae">Institución:</label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name=""  class="my-form-control" placeholder="" 
                                            style="text-transform:uppercase;" v-model="arrayEducation[index].institution"
                                            :disabled="!enabledEdition">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-2 label-container">
                                        <label for="fullNmae">Período:</label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name=""  class="my-form-control" placeholder="" 
                                            style="text-transform:uppercase;" v-model="arrayEducation[index].period"
                                            :disabled="!enabledEdition">
                                    </div>

                                    <div class="col-md-2 label-container">
                                        <label for="fullNmae">Programa/curso:</label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name=""  class="my-form-control" placeholder="" 
                                            style="text-transform:uppercase;" v-model="arrayEducation[index].program"
                                            :disabled="!enabledEdition">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2 label-container">
                                        <label for="fullNmae">Documento obtenido:</label>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="select2-class my-form-control" id="documentObtained" name="documentObtained" 
                                            v-model="arrayEducation[index].document" style="width: 100%;"
                                            :disabled="!enabledEdition">
                                            <option v-for="documentObtained in curriculumOptions.documentObtained" :value="documentObtained">@{{documentObtained}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <br>
                        <div class="row">
                            <div class="col-md-12" style="text-align: left">
                                <h4>
                                    <b>Habilidades y aptitudes</b>
                                </h4>
                                <button id="btn_crear" type="button" class="btn3d btn-success" v-show="enabledEdition"
                                    style="display: inline-block; margin-right: 20px" title="Añadir experiencia laboral"
                                    @click="addSkill" :disabled="!enabledEdition">
                                        <span class="bx bx-plus"></span>
                                </button>
                                <button id="btn_reject" type="button" class="btn3d btn-danger" v-show="enabledEdition"
                                    style="display: inline-block; margin-right: 20px" title="Eliminar renglon"
                                    @click="lessSkill" :disabled="!enabledEdition">
                                        <span class="bx bx-minus"></span>
                                </button>
                                <br>
                                <br>
                            </div>
                        </div>

                        <div v-if="arraySkills.length == 0">
                            Sin datos capturados
                        </div>
                        <div class="row" v-for="(oSkill, index) in arraySkills">
                            <div class="col-md-2 label-container">
                                <label for="fullNmae">Habilidad/Aptitud:</label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name=""  class="my-form-control" placeholder="" 
                                    style="text-transform:uppercase;" v-model="arraySkills[index].skill"
                                    :disabled="!enabledEdition">
                            </div>
                        </div>

                        <br>
                        <div class="row">
                            <div class="col-md-12" style="text-align: left">
                                <h4>
                                    <b>Idiomas</b>
                                </h4>
                                <button id="btn_crear" type="button" class="btn3d btn-success" v-show="enabledEdition"
                                    style="display: inline-block; margin-right: 20px" title="Añadir experiencia laboral"
                                    @click="addLanguage" :disabled="!enabledEdition">
                                        <span class="bx bx-plus"></span>
                                </button>
                                <button id="btn_reject" type="button" class="btn3d btn-danger" v-show="enabledEdition"
                                    style="display: inline-block; margin-right: 20px" title="Eliminar renglon"
                                    @click="lessLanguage" :disabled="!enabledEdition">
                                        <span class="bx bx-minus"></span>
                                </button>
                                <br>
                                <br>
                            </div>
                        </div>

                        <div v-if="arrayLanguage.length == 0">
                            Sin datos capturados
                        </div>
                        <div class="row" v-for="(oLanguage, index) in arrayLanguage">
                            <div class="col-md-1 label-container">
                                <label for="">Idioma:</label>
                            </div>
                            <div class="col-md-2">
                                <select class="select2-class my-form-control" id="language" name="language" 
                                    v-model="arrayLanguage[index].language" style="width: 100%;"
                                    :disabled="!enabledEdition">
                                    <option v-for="language in curriculumOptions.language" :value="language">@{{language}}</option>
                                </select>
                            </div>
                            <div class="col-md-1 label-container">
                                <label for="">Nivel:</label>
                            </div>
                            <div class="col-md-2">
                                <select class="select2-class my-form-control" id="languageLevel" name="languageLevel" 
                                    v-model="arrayLanguage[index].level" style="width: 100%;"
                                    :disabled="!enabledEdition">
                                    <option v-for="languageLevel in curriculumOptions.languageLevel" :value="languageLevel">@{{languageLevel}}</option>
                                </select>
                            </div>
                        </div>

                        <br>
                        <div class="row">
                            <div class="col-md-12" style="text-align: left">
                                <h4>
                                    <b>Aspectos adicionales</b>
                                </h4>
                                <button id="btn_crear" type="button" class="btn3d btn-success" v-show="enabledEdition"
                                    style="display: inline-block; margin-right: 20px" title="Añadir experiencia laboral"
                                    @click="addAspect" :disabled="!enabledEdition">
                                        <span class="bx bx-plus"></span>
                                </button>
                                <button id="btn_reject" type="button" class="btn3d btn-danger" v-show="enabledEdition"
                                    style="display: inline-block; margin-right: 20px" title="Eliminar renglon"
                                    @click="lessAspect" :disabled="!enabledEdition">
                                        <span class="bx bx-minus"></span>
                                </button>
                                <br>
                                <br>
                            </div>
                        </div>

                        <div v-if="arrayAspect.length == 0">
                            Sin datos capturados
                        </div>
                        <div class="row" v-for="(oAspect, index) in arrayAspect">
                            <div class="col-md-2 label-container d-flex align-items-end">
                                <label for="">Tipo:</label>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <select class="select2-class my-form-control" id="aspectType" name="aspectType" 
                                    v-model="arrayAspect[index].type" style="width: 100%;"
                                    :disabled="!enabledEdition">
                                    <option v-for="aspectType in curriculumOptions.aspectType" :value="aspectType">@{{aspectType}}</option>
                                </select>
                            </div>
                            <div class="col-md-2 label-container d-flex align-items-end">
                                <label for="">Descripción:</label>
                            </div>
                            <div class="col-md-4">
                                <textarea name="" id="" rows="5" style="width: 100%;" class="my-form-control" 
                                    v-model="arrayAspect[index].description"
                                    :disabled="!enabledEdition">
                                </textarea>
                            </div>
                        </div>

                        <br>
                        <br>
                        <button type="button" class="btn btn-primary" v-on:click="saveCurriculum" 
                            style="float: right;" v-show="enabledEdition">Actualizar cv</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    moment.locale('es');
</script>
@include('layouts.manual_jsControll')
<script type="text/javascript" src="{{ asset('myApp/curriculum/vue_curriculum.js') }}"></script>
@endsection