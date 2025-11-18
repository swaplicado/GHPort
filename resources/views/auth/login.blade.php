@extends('layouts.app')

@section('content')
<img src="{{ asset('img/aeth_logo.png') }}" alt="Logo SWAP"
     style="position: absolute; top: 60px; right: 20px; height: 50px; z-index: 1000;">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
            <div class="card-header text-white text-center" style="background-color: #4e73df; font-size: 18px;">
                Portal de Gestión Humana
            </div>

                <div class="card-body">
                    <p class="text-center mb-4" style="font-weight: 500;">Inicie su sesión</p>
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('message') }}
                        </div>
                    @endif
                    <form id="login_form" method="POST" action="{{ route('login', ['idRoute' => $idRoute, 'idApp' => $idApp]) }}">
                        @csrf

                        <div class="form-group row">
                            <label for="username" class="col-md-4 col-form-label text-md-right">Nombre de usuario:*</label>

                            <div class="col-md-6">
                                <input id="username" placeholder="nombre.apellido" type="username" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autocomplete="username" autofocus>

                                @error('username')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">Contraseña:*</label>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility()" tabindex="-1">
                                            <i id="toggle-password-icon" class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>


                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary" style="background-color: #4e73df;">
                                    Entrar
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        ¿Olvidó su contraseña?
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function disableSubmitButton(form) {
        form.addEventListener('submit', function() {
            // Disable the submit button to prevent multiple submissions
            var submitButton = form.querySelector('[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
            }
        });
    }

    $(document).ready(function () {
        var form = document.getElementById('login_form');
        disableSubmitButton(form);
    });

    function togglePasswordVisibility() {
        const input = document.getElementById('password');
        const icon = document.getElementById('toggle-password-icon');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
<footer style="text-align: center; padding: 20px;">
    <div>
        Creado por:<br>
        <img src="{{ asset('img/swap_logo.jpg') }}" alt="Logo SWAP" style="margin-top: 10px; width: 120px; height: auto;">
    </div>
</footer>
@endsection