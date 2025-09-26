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
        :root {
            --color-primary: #AD6E9B;
            --color-primary-darker: #844773;
            --color-primary-darkest: #5B214C;
        }

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
        }

        .login-card {
            width: 100%;
            max-width: 380px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            background: white;
        }

        .login-header {
            background: #ffffff;
            padding: 30px 30px 10px 30px;
            border-bottom: 1px solid #dee2e6;
            text-align: center;
        }

        .login-header h4 {
            margin: 0;
            color: var(--color-primary-darkest);
            font-weight: 500;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }

        .login-body {
            padding: 30px;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            font-weight: 500;
            background-color: var(--color-primary);
            border-color: var(--color-primary);
            color: white;
            border-radius: 4px;
            margin-top: 1rem;
        }

        .btn-login:hover {
            background-color: var(--color-primary-darker);
            border-color: var(--color-primary-darker);
        }

        .form-floating {
            margin-bottom: 1rem;
            position: relative;
        }

        .form-floating input {
            height: 56px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        .form-floating label {
            color: #6c757d;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
            padding: 5px;
            font-size: 18px;
        }

        .password-toggle:hover {
            color: var(--color-primary);
        }

        .form-control:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 0.2rem rgba(173, 110, 155, 0.25);
        }

        .form-control:-webkit-autofill,
        .form-control:-webkit-autofill:hover,
        .form-control:-webkit-autofill:focus,
        .form-control:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px white inset !important;
            -webkit-text-fill-color: #495057 !important;
        }

        .forgot-password {
            text-align: right;
            margin-bottom: 1rem;
        }

        .forgot-password a {
            color: var(--color-primary);
            font-size: 0.9rem;
            text-decoration: none;
        }

        .forgot-password a:hover {
            color: var(--color-primary-darker);
        }

        .form-check {
            margin-bottom: 1.5rem;
        }

        .form-check-input:checked {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
        }

        .form-check-label {
            color: #495057;
        }

        .alert-danger {
            border: 1px solid #f5c6cb;
            background-color: #f8d7da;
            color: #721c24;
            border-radius: 4px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }

        .alert-danger ul {
            margin: 0;
            padding-left: 20px;
        }

        .alert-warning {
            border: 1px solid #ffeaa7;
            background-color: #fff9e6;
            color: #b7791f;
            border-radius: 4px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }

        .rate-limit-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 15px;
            font-size: 0.85rem;
        }

        .rate-limit-info .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .rate-limit-info .info-row:last-child {
            margin-bottom: 0;
        }

        .countdown-timer {
            font-weight: bold;
            color: var(--color-primary-darker);
        }

        .btn-login:disabled {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            cursor: not-allowed;
        }

        .recaptcha-container {
            margin: 1.5rem 0;
        }

        .g-recaptcha {
            width: 100% !important;
            height: auto !important;
        }

        .g-recaptcha > div {
            width: 100% !important;
            height: auto !important;
        }

        .g-recaptcha iframe {
            width: 100% !important;
            height: 78px !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .g-recaptcha div[style] {
            width: 100% !important;
            max-width: 100% !important;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 15px;
            }

            .login-body, .login-header {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="login-header">
                <h4 class="mb-0">
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

                @if (session('rate_limit_info'))
                    @php
                        $rateLimitInfo = session('rate_limit_info');
                        $remainingTime = $rateLimitInfo['remaining_time'] ?? 0;
                    @endphp

                    @if ($remainingTime > 0)
                        <div class="alert-warning">
                            <i class="bi bi-shield-exclamation me-2"></i>
                            <strong>Muitas tentativas de login</strong><br>
                            Aguarde <span class="countdown-timer" id="countdown">{{ ceil($remainingTime / 60) }}</span> minuto(s) antes de tentar novamente.
                        </div>

                        <div class="rate-limit-info">
                            <div class="info-row">
                                <span>Tentativas por IP:</span>
                                <span>{{ $rateLimitInfo['ip_attempts'] ?? 0 }}/5</span>
                            </div>
                            <div class="info-row">
                                <span>Tentativas por e-mail:</span>
                                <span>{{ $rateLimitInfo['email_attempts'] ?? 0 }}/3</span>
                            </div>
                        </div>
                    @endif
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-floating">
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" value="{{ old('email') }}" placeholder="nome@exemplo.com" required
                            autofocus>
                        <label for="email">E-mail</label>
                    </div>

                    <div class="form-floating">
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                            id="password" name="password" placeholder="Senha" required>
                        <label for="password">Senha</label>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>

                    <div class="forgot-password">
                        <a href="{{ route('password.request') }}">
                            Esqueceu sua senha?
                        </a>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember"
                            {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            Lembrar-me
                        </label>
                    </div>

                    <div class="recaptcha-container">
                        {!! htmlFormSnippet() !!}
                    </div>

                    <button type="submit" class="btn btn-primary btn-login" id="loginButton">
                        Entrar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'bi bi-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'bi bi-eye';
            }
        }

        // Rate limiting countdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const countdownElement = document.getElementById('countdown');
            const loginButton = document.getElementById('loginButton');

            if (countdownElement) {
                let remainingSeconds = {{ session('rate_limit_info.remaining_time', 0) }};

                if (remainingSeconds > 0) {
                    // Disable form initially
                    loginButton.disabled = true;

                    const timer = setInterval(function() {
                        remainingSeconds--;

                        if (remainingSeconds <= 0) {
                            clearInterval(timer);
                            // Re-enable form and refresh page
                            location.reload();
                        } else {
                            const minutes = Math.ceil(remainingSeconds / 60);
                            countdownElement.textContent = minutes;
                        }
                    }, 1000);
                }
            }

            // Disable form submission if rate limited
            const rateLimitInfo = {!! safe_js(session('rate_limit_info')) !!};
            if (rateLimitInfo && rateLimitInfo.remaining_time > 0) {
                const form = document.querySelector('form');
                const inputs = form.querySelectorAll('input, button');

                inputs.forEach(input => {
                    if (input.type !== 'hidden') {
                        input.disabled = true;
                    }
                });
            }
        });
    </script>
</body>

</html>
