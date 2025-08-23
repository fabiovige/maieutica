<?php

declare(strict_types=1);

namespace App\Services\UserCreationStrategies;

use App\Models\Professional;
use App\Models\User;

class ProfessionalUserCreationStrategy implements UserCreationStrategyInterface
{
    public function createUser(User $user, array $data): void
    {
        Professional::create([
            'specialty_id' => $data['specialty_id'] ?? 1,
            'registration_number' => $data['registration_number'] ?? 'Pendente',
            'created_by' => auth()->id(),
        ])->user()->attach($user->id);
    }

    public function supports(string $roleName): bool
    {
        return $roleName === 'professional';
    }
}