@extends('layouts.principal')

@section('headStyles')
<link href={{asset('select2js/css/select2.min.css')}} rel="stylesheet" />
@endsection

@section('headJs')
<script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        function GlobalData() {
            this.user = <?php echo json_encode($user); ?>;
            this.levels = <?php echo json_encode($levels); ?>;
            this.myConf_level = <?php echo json_encode($myConf_level); ?>;
            this.user_update = <?php echo json_encode(route('profile_update')); ?>;
            this.reportChecked = <?php echo json_encode($reportChecked); ?>;
            this.reportAlways_send = <?php echo json_encode($reportAlways_send); ?>;
            this.updateReportRoute = <?php echo json_encode(route('report_user')); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content')
<div id="profile">
    <div class="row justify-content-md-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header">
                    <h3>
                        <b>Mi perfil</b>
                        <a href="http://192.168.1.251/dokuwiki/doku.php?id=wiki:miperfil" target="_blank">
                            <span class="bx bx-question-mark btn3d"
                                style="display: inline-block; margin-left: 10px; background-color: #e4e4e4"></span>
                        </a>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="col-md-8 offset-md-2">
                        <div>
                            <label for="username" class="form-label">Usuario:</label>
                            <input class="form-control" id="username" v-model="user.username" disabled>
                        </div>
                        <br>
                        <div>
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="email" v-model="user.email" disabled>
                        </div>
                        <br>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" v-model="changePass" id="changePass">
                            <label class="form-check-label" for="changePass">
                                Cambiar contraseña
                            </label>
                        </div>
                        <br>
                        <div>
                            <label for="password" class="form-label">Contraseña: 
                                <span style="float: right;" :class="[ showPassword ? 'bx bx-show bx-sm' : 'bx bx-hide bx-sm' ]" v-on:click="showPass()"></span>
                            </label>
                            <input :type="typeInputPass" class="form-control" id="password" v-model="password" required
                                :disabled="changePass == false">
                        </div>
                        <br>
                        <div>
                            <label for="confirm_password" class="form-label">Confirmar contraseña:</label>
                            <input :type="typeInputPass" class="form-control" id="confirm_password" v-model="confirm_password"
                                required :disabled="changePass == false">
                        </div>
                        <br>
                        <div>
                            <button type="button" class="btn btn-primary" style="float: right" v-on:click="updatePass()"
                                :disabled="changePass == false">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if (($user->rol_id == $constants['JEFE'] || $user->rol_id == $constants['GH'] || $user->rol_id == $constants['ADMIN']) && $report_enabled)
        <br>
        <div class="row justify-content-md-center">
            <div class="col-md-6">
                <div class="card shadow" id="profile">
                    <div class="card-header">
                        <h3>
                            <b>Reportario</b>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="reports" v-model="reportChecked" v-on:change="updateReports()"/>
                            <label class="form-check-label" for="reports">
                                Reporte de incidencias
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" v-model="always_send"
                                value="option1" v-on:change="updateReports()" :disabled="!reportChecked">
                            <label class="form-check-label" for="inlineRadio1">Enviar reporte solo si existen incidencias</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" v-model="always_send"
                                value="option2" v-on:change="updateReports()" :disabled="!reportChecked">
                            <label class="form-check-label" for="inlineRadio2">Enviar siempre el reporte</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label for="sel_levels" style="padding-right: 10px;">Nivel:</label>
                            <select class="select2-class form-control" name="sel_levels" id="sel_levels" style="width: 100%;" :disabled="!reportChecked"></select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ asset('myApp/users/profile.js') }}"></script>
@endsection
