<?php

declare(strict_types=1);

namespace App\ValueObjects;

use InvalidArgumentException;

class ProfessionalUpdateData
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly int $specialtyId,
        public readonly string $registrationNumber,
        public readonly ?string $bio = null,
        public readonly ?bool $allow = null,
        public readonly ?int $currentProfessionalId = null
    ) {
        $this->validate();
    }

    public static function fromArray(array $data, ?int $currentProfessionalId = null): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            phone: $data['phone'],
            specialtyId: (int) $data['specialty_id'],
            registrationNumber: $data['registration_number'],
            bio: $data['bio'] ?? null,
            allow: $data['allow'] ?? null,
            currentProfessionalId: $currentProfessionalId
        );
    }

    public function toUserArray(): array
    {
        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ];

        if ($this->allow !== null) {
            $userData['allow'] = $this->allow;
        }

        return $userData;
    }

    public function toProfessionalArray(): array
    {
        $professionalData = [
            'specialty_id' => $this->specialtyId,
            'registration_number' => $this->registrationNumber,
        ];

        if ($this->bio !== null) {
            $professionalData['bio'] = $this->bio;
        }

        return $professionalData;
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
        // Verificar se email já existe (excluindo o profissional atual)
        $userQuery = \App\Models\User::where('email', $this->email);
        
        if ($this->currentProfessionalId) {
            $currentProfessional = \App\Models\Professional::find($this->currentProfessionalId);
            if ($currentProfessional && $currentProfessional->user->first()) {
                $userQuery->where('id', '!=', $currentProfessional->user->first()->id);
            }
        }
        
        if ($userQuery->exists()) {
            throw new InvalidArgumentException('Email já está sendo usado por outro usuário');
        }

        // Verificar se registration_number já existe (excluindo o profissional atual)
        $professionalQuery = \App\Models\Professional::where('registration_number', $this->registrationNumber);
        
        if ($this->currentProfessionalId) {
            $professionalQuery->where('id', '!=', $this->currentProfessionalId);
        }
        
        if ($professionalQuery->exists()) {
            throw new InvalidArgumentException('Número de registro já está sendo usado por outro profissional');
        }

        // Verificar se specialty_id existe
        $specialtyExists = \App\Models\Specialty::find($this->specialtyId);
        if (!$specialtyExists) {
            throw new InvalidArgumentException('Especialidade não encontrada');
        }
    }
}