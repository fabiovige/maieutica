<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Acesso</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- reCAPTCHA Script -->
    {!! htmlScriptTagJsApi() !!}

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8eef5 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .login-header {
            padding: 48px 40px 32px;
            text-align: center;
            background: #ffffff;
        }

        .logo-container {
            margin-bottom: 24px;
        }

        .logo-container img {
            max-width: 180px;
            height: auto;
        }

        .login-header h1 {
            font-size: 24px;
            font-weight: 600;
            color: #1a2332;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .login-header p {
            font-size: 15px;
            color: #64748b;
            font-weight: 400;
            margin: 0;
        }

        .login-body {
            padding: 0 40px 48px;
        }

        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 24px;
            color: #991b1b;
            font-size: 14px;
        }

        .alert-danger ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .alert-danger li {
            padding: 4px 0;
            display: flex;
            align-items: center;
        }

        .alert-danger li:before {
            content: "•";
            margin-right: 8px;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #334155;
            margin-bottom: 8px;
            letter-spacing: -0.2px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            height: 48px;
            padding: 12px 16px 12px 46px;
            font-size: 15px;
            color: #1e293b;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            transition: all 0.2s ease;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus {
            outline: none;
            background: #ffffff;
            border-color: #4aa4ee;
            box-shadow: 0 0 0 4px rgba(74, 164, 238, 0.08);
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .form-control.is-invalid {
            border-color: #ef4444;
        }

        .form-extras {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            border: 1.5px solid #cbd5e1;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .form-check-input:checked {
            background-color: #4aa4ee;
            border-color: #4aa4ee;
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 4px rgba(74, 164, 238, 0.08);
        }

        .form-check-label {
            font-size: 14px;
            color: #475569;
            cursor: pointer;
            user-select: none;
            font-weight: 400;
        }

        .forgot-link {
            font-size: 14px;
            color: #4aa4ee;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .forgot-link:hover {
            color: #3286ca;
        }

        .btn-login {
            width: 100%;
            height: 48px;
            background: linear-gradient(135deg, #4aa4ee 0%, #3d9ce0 100%);
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            letter-spacing: 0.3px;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #3d9ce0 0%, #3286ca 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(74, 164, 238, 0.25);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .recaptcha-container {
            margin-bottom: 24px;
            display: flex;
            justify-content: center;
        }

        .recaptcha-container > div {
            transform: scale(0.95);
            transform-origin: center;
        }

        /* Responsividade */
        @media (max-width: 480px) {
            body {
                padding: 16px;
            }

            .login-header {
                padding: 36px 24px 24px;
            }

            .login-body {
                padding: 0 24px 36px;
            }

            .login-header h1 {
                font-size: 22px;
            }

            .login-header p {
                font-size: 14px;
            }

            .form-extras {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }

            .recaptcha-container > div {
                transform: scale(0.85);
            }
        }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-container">
                    <img src="{{ asset('images/logo_login_transparente.png') }}" alt="{{ config('app.name') }}">
                </div>
                <h1>Bem-vindo</h1>
                <p>Acesse sua conta para continuar</p>
            </div>

            <div class="login-body">
                @if ($errors->any())
                    <div class="alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email" class="form-label">E-mail</label>
                        <div class="input-wrapper">
                            <i class="bi bi-envelope input-icon"></i>
                            <input
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="seu@email.com"
                                required
                                autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Senha</label>
                        <div class="input-wrapper">
                            <i class="bi bi-lock input-icon"></i>
                            <input
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                placeholder="••••••••"
                                required>
                        </div>
                    </div>

                    <div class="form-extras">
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="remember"
                                id="remember"
                                {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Lembrar-me
                            </label>
                        </div>
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            Esqueceu a senha?
                        </a>
                    </div>

                    <div class="recaptcha-container">
                        {!! htmlFormSnippet() !!}
                    </div>

                    <button type="submit" class="btn-login">
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
