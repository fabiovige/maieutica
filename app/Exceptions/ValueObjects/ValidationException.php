<?php

declare(strict_types=1);

namespace App\Exceptions\ValueObjects;

use InvalidArgumentException;

class ValidationException extends InvalidArgumentException
{
    private array $errors;

    public function __construct(string $message, array $errors = [])
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public static function withErrors(array $errors): self
    {
        $message = count($errors) === 1
            ? $errors[0]
            : 'Múltiplos erros de validação encontrados';

        return new self($message, $errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}
