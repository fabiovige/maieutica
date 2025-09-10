<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Specifications\UniqueEmailSpecification;
use App\ValueObjects\Address\AddressData;
use InvalidArgumentException;

class UserData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone = null,
        public bool $allow = true,
        public ?AddressData $address = null,
        private readonly ?int $currentUserId = null
    ) {
        $this->validateEmail();
        $this->validateName();
        $this->validatePhone();
    }

    public static function fromArray(array $data, ?int $currentUserId = null): self
    {
        return new self(
            name: trim($data['name']),
            email: trim(strtolower($data['email'])),
            phone: isset($data['phone']) ? trim($data['phone']) : null,
            allow: filter_var($data['allow'] ?? true, FILTER_VALIDATE_BOOLEAN),
            address: !empty($data['address']) ? AddressData::fromArray($data['address']) : null,
            currentUserId: $currentUserId
        );
    }

    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'allow' => $this->allow,
        ];

        if ($this->address && !$this->address->isEmpty()) {
            $data = array_merge($data, $this->address->toUserArray());
        }

        return $data;
    }

    private function validateEmail(): void
    {
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email inválido');
        }

        $emailSpec = new UniqueEmailSpecification();
        if (!$emailSpec->isSatisfiedBy($this->email, $this->currentUserId)) {
            throw new InvalidArgumentException($emailSpec->getErrorMessage());
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
        if ($this->phone !== null && empty(trim($this->phone))) {
            throw new InvalidArgumentException('Telefone não pode ser vazio quando fornecido');
        }
    }
}