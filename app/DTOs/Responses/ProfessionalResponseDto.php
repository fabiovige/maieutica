<?php

declare(strict_types=1);

namespace App\DTOs\Responses;

use App\Models\Professional;

class ProfessionalResponseDto extends AbstractResponseDto
{
    public function __construct(
        public int $id,
        public string $registrationNumber,
        public ?string $bio,
        public string $specialtyName,
        public ?string $userName,
        public ?string $userEmail,
        public ?string $userPhone,
        public bool $userIsActive,
        public array $kids,
        public ?string $createdAt,
        public ?string $updatedAt
    ) {
    }

    public static function fromModel(Professional $professional): self
    {
        $user = $professional->user->first();
        $kids = $professional->kids->map(function ($kid) {
            return [
                'id' => $kid->id,
                'name' => $kid->name,
                'age' => $kid->getAge(),
                'initials' => $kid->getInitialsAttribute(),
            ];
        })->toArray();

        return new self(
            id: $professional->id,
            registrationNumber: $professional->registration_number,
            bio: $professional->bio,
            specialtyName: $professional->specialty?->name ?? 'N/A',
            userName: $user?->name,
            userEmail: $user?->email,
            userPhone: $user?->phone,
            userIsActive: $user?->isActive() ?? false,
            kids: $kids,
            createdAt: $professional->created_at?->format('d/m/Y H:i'),
            updatedAt: $professional->updated_at?->format('d/m/Y H:i')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'registration_number' => $this->registrationNumber,
            'bio' => $this->bio,
            'specialty' => [
                'name' => $this->specialtyName,
            ],
            'user' => [
                'name' => $this->userName,
                'email' => $this->userEmail,
                'phone' => $this->userPhone,
                'is_active' => $this->userIsActive,
            ],
            'kids' => $this->kids,
            'kids_count' => count($this->kids),
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public function toMinimalArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->userName,
            'email' => $this->userEmail,
            'registration_number' => $this->registrationNumber,
            'specialty' => $this->specialtyName,
            'is_active' => $this->userIsActive,
            'kids_count' => count($this->kids),
        ];
    }

    public function toSelectArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->userName,
            'specialty' => $this->specialtyName,
            'registration_number' => $this->registrationNumber,
            'label' => "{$this->userName} - {$this->specialtyName} ({$this->registrationNumber})",
        ];
    }
}