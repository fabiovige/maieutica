<?php

declare(strict_types=1);

namespace App\DTOs\Responses;

use App\Models\Kid;

class KidResponseDto extends AbstractResponseDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $birthDate,
        public string $gender,
        public ?string $ethnicity,
        public ?string $photo,
        public int $months,
        public array $age,
        public string $initials,
        public ?string $responsibleName,
        public ?string $responsibleEmail,
        public array $professionals,
        public ?string $createdAt,
        public ?string $updatedAt
    ) {
    }

    public static function fromModel(Kid $kid): self
    {
        $responsible = $kid->responsible;
        $professionals = $kid->professionals->map(function ($professional) {
            return [
                'id' => $professional->id,
                'name' => $professional->user->first()?->name ?? 'N/A',
                'specialty' => $professional->specialty?->name ?? 'N/A',
            ];
        })->toArray();

        return new self(
            id: $kid->id,
            name: $kid->name,
            birthDate: $kid->birth_date?->format('d/m/Y') ?? '',
            gender: $kid->gender,
            ethnicity: $kid->ethnicity,
            photo: $kid->photo,
            months: $kid->months ?? $kid->getMonthsAttribute(),
            age: [
                'years' => $kid->getAge()['years'] ?? 0,
                'months' => $kid->getAge()['months'] ?? 0,
                'formatted' => $kid->getAge()['formatted'] ?? '0 anos',
            ],
            initials: $kid->getInitialsAttribute(),
            responsibleName: $responsible?->name,
            responsibleEmail: $responsible?->email,
            professionals: $professionals,
            createdAt: $kid->created_at?->format('d/m/Y H:i'),
            updatedAt: $kid->updated_at?->format('d/m/Y H:i')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'birth_date' => $this->birthDate,
            'gender' => $this->gender,
            'gender_label' => Kid::GENDERS[$this->gender] ?? $this->gender,
            'ethnicity' => $this->ethnicity,
            'ethnicity_label' => $this->ethnicity ? (Kid::ETHNICITIES[$this->ethnicity] ?? $this->ethnicity) : null,
            'photo' => $this->photo,
            'photo_url' => $this->photo ? route('kids.photo.show', ['kid' => $this->id, 'filename' => $this->photo]) : null,
            'months' => $this->months,
            'age' => $this->age,
            'initials' => $this->initials,
            'responsible' => [
                'name' => $this->responsibleName,
                'email' => $this->responsibleEmail,
            ],
            'professionals' => $this->professionals,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public function toMinimalArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'age' => $this->age,
            'initials' => $this->initials,
            'photo_url' => $this->photo ? route('kids.photo.show', ['kid' => $this->id, 'filename' => $this->photo]) : null,
            'responsible_name' => $this->responsibleName,
        ];
    }

    public function toCardArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'birth_date' => $this->birthDate,
            'age' => $this->age,
            'gender_label' => Kid::GENDERS[$this->gender] ?? $this->gender,
            'ethnicity_label' => $this->ethnicity ? (Kid::ETHNICITIES[$this->ethnicity] ?? $this->ethnicity) : null,
            'initials' => $this->initials,
            'photo_url' => $this->photo ? route('kids.photo.show', ['kid' => $this->id, 'filename' => $this->photo]) : null,
            'responsible' => [
                'name' => $this->responsibleName,
                'email' => $this->responsibleEmail,
            ],
            'professionals_count' => count($this->professionals),
        ];
    }
}