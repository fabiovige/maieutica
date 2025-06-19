<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Login</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- reCAPTCHA Script -->
    {!! htmlScriptTagJsApi() !!}

    <style>
        body {
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            background: white;
        }

        .login-header {
            background: #d7e2ec;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }

        .login-header h4 {
            margin: 0;
            color: #333;
            font-weight: 600;
        }

        .login-body {
            padding: 30px;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            font-weight: 500;
            font-size: 1.1rem;
            margin-top: 1rem;
            border-radius: 5px;
        }

        .form-floating {
            margin-bottom: 1rem;
            position: relative;
        }

        .form-floating .bi {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: 15px;
            z-index: 4;
            color: #6c757d;
            font-size: 16px;
        }

        .form-floating input {
            padding-left: 45px !important;
            height: 58px;
            border-radius: 5px;
        }

        .form-floating label {
            padding-left: 45px;
            color: #6c757d;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        .invalid-feedback {
            display: block;
            font-size: 0.875rem;
        }

        .forgot-password {
            text-align: right;
            margin-bottom: 1rem;
        }

        .forgot-password a {
            color: #6c757d;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: color 0.2s;
        }

        .forgot-password a:hover {
            color: #0d6efd;
            text-decoration: none;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 1.5rem;
        }

        .form-check-input {
            margin-top: 0;
            cursor: pointer;
        }

        .form-check-label {
            cursor: pointer;
            user-select: none;
        }

        .alert-danger {
            border-left: 4px solid #dc3545;
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .alert-danger ul {
            padding-left: 1rem;
            margin-bottom: 0;
        }

        .alert-danger li {
            margin-bottom: 0.25rem;
        }

        /* Responsividade */
        @media (max-width: 480px) {
            .login-container {
                padding: 10px;
            }

            .login-body {
                padding: 20px;
            }

            .login-card {
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="login-header">
                <h4 class="mb-0">
                    <i class="bi bi-shield-lock me-2"></i>
                    {{ config('app.name') }}
                </h4>
            </div>
            <div class="login-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-floating">
                        <i class="bi bi-envelope"></i>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" value="{{ old('email') }}" placeholder="nome@exemplo.com" required
                            autofocus>
                        <label for="email">E-mail</label>
                    </div>

                    <div class="form-floating">
                        <i class="bi bi-key"></i>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                            id="password" name="password" placeholder="Senha" required>
                        <label for="password">Senha</label>
                    </div>

                    <div class="forgot-password">
                        <a href="{{ route('password.request') }}">
                            <i class="bi bi-question-circle"></i>
                            Esqueceu sua senha?
                        </a>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember"
                            {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            <i class="bi bi-clock-history me-1"></i>
                            Lembrar-me
                        </label>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div style="display: flex; justify-content: center;">
                                {!! htmlFormSnippet() !!}
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Entrar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
