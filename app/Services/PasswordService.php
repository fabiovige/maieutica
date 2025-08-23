<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

class PasswordService
{
    private const DEFAULT_LENGTH = 12;
    private const SPECIAL_CHARS = '!@#$%&*';

    public function generateTemporaryPassword(int $length = self::DEFAULT_LENGTH): string
    {
        return $this->generateSecurePassword($length);
    }

    public function generateSecurePassword(int $length = self::DEFAULT_LENGTH): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = self::SPECIAL_CHARS;

        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        $allChars = $uppercase . $lowercase . $numbers . $special;
        
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        return str_shuffle($password);
    }

    public function isTemporary(string $password): bool
    {
        return preg_match('/^[A-Za-z0-9!@#$%&*]{12}$/', $password) === 1;
    }

    public function validatePasswordStrength(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Senha deve ter pelo menos 8 caracteres';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Senha deve conter pelo menos uma letra maiúscula';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Senha deve conter pelo menos uma letra minúscula';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Senha deve conter pelo menos um número';
        }

        if (!preg_match('/[' . preg_quote(self::SPECIAL_CHARS, '/') . ']/', $password)) {
            $errors[] = 'Senha deve conter pelo menos um caractere especial (!@#$%&*)';
        }

        return $errors;
    }
}