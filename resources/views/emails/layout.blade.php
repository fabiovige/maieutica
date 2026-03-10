<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    <style>
        body {
            font-family: 'Segoe UI', Roboto, Arial, sans-serif;
            line-height: 1.6;
            color: #374151;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            -webkit-font-smoothing: antialiased;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            padding: 32px 16px;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
        }
        .email-header {
            background-color: #AD6E9B;
            padding: 24px 32px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #ffffff;
            letter-spacing: 0.02em;
        }
        .email-body {
            padding: 32px;
        }
        h2 {
            color: #1f2937;
            font-size: 20px;
            font-weight: 600;
            margin: 0 0 16px 0;
        }
        h3 {
            color: #4b5563;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin: 24px 0 12px 0;
            padding-bottom: 6px;
            border-bottom: 1px solid #e5e7eb;
        }
        p {
            margin: 0 0 12px 0;
            font-size: 15px;
            color: #4b5563;
        }
        .info-box {
            background-color: #f9fafb;
            padding: 16px 20px;
            border-left: 3px solid #AD6E9B;
            border-radius: 0 6px 6px 0;
            margin: 16px 0;
        }
        .info-box p {
            margin: 0;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 10px 24px;
            background-color: #AD6E9B;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            margin: 8px 0;
        }
        .notice {
            background-color: #fefce8;
            border-left: 3px solid #ca8a04;
            padding: 12px 16px;
            border-radius: 0 6px 6px 0;
            margin: 16px 0;
        }
        .notice p {
            margin: 0;
            font-size: 13px;
            color: #713f12;
        }
        .data-table {
            width: 100%;
            font-size: 14px;
            margin: 8px 0;
        }
        .data-table td {
            padding: 4px 0;
            vertical-align: top;
            color: #4b5563;
        }
        .data-table td:first-child {
            color: #6b7280;
            width: 120px;
            font-size: 13px;
        }
        code {
            background: #f3f4f6;
            padding: 4px 10px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #1f2937;
            font-weight: 600;
            letter-spacing: 0.05em;
        }
        .email-footer {
            padding: 20px 32px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }
        .email-footer p {
            font-size: 12px;
            color: #9ca3af;
            margin: 2px 0;
        }
        .signature {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #f3f4f6;
        }
        .signature p {
            font-size: 14px;
            color: #6b7280;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <h1>{{ $appName ?? config('app.name') }}</h1>
            </div>

            <div class="email-body">
                @yield('content')

                <div class="signature">
                    <p>Atenciosamente,<br><strong>Equipe {{ $appName ?? config('app.name') }}</strong></p>
                </div>
            </div>

            <div class="email-footer">
                @if(env('APP_DESCRIPTION'))
                    <p>{{ env('APP_DESCRIPTION') }}</p>
                @endif
                <p>&copy; {{ date('Y') }} {{ $appName ?? config('app.name') }}. Todos os direitos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>
