<?php

declare(strict_types=1);

namespace App\ValueObjects;

use InvalidArgumentException;

readonly class UserData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $phone,
        public bool $allow = true
    ) {
        $this->validateEmail();
        $this->validateName();
        $this->validatePhone();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: trim($data['name']),
            email: trim(strtolower($data['email'])),
            phone: trim($data['phone']),
            allow: $data['allow'] ?? true
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'allow' => $this->allow,
        ];
    }

    private function validateEmail(): void
    {
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email inválido');
        }
    }

    private function validateName(): void
    {
        if (empty(trim($this->name))) {
            throw new InvalidArgumentException('Nome é obrigatório');
        }

        if (strlen($this->name) < 2) {
            throw new InvalidArgumentException('Nome deve ter pelo menos 2 caracteres');
        }
    }

    private function validatePhone(): void
    {
        if (empty(trim($this->phone))) {
            throw new InvalidArgumentException('Telefone é obrigatório');
        }
    }
}