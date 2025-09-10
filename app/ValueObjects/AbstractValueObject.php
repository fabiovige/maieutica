<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Exceptions\ValueObjects\ValidationException;

abstract class AbstractValueObject
{
    protected array $validationErrors = [];

    abstract public static function fromArray(array $data): self;
    abstract public function toArray(): array;

    protected function validateEmail(string $email, string $fieldName = 'email'): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addValidationError("{$fieldName} inválido");
        }
    }

    protected function validateRequired(mixed $value, string $fieldName): void
    {
        if (empty(trim((string) $value))) {
            $this->addValidationError("{$fieldName} é obrigatório");
        }
    }

    protected function validateMinLength(string $value, int $minLength, string $fieldName): void
    {
        if (strlen(trim($value)) < $minLength) {
            $this->addValidationError("{$fieldName} deve ter pelo menos {$minLength} caracteres");
        }
    }

    protected function validateMaxLength(string $value, int $maxLength, string $fieldName): void
    {
        if (strlen(trim($value)) > $maxLength) {
            $this->addValidationError("{$fieldName} deve ter no máximo {$maxLength} caracteres");
        }
    }

    protected function validateInArray(mixed $value, array $allowedValues, string $fieldName): void
    {
        if (!in_array($value, $allowedValues, true)) {
            $this->addValidationError("{$fieldName} deve ser um dos valores: " . implode(', ', $allowedValues));
        }
    }

    protected function validatePositiveInteger(int $value, string $fieldName): void
    {
        if ($value <= 0) {
            $this->addValidationError("{$fieldName} deve ser um número positivo");
        }
    }

    protected function addValidationError(string $error): void
    {
        $this->validationErrors[] = $error;
    }

    protected function throwIfHasErrors(): void
    {
        if (!empty($this->validationErrors)) {
            throw ValidationException::withErrors($this->validationErrors);
        }
    }

    protected function clearValidationErrors(): void
    {
        $this->validationErrors = [];
    }
}
