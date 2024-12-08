<!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Styles -->
        <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    </head>
<body>

    <div class="container-login">
        <div class="img-box">
        </div>
        <div class="content-box">
            <div class="form-box">
                <div style="text-align: center; margin-bottom: 70px;">
                    <img src="{{ asset('images/logo_login.png') }} "
                            class="elevation-0"
                            alt="{{ config('app.name') }}"
                            width="160px"
                        />
                </div>
                <form method="POST" action="{{ route('autenticacao') }}">
                    @csrf
                    <div class="col-md-12 d-flex align-items-center justify-content-center">
                        @error('email')
                        <div class="alert alert-danger text-center text-danger" style="width:100%;">
                            Credenciais incorretas
                        </div>
                        @enderror
                    </div>

                    <div class="input-box">
                        <span>E-mail</span>
                        <input type="email" name="email">
                    </div>

                    <div class="input-box">
                        <span>Senha</span>
                        <input type="password" name="password">
                    </div>

                    <div class="remember">
                        <div>
                            <label>
                                <input type="checkbox" name="remember"> Lembrar acesso
                            </label>
                        </div>
                        @if (Route::has('password.request'))
                            <div>
                                <a href="{{ route('password.request') }}">
                                {{ __('Forgot Your Password?') }}
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="input-box">
                        <input type="submit" value="Entrar">
                    </div>
                </form>
                <hr>
                <div class="d-flex flex-column align-items-center">
                    <a href="{{ url('/auth/google/redirect') }}" class="btn btn-primary mb-3">
                        <i class="bi bi-google"></i> Entrar com o Google
                    </a>
                    <span class="small text-muted">
                        Todos os direitos reservados. {{ config('app.name') }} - {{ config('app.description') }}.
                    </span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
