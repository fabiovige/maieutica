<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Recuperar Senha</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #f8f9fa;
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
            max-width: 400px;
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .login-header {
            background: #d7e2ec;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        .login-body {
            padding: 30px;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            font-weight: 500;
            font-size: 1.1rem;
            margin-top: 1rem;
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
        }
        .form-floating input {
            padding-left: 45px !important;
        }
        .form-floating label {
            padding-left: 45px;
        }
        .invalid-feedback {
            display: block;
        }
        .alert {
            border-left: 4px solid;
        }
        .alert-success {
            border-left-color: #198754;
        }
        .alert-danger {
            border-left-color: #dc3545;
        }
        .back-to-login {
            text-align: center;
            margin-top: 1rem;
        }
        .back-to-login a {
            color: #6c757d;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .back-to-login a:hover {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="login-header">
                <h4 class="mb-0">
                    <i class="bi bi-shield-lock me-2"></i>
                    Recuperar Senha
                </h4>
            </div>
            <div class="login-body">
                @if (session('status'))
                    <div class="alert alert-success mb-4">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="form-floating">
                        <i class="bi bi-envelope"></i>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email') }}"
                               placeholder="nome@exemplo.com" required autofocus>
                        <label for="email">E-mail</label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-submit">
                        <i class="bi bi-send me-2"></i>
                        Enviar Link de Recuperação
                    </button>
                </form>

                <div class="back-to-login">
                    <a href="{{ route('login') }}">
                        <i class="bi bi-arrow-left"></i>
                        Voltar para o Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
