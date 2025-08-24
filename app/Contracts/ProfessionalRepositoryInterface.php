<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProfessionalRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithFilters(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function findBySpecialty(int $specialtyId): Collection;

    public function findByRegistrationNumber(string $registrationNumber): ?object;

    public function getActiveProfessionals(): Collection;

    public function attachUser(int $professionalId, int $userId): bool;

    public function detachUser(int $professionalId, int $userId): bool;

    public function findByUser(int $userId): ?object;
}