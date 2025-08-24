<?php

declare(strict_types=1);

namespace App\Services\UserCreationStrategies;

use App\Models\User;

interface UserCreationStrategyInterface
{
    public function createUser(User $user, array $data): void;
    
    public function updateUser(User $user, array $data): void;
    
    public function supports(string $roleName): bool;
}