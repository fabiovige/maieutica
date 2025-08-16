<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Kid;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface KidRepositoryInterface extends BaseRepositoryInterface
{
    public function getKidsForUser(): Collection;
    
    public function getKidsByRole(string $role, ?int $userId = null): Collection;
    
    public function findByResponsible(int $responsibleId): Collection;
    
    public function findByProfessional(int $professionalId): Collection;
    
    public function getKidsWithRelations(array $relations = ['professionals', 'responsible', 'checklists']): Collection;
    
    public function paginateForUser(int $perPage = 15): LengthAwarePaginator;
    
    public function getKidsForProfessional(int $professionalId): Collection;
    
    public function attachProfessional(int $kidId, int $professionalId, array $pivotData = []): bool;
    
    public function detachProfessional(int $kidId, int $professionalId): bool;
    
    public function syncProfessionals(int $kidId, array $professionalIds): bool;
}