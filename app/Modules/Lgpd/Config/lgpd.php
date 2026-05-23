<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Prazos Legais
    |--------------------------------------------------------------------------
    |
    | Configuração dos prazos legais para processamento de requisições de
    | direitos dos titulares, conforme Art. 18 §5º da LGPD.
    |
    */

    'deadlines' => [
        // Prazo em dias úteis para resposta a DataRequests (Art. 18 §5º LGPD)
        'data_request_business_days' => 15,

        // Dias úteis restantes para disparar alerta de prazo crítico
        'alert_threshold_business_days' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Mínimos Legais de Retenção (em dias)
    |--------------------------------------------------------------------------
    |
    | Períodos mínimos de retenção por categoria de dados, baseados em
    | obrigações legais brasileiras. Nenhuma política de retenção pode
    | definir período inferior a estes valores.
    |
    | - prontuarios: 7300 dias (20 anos — CFM Resolução 1.821/2007)
    | - consentimentos: 1825 dias (5 anos após término do tratamento)
    | - access_logs: 1825 dias (5 anos)
    | - dados_cadastrais: 1825 dias (5 anos — prazo prescricional geral)
    |
    */

    'retention_minimums' => [
        'prontuarios' => 7300,
        'consentimentos' => 1825,
        'access_logs' => 1825,
        'dados_cadastrais' => 1825,
    ],

    /*
    |--------------------------------------------------------------------------
    | Feriados Nacionais Brasileiros
    |--------------------------------------------------------------------------
    |
    | Lista de feriados nacionais fixos e móveis utilizados no cálculo de
    | dias úteis para prazos legais. Feriados móveis (Carnaval, Sexta-feira
    | Santa, Corpus Christi) devem ser atualizados anualmente.
    |
    | Formato: 'MM-DD' para feriados fixos, 'YYYY-MM-DD' para móveis.
    |
    */

    'holidays' => [
        // Feriados fixos (recorrentes todo ano)
        'fixed' => [
            '01-01', // Confraternização Universal
            '04-21', // Tiradentes
            '05-01', // Dia do Trabalho
            '09-07', // Independência do Brasil
            '10-12', // Nossa Senhora Aparecida
            '11-02', // Finados
            '11-15', // Proclamação da República
            '12-25', // Natal
        ],

        // Feriados móveis (devem ser atualizados anualmente)
        'mobile' => [
            // 2025
            '2025-03-03', // Carnaval
            '2025-03-04', // Carnaval
            '2025-04-18', // Sexta-feira Santa
            '2025-06-19', // Corpus Christi

            // 2026
            '2026-02-16', // Carnaval
            '2026-02-17', // Carnaval
            '2026-04-03', // Sexta-feira Santa
            '2026-06-04', // Corpus Christi

            // 2027
            '2027-02-08', // Carnaval
            '2027-02-09', // Carnaval
            '2027-03-26', // Sexta-feira Santa
            '2027-05-27', // Corpus Christi
        ],
    ],

];
