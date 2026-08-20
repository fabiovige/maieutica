<?php

namespace App\Services\Calendar;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Cliente REST da Google Calendar API v3 autenticado por Service Account.
 *
 * Nao usa o SDK oficial de proposito: o projeto ja tem Guzzle + facade Http,
 * e producao roda em Hostinger, onde adicionar dependencia pesada e sensivel.
 * A autenticacao e o fluxo JWT bearer (server-to-server), assinado com openssl.
 *
 * O escopo e SOMENTE LEITURA - este cliente nunca altera a agenda.
 */
class GoogleCalendarClient
{
    private const SCOPE = 'https://www.googleapis.com/auth/calendar.readonly';

    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const API_BASE = 'https://www.googleapis.com/calendar/v3';

    private const TOKEN_CACHE_KEY = 'google_calendar.access_token';

    /**
     * Lista os eventos do calendario configurado dentro da janela informada.
     *
     * `singleEvents` expande recorrencias em ocorrencias individuais e
     * `showDeleted` traz os cancelados - necessario para refletir cancelamento.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listEvents(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        $calendarId = config('services.google_calendar.calendar_id');

        if (blank($calendarId)) {
            throw new RuntimeException('GOOGLE_CALENDAR_ID nao configurado no .env.');
        }

        $events = [];
        $pageToken = null;

        do {
            $response = Http::withToken($this->accessToken())
                ->retry(3, 250, fn ($exception) => $this->isTransientFailure($exception), false)
                ->get(self::API_BASE.'/calendars/'.rawurlencode($calendarId).'/events', array_filter([
                    'timeMin' => $from->format(\DATE_RFC3339),
                    'timeMax' => $to->format(\DATE_RFC3339),
                    'singleEvents' => 'true',
                    'orderBy' => 'startTime',
                    'maxResults' => 250,
                    'showDeleted' => 'true',
                    'pageToken' => $pageToken,
                ]));

            if ($response->failed()) {
                throw new RuntimeException(
                    'Falha ao consultar a Google Calendar API: '.$response->status().' '.$response->body()
                );
            }

            $events = array_merge($events, $response->json('items', []));
            $pageToken = $response->json('nextPageToken');
        } while ($pageToken);

        return $events;
    }

    /**
     * Access token da Service Account, cacheado por pouco menos que a validade.
     */
    private function accessToken(): string
    {
        return Cache::remember(self::TOKEN_CACHE_KEY, now()->addMinutes(55), function () {
            $credentials = $this->credentials();

            $response = Http::asForm()
                ->retry(3, 250, fn ($exception) => $this->isTransientFailure($exception), false)
                ->post(self::TOKEN_URL, [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $this->buildSignedJwt($credentials),
                ]);

            if ($response->failed()) {
                throw new RuntimeException(
                    'Falha ao autenticar na Google API: '.$response->status().' '.$response->body()
                );
            }

            return $response->json('access_token');
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function credentials(): array
    {
        $path = config('services.google_calendar.credentials_path');

        if (blank($path)) {
            throw new RuntimeException('GOOGLE_CALENDAR_CREDENTIALS nao configurado no .env.');
        }

        if (! is_readable($path)) {
            throw new RuntimeException("Credencial da Service Account nao encontrada ou sem permissao de leitura: {$path}");
        }

        $credentials = json_decode((string) file_get_contents($path), true);

        if (! is_array($credentials) || blank($credentials['client_email'] ?? null) || blank($credentials['private_key'] ?? null)) {
            throw new RuntimeException("Credencial invalida em {$path}: esperado JSON com client_email e private_key.");
        }

        return $credentials;
    }

    /**
     * Monta e assina (RS256) o JWT trocado por um access token.
     *
     * @param  array<string, mixed>  $credentials
     */
    private function buildSignedJwt(array $credentials): string
    {
        $issuedAt = time();

        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claims = [
            'iss' => $credentials['client_email'],
            'scope' => self::SCOPE,
            'aud' => self::TOKEN_URL,
            'iat' => $issuedAt,
            'exp' => $issuedAt + 3600,
        ];

        $payload = $this->base64UrlEncode(json_encode($header))
            .'.'.$this->base64UrlEncode(json_encode($claims));

        $signature = '';

        if (! openssl_sign($payload, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Nao foi possivel assinar o JWT com a private_key da Service Account.');
        }

        return $payload.'.'.$this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function isTransientFailure($exception): bool
    {
        if ($exception instanceof ConnectionException) {
            return true;
        }

        return $exception instanceof RequestException
            && ($exception->response->serverError() || $exception->response->status() === 429);
    }
}
