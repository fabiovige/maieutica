<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function create(array $data): User;

    public function updateUser(int $id, array $data): User;

    public function deleteUser(User $user): bool;

    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function getAllForSelect(): array;

    public function getByRole(string $roleName): Collection;

    public function existsById(int $id): bool;

    public function hasRoles(User $user): bool;

    public function markAsDeleted(User $user, int $deletedBy): bool;
}
