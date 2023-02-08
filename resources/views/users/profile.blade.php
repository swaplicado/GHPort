@extends('layouts.principal')

@section('headStyles')
@endsection

@section('headJs')
    <script>
        function GlobalData() {
            this.user = <?php echo json_encode($user); ?>;
            this.user_update = <?php echo json_encode(route('profile_update')); ?>;
        }
        var oServerData = new GlobalData();
    </script>
@endsection

@section('content')
    <div class="row justify-content-md-center">
        <div class="col-md-6">
            <div class="card shadow" id="profile">
                <div class="card-header">
                    <h3>
                        <b>MI PERFIL</b>
                        <a href="http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:miperfil" target="_blank">
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
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ asset('myApp/users/profile.js') }}"></script>
@endsection
