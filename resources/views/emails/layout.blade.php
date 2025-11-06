<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h2 {
            color: #0d6efd;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
        }
        h3 {
            color: #495057;
            margin-top: 20px;
        }
        .info-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #0d6efd;
            margin: 15px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #0d6efd;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 15px 0;
        }
        .button:hover {
            background-color: #0b5ed7;
        }
        footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 0.9em;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        @yield('content')

        <footer>
            <p><strong>{{ $appName ?? config('app.name') }}</strong></p>
            @if(env('APP_DESCRIPTION'))
                <p>{{ env('APP_DESCRIPTION') }}</p>
            @endif
            @if(env('APP_VERSION'))
                <p>Versão do sistema: {{ env('APP_VERSION') }}</p>
            @endif
            <p>&copy; {{ date('Y') }} Todos os direitos reservados.</p>
        </footer>
    </div>
</body>
</html>
