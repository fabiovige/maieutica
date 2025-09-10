<?php

declare(strict_types=1);

namespace App\Specifications;

use App\Models\Responsible;

class UniqueResponsibleEmailSpecification
{
    public function isSatisfiedBy(string $email, ?int $excludeResponsibleId = null): bool
    {
        $query = Responsible::where('email', $email);

        if ($excludeResponsibleId) {
            $query->where('id', '!=', $excludeResponsibleId);
        }

        return !$query->exists();
    }

    public function getErrorMessage(): string
    {
        return 'Email já está sendo usado por outro responsável';
    }
}
