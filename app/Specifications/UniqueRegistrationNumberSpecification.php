<?php

declare(strict_types=1);

namespace App\Specifications;

use App\Models\Professional;

class UniqueRegistrationNumberSpecification
{
    public function isSatisfiedBy(string $registrationNumber, ?int $excludeProfessionalId = null): bool
    {
        $query = Professional::where('registration_number', $registrationNumber);

        if ($excludeProfessionalId) {
            $query->where('id', '!=', $excludeProfessionalId);
        }

        return !$query->exists();
    }

    public function getErrorMessage(): string
    {
        return 'Número de registro já está sendo usado por outro profissional';
    }
}
