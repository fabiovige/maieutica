<?php

namespace App\Services\Log;

use Illuminate\Support\Str;

class DataSanitizer
{
    private const SENSITIVE_FIELDS = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'token',
        'api_token',
        'access_token',
        'refresh_token',
        'remember_token',
        'secret',
        'key',
        'private_key',
        'public_key',
        'card_number',
        'cvv',
        'ssn',
        'social_security_number',
        'credit_card',
    ];

    private const PERSONAL_DATA_FIELDS = [
        'email',
        'phone',
        'cpf',
        'rg',
        'birth_date',
        'address',
        'zip_code',
        'cep',
    ];

    private const ANONYMIZATION_FIELDS = [
        'name',
        'first_name',
        'last_name',
        'full_name',
    ];

    public static function sanitize(array $data, bool $anonymizePersonalData = false): array
    {
        return self::recursiveSanitize($data, $anonymizePersonalData);
    }

    private static function recursiveSanitize(array $data, bool $anonymizePersonalData): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::recursiveSanitize($value, $anonymizePersonalData);
                continue;
            }

            if (is_object($value)) {
                $sanitized[$key] = self::sanitizeObject($value, $anonymizePersonalData);
                continue;
            }

            $sanitized[$key] = self::sanitizeValue($key, $value, $anonymizePersonalData);
        }

        return $sanitized;
    }

    private static function sanitizeObject(object $object, bool $anonymizePersonalData): array
    {
        if (method_exists($object, 'toArray')) {
            return self::sanitize($object->toArray(), $anonymizePersonalData);
        }

        $array = json_decode(json_encode($object), true);

        return self::sanitize($array, $anonymizePersonalData);
    }

    private static function sanitizeValue(string $key, mixed $value, bool $anonymizePersonalData): mixed
    {
        $normalizedKey = strtolower($key);

        if (self::isSensitiveField($normalizedKey)) {
            return '[REDACTED]';
        }

        if ($anonymizePersonalData && self::isPersonalDataField($normalizedKey)) {
            return self::maskPersonalData($normalizedKey, $value);
        }

        if ($anonymizePersonalData && self::isAnonymizationField($normalizedKey)) {
            return self::anonymizeName($value);
        }

        return $value;
    }

    private static function isSensitiveField(string $key): bool
    {
        foreach (self::SENSITIVE_FIELDS as $sensitiveField) {
            if (Str::contains($key, $sensitiveField)) {
                return true;
            }
        }

        return false;
    }

    private static function isPersonalDataField(string $key): bool
    {
        foreach (self::PERSONAL_DATA_FIELDS as $personalField) {
            if (Str::contains($key, $personalField)) {
                return true;
            }
        }

        return false;
    }

    private static function isAnonymizationField(string $key): bool
    {
        foreach (self::ANONYMIZATION_FIELDS as $anonymizationField) {
            if (Str::contains($key, $anonymizationField)) {
                return true;
            }
        }

        return false;
    }

    private static function maskPersonalData(string $key, mixed $value): string
    {
        if (!is_string($value)) {
            return '[MASKED]';
        }

        return match (true) {
            Str::contains($key, 'email') => self::maskEmail($value),
            Str::contains($key, 'phone') => self::maskPhone($value),
            Str::contains($key, 'cpf') => self::maskCpf($value),
            Str::contains($key, 'rg') => self::maskRg($value),
            Str::contains($key, 'address') => self::maskAddress($value),
            Str::contains($key, 'cep') || Str::contains($key, 'zip') => self::maskZipCode($value),
            default => '[MASKED]'
        };
    }

    private static function maskEmail(string $email): string
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '[MASKED_EMAIL]';
        }

        $parts = explode('@', $email);
        $username = $parts[0];
        $domain = $parts[1];

        $maskedUsername = substr($username, 0, 2) . str_repeat('*', max(0, strlen($username) - 2));

        return $maskedUsername . '@' . $domain;
    }

    private static function maskPhone(string $phone): string
    {
        $cleaned = preg_replace('/\D/', '', $phone);

        if (strlen($cleaned) < 10) {
            return '[MASKED_PHONE]';
        }

        return substr($cleaned, 0, 2) . str_repeat('*', strlen($cleaned) - 4) . substr($cleaned, -2);
    }

    private static function maskCpf(string $cpf): string
    {
        $cleaned = preg_replace('/\D/', '', $cpf);

        if (strlen($cleaned) !== 11) {
            return '[MASKED_CPF]';
        }

        return substr($cleaned, 0, 3) . '.***.***-' . substr($cleaned, -2);
    }

    private static function maskRg(string $rg): string
    {
        $cleaned = preg_replace('/\D/', '', $rg);

        if (strlen($cleaned) < 7) {
            return '[MASKED_RG]';
        }

        return substr($cleaned, 0, 2) . str_repeat('*', strlen($cleaned) - 3) . substr($cleaned, -1);
    }

    private static function maskAddress(string $address): string
    {
        $words = explode(' ', $address);

        if (count($words) <= 2) {
            return '[MASKED_ADDRESS]';
        }

        return $words[0] . ' ' . str_repeat('*', 10) . ' ' . end($words);
    }

    private static function maskZipCode(string $zipCode): string
    {
        $cleaned = preg_replace('/\D/', '', $zipCode);

        if (strlen($cleaned) !== 8) {
            return '[MASKED_ZIP]';
        }

        return substr($cleaned, 0, 2) . '***-***';
    }

    private static function anonymizeName(mixed $value): string
    {
        if (!is_string($value)) {
            return '[ANONYMOUS]';
        }

        $words = explode(' ', $value);

        if (count($words) === 1) {
            return substr($value, 0, 1) . str_repeat('*', max(0, strlen($value) - 1));
        }

        $firstName = $words[0];
        $lastName = end($words);

        return substr($firstName, 0, 1) . '***' .
               (count($words) > 2 ? ' *** ' : ' ') .
               substr($lastName, 0, 1) . str_repeat('*', max(0, strlen($lastName) - 1));
    }

    public static function createLogContext(string $message, array $context = [], array $metadata = []): array
    {
        return [
            'message' => $message,
            'context' => self::sanitize($context),
            'metadata' => array_merge([
                'timestamp' => now()->toISOString(),
                'trace_id' => self::generateTraceId(),
                'user_id' => auth()->id(),
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'request_id' => request()?->header('X-Request-ID'),
            ], $metadata),
        ];
    }

    private static function generateTraceId(): string
    {
        return Str::uuid()->toString();
    }
}
