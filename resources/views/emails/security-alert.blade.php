<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta de Segurança - Maiêutica</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #dc3545;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 0 0 8px 8px;
        }
        .alert {
            background-color: white;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .alert.high {
            border-left-color: #dc3545;
        }
        .alert.medium {
            border-left-color: #ffc107;
        }
        .alert.low {
            border-left-color: #17a2b8;
        }
        .severity {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        .severity.high { color: #dc3545; }
        .severity.medium { color: #ffc107; }
        .severity.low { color: #17a2b8; }
        .timestamp {
            color: #6c757d;
            font-size: 14px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .footer {
            margin-top: 20px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 4px;
            font-size: 14px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚨 Alerta de Segurança</h1>
        <p>Sistema Maiêutica - Monitoramento de Segurança</p>
    </div>

    <div class="content">
        <p><strong>Foram detectados {{ $alert_count }} alerta(s) de segurança crítico(s) que requerem atenção imediata.</strong></p>

        @foreach($alerts as $alert)
        <div class="alert {{ $alert['severity'] }}">
            <div class="severity {{ $alert['severity'] }}">{{ strtoupper($alert['severity']) }}</div>
            <h3>{{ $alert['title'] }}</h3>
            <p>{{ $alert['message'] }}</p>

            @if(!empty($alert['data']))
                <details>
                    <summary style="cursor: pointer; font-weight: bold;">Detalhes Técnicos</summary>
                    <ul style="margin-top: 10px;">
                        @foreach($alert['data'] as $key => $value)
                            <li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</li>
                        @endforeach
                    </ul>
                </details>
            @endif
        </div>
        @endforeach

        <div class="timestamp">
            <strong>Timestamp:</strong> {{ $timestamp }}
        </div>
    </div>

    <div class="footer">
        <p><strong>Ações Recomendadas:</strong></p>
        <ul>
            <li>Acesse o dashboard administrativo para mais detalhes</li>
            <li>Verifique os logs de auditoria</li>
            <li>Considere implementar medidas de segurança adicionais se necessário</li>
        </ul>

        <p><strong>Sistema Maiêutica</strong><br>
        Monitoramento de Segurança Automático<br>
        <em>Este é um email automático - não responda.</em></p>
    </div>
</body>
</html>