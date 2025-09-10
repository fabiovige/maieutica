<?php

declare(strict_types=1);

namespace App\ValueObjects\Address;

use App\ValueObjects\AbstractValueObject;

class AddressData extends AbstractValueObject
{
    public function __construct(
        public readonly ?string $postalCode = null,
        public readonly ?string $street = null,
        public readonly ?string $number = null,
        public readonly ?string $complement = null,
        public readonly ?string $neighborhood = null,
        public readonly ?string $city = null,
        public readonly ?string $state = null
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            postalCode: $data['cep'] ?? $data['postal_code'] ?? null,
            street: $data['logradouro'] ?? $data['street'] ?? null,
            number: $data['numero'] ?? $data['number'] ?? null,
            complement: $data['complemento'] ?? $data['complement'] ?? null,
            neighborhood: $data['bairro'] ?? $data['neighborhood'] ?? null,
            city: $data['cidade'] ?? $data['city'] ?? null,
            state: $data['estado'] ?? $data['state'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'cep' => $this->postalCode,
            'logradouro' => $this->street,
            'numero' => $this->number,
            'complemento' => $this->complement,
            'bairro' => $this->neighborhood,
            'cidade' => $this->city,
            'estado' => $this->state,
        ];
    }

    public function toUserArray(): array
    {
        return [
            'postal_code' => $this->postalCode,
            'street' => $this->street,
            'number' => $this->number,
            'complement' => $this->complement,
            'neighborhood' => $this->neighborhood,
            'city' => $this->city,
            'state' => $this->state,
        ];
    }

    public function isEmpty(): bool
    {
        return empty($this->postalCode) &&
               empty($this->street) &&
               empty($this->number) &&
               empty($this->neighborhood) &&
               empty($this->city) &&
               empty($this->state);
    }

    public function getFormattedAddress(): string
    {
        $parts = array_filter([
            $this->street,
            $this->number,
            $this->complement,
            $this->neighborhood,
            $this->city,
            $this->state,
        ]);

        return implode(', ', $parts);
    }

    private function validate(): void
    {
        $this->clearValidationErrors();

        if ($this->postalCode && !$this->isValidPostalCode($this->postalCode)) {
            $this->addValidationError('CEP deve ter o formato 00000-000');
        }

        if ($this->state && strlen($this->state) !== 2) {
            $this->addValidationError('Estado deve ter 2 caracteres (UF)');
        }

        $this->throwIfHasErrors();
    }

    private function isValidPostalCode(string $postalCode): bool
    {
        $cleanCep = preg_replace('/\D/', '', $postalCode);

        return strlen($cleanCep) === 8;
    }
}
