<?php

declare(strict_types=1);

namespace App\Specifications;

use App\Models\User;

class UniqueEmailSpecification
{
    public function isSatisfiedBy(string $email, ?int $excludeUserId = null): bool
    {
        $query = User::where('email', $email);

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        return !$query->exists();
    }

    public function getErrorMessage(): string
    {
        return 'Email já está sendo usado por outro usuário';
    }
}
