<?php

declare(strict_types=1);

namespace App\ValueObjects;

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
            allow: $data['allow'] ?? true
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
        // Verificar se email já existe
        $existingUserEmail = \App\Models\User::where('email', $this->email)->first();
        if ($existingUserEmail) {
            throw new InvalidArgumentException('Email já está sendo usado por outro usuário');
        }

        // Verificar se registration_number já existe
        $existingProfessional = \App\Models\Professional::where('registration_number', $this->registrationNumber)->first();
        if ($existingProfessional) {
            throw new InvalidArgumentException('Número de registro já está sendo usado por outro profissional');
        }

        // Verificar se specialty_id existe
        $specialtyExists = \App\Models\Specialty::find($this->specialtyId);
        if (!$specialtyExists) {
            throw new InvalidArgumentException('Especialidade não encontrada');
        }
    }
}