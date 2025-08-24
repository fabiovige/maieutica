<?php

declare(strict_types=1);

namespace App\Services\UserCreationStrategies;

use App\Models\User;

class DefaultUserCreationStrategy implements UserCreationStrategyInterface
{
    public function createUser(User $user, array $data): void
    {
        // Estratégia padrão - não faz nada adicional
        // Para roles como 'pais', 'admin', etc.
    }

    public function updateUser(User $user, array $data): void
    {
        // Estratégia padrão para update - não faz nada adicional
    }

    public function supports(string $roleName): bool
    {
        // Suporta todos os roles não especializados
        return !in_array($roleName, ['professional']);
    }
}