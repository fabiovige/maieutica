<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => 'http://localhost:8585/auth/github/callback',
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/auth/google/callback',
    ],

    /*
     * Agenda do Google Calendar consumida pelo comando `agenda:sync`.
     * Credencial separada da chave 'google' acima (que é do Socialite/login):
     * aqui usamos uma Service Account com escopo SOMENTE LEITURA.
     */
    'google_calendar' => [
        'calendar_id' => env('GOOGLE_CALENDAR_ID'),
        'credentials_path' => env('GOOGLE_CALENDAR_CREDENTIALS'),
        'sync_window_days' => env('GOOGLE_CALENDAR_SYNC_DAYS', 60),
    ],

    // Webhook do workflow que notificara paciente e profissional via WhatsApp
    // quando a recepcao confirmar o agendamento no Maieutica.
    'n8n' => [
        'appointment_confirmed_webhook' => env('N8N_APPOINTMENT_CONFIRMED_WEBHOOK'),
        'webhook_token' => env('N8N_WEBHOOK_TOKEN'),
        'webhook_timeout' => env('N8N_WEBHOOK_TIMEOUT', 10),
        // Somente para homologacao: redireciona paciente e profissional para
        // um numero controlado. Deve permanecer vazio em producao.
        'notification_test_phone' => env('N8N_NOTIFICATION_TEST_PHONE'),
    ],

];
