<?php

declare(strict_types=1);

namespace App\Specifications;

use App\Models\Specialty;

class SpecialtyExistsSpecification
{
    public function isSatisfiedBy(int $specialtyId): bool
    {
        return Specialty::where('id', $specialtyId)->exists();
    }

    public function getErrorMessage(): string
    {
        return 'Especialidade não encontrada';
    }
}
