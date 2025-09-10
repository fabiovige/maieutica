<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Specifications\UniqueEmailSpecification;
use App\Specifications\UniqueRegistrationNumberSpecification;
use App\Specifications\SpecialtyExistsSpecification;
use InvalidArgumentException;

class ProfessionalData
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly int $specialtyId,
        public readonly string $registrationNumber,
        public readonly ?string $bio = null,
        public readonly bool $allow = true
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            phone: $data['phone'],
            specialtyId: (int) $data['specialty_id'],
            registrationNumber: $data['registration_number'],
            bio: $data['bio'] ?? null,
            allow: isset($data['allow']) ? filter_var($data['allow'], FILTER_VALIDATE_BOOLEAN) : true
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'specialty_id' => $this->specialtyId,
            'registration_number' => $this->registrationNumber,
            'bio' => $this->bio,
            'allow' => $this->allow,
        ];
    }

    public function toUserArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'allow' => $this->allow,
        ];
    }

    public function toProfessionalArray(): array
    {
        return [
            'specialty_id' => $this->specialtyId,
            'registration_number' => $this->registrationNumber,
            'bio' => $this->bio,
        ];
    }

    private function validate(): void
    {
        if (empty($this->name)) {
            throw new InvalidArgumentException('Nome é obrigatório');
        }

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email inválido');
        }

        if (empty($this->phone)) {
            throw new InvalidArgumentException('Telefone é obrigatório');
        }

        if ($this->specialtyId <= 0) {
            throw new InvalidArgumentException('Especialidade inválida');
        }

        if (empty($this->registrationNumber)) {
            throw new InvalidArgumentException('Número de registro é obrigatório');
        }

        $this->validateUniqueFields();
    }

    private function validateUniqueFields(): void
    {
        $emailSpec = new UniqueEmailSpecification();
        if (!$emailSpec->isSatisfiedBy($this->email)) {
            throw new InvalidArgumentException($emailSpec->getErrorMessage());
        }

        $registrationSpec = new UniqueRegistrationNumberSpecification();
        if (!$registrationSpec->isSatisfiedBy($this->registrationNumber)) {
            throw new InvalidArgumentException($registrationSpec->getErrorMessage());
        }

        $specialtySpec = new SpecialtyExistsSpecification();
        if (!$specialtySpec->isSatisfiedBy($this->specialtyId)) {
            throw new InvalidArgumentException($specialtySpec->getErrorMessage());
        }
    }
}
