<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StrongPassword implements Rule
{
    private string $username;

    public function __construct(string $username = '')
    {
        $this->username = strtolower($username);
    }

    public function passes($attribute, $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return $this->hasMinimumLength($value)
            && $this->hasUppercase($value)
            && $this->hasLowercase($value)
            && $this->hasNumber($value)
            && $this->hasSpecialCharacter($value)
            && $this->doesNotContainUsername($value);
    }

    public function message(): string
    {
        return 'A :attribute não atende aos critérios de segurança necessários.';
    }

    public function getRequirements(): array
    {
        return [
            'min_length' => 'Mínimo 8 caracteres',
            'uppercase' => 'Pelo menos 1 letra maiúscula',
            'lowercase' => 'Pelo menos 1 letra minúscula',
            'number' => 'Pelo menos 1 número',
            'special' => 'Pelo menos 1 caractere especial (!@#$%^&*)',
            'no_username' => 'Não deve conter o nome do usuário'
        ];
    }

    public function validateRequirements(string $value): array
    {
        return [
            'min_length' => $this->hasMinimumLength($value),
            'uppercase' => $this->hasUppercase($value),
            'lowercase' => $this->hasLowercase($value),
            'number' => $this->hasNumber($value),
            'special' => $this->hasSpecialCharacter($value),
            'no_username' => $this->doesNotContainUsername($value)
        ];
    }

    private function hasMinimumLength(string $value): bool
    {
        return strlen($value) >= 8;
    }

    private function hasUppercase(string $value): bool
    {
        return preg_match('/[A-Z]/', $value) === 1;
    }

    private function hasLowercase(string $value): bool
    {
        return preg_match('/[a-z]/', $value) === 1;
    }

    private function hasNumber(string $value): bool
    {
        return preg_match('/[0-9]/', $value) === 1;
    }

    private function hasSpecialCharacter(string $value): bool
    {
        return preg_match('/[!@#$%^&*]/', $value) === 1;
    }

    private function doesNotContainUsername(string $value): bool
    {
        if (empty($this->username)) {
            return true;
        }

        return stripos(strtolower($value), $this->username) === false;
    }
}