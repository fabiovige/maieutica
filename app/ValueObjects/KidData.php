<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Models\Kid;
use Carbon\Carbon;
use DateTime;

class KidData extends AbstractValueObject
{
    public function __construct(
        public readonly string $name,
        public readonly DateTime $birthDate,
        public readonly string $gender,
        public readonly ?string $ethnicity,
        public readonly int $responsibleId,
        public readonly ?string $photo = null
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: trim($data['name']),
            birthDate: is_string($data['birth_date'])
                ? Carbon::createFromFormat('Y-m-d', $data['birth_date'])->toDateTime()
                : $data['birth_date'],
            gender: $data['gender'],
            ethnicity: $data['ethnicity'] ?? null,
            responsibleId: (int) $data['responsible_id'],
            photo: $data['photo'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'birth_date' => $this->birthDate->format('Y-m-d'),
            'gender' => $this->gender,
            'ethnicity' => $this->ethnicity,
            'responsible_id' => $this->responsibleId,
            'photo' => $this->photo,
        ];
    }

    public function toCreateArray(): array
    {
        return array_merge($this->toArray(), [
            'months' => $this->calculateAgeInMonths(),
        ]);
    }

    public function calculateAgeInMonths(): int
    {
        $now = new DateTime();
        $diff = $now->diff($this->birthDate);

        return ($diff->y * 12) + $diff->m;
    }

    public function getAge(): array
    {
        $now = new DateTime();
        $diff = $now->diff($this->birthDate);

        return [
            'years' => $diff->y,
            'months' => $diff->m,
            'total_months' => ($diff->y * 12) + $diff->m,
        ];
    }

    private function validate(): void
    {
        $this->clearValidationErrors();

        $this->validateRequired($this->name, 'Nome');
        $this->validateMinLength($this->name, 2, 'Nome');
        $this->validateMaxLength($this->name, 255, 'Nome');

        $this->validateGender();
        $this->validateEthnicity();
        $this->validateBirthDate();
        $this->validateResponsibleId();

        $this->throwIfHasErrors();
    }

    private function validateGender(): void
    {
        $allowedGenders = array_keys(Kid::GENDERS);
        if (!in_array($this->gender, $allowedGenders, true)) {
            $this->addValidationError('Gênero deve ser: ' . implode(' ou ', $allowedGenders));
        }
    }

    private function validateEthnicity(): void
    {
        if ($this->ethnicity === null) {
            return;
        }

        $allowedEthnicities = array_keys(Kid::ETHNICITIES);
        if (!in_array($this->ethnicity, $allowedEthnicities, true)) {
            $this->addValidationError('Etnia inválida');
        }
    }

    private function validateBirthDate(): void
    {
        $now = new DateTime();

        if ($this->birthDate >= $now) {
            $this->addValidationError('Data de nascimento deve ser anterior à data atual');
        }

        $maxAge = new DateTime('-18 years');
        if ($this->birthDate < $maxAge) {
            $this->addValidationError('Criança deve ter menos de 18 anos');
        }
    }

    private function validateResponsibleId(): void
    {
        $this->validatePositiveInteger($this->responsibleId, 'ID do responsável');

        if (!\App\Models\User::where('id', $this->responsibleId)->exists()) {
            $this->addValidationError('Responsável não encontrado');
        }
    }
}
