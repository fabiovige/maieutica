<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\KidRepositoryInterface;
use App\Models\Kid;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class KidRepository extends BaseRepository implements KidRepositoryInterface
{
    public function __construct(Kid $model)
    {
        parent::__construct($model);
    }

    public function getKidsForUser(): Collection
    {
        $user = Auth::user();
        
        if (!$user) {
            return new Collection();
        }

        return $this->getKidsByRole($user->roles->first()?->name ?? '', $user->id);
    }

    public function getKidsByRole(string $role, ?int $userId = null): Collection
    {
        $query = $this->model->newQuery();

        return match ($role) {
            'superadmin', 'admin' => $query->with(['professionals', 'responsible', 'checklists'])->get(),
            'professional' => $this->getKidsForProfessionalRole($userId, $query),
            'pais' => $this->getKidsForParentRole($userId, $query),
            default => new Collection(),
        };
    }

    public function findByResponsible(int $responsibleId): Collection
    {
        return $this->model->where('responsible_id', $responsibleId)
            ->with(['professionals', 'responsible', 'checklists'])
            ->get();
    }

    public function findByProfessional(int $professionalId): Collection
    {
        return $this->model->whereHas('professionals', function ($query) use ($professionalId) {
            $query->where('professional_id', $professionalId);
        })
        ->with(['professionals', 'responsible', 'checklists'])
        ->get();
    }

    public function getKidsWithRelations(array $relations = ['professionals', 'responsible', 'checklists']): Collection
    {
        return $this->model->with($relations)->get();
    }

    public function paginateForUser(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $user = Auth::user();
        
        if (!$user) {
            return $this->model->newQuery()->paginate($perPage);
        }

        $query = $this->model->newQuery()->with(['responsible', 'professionals']);

        // Aplicar filtros de role
        if ($user->hasRole('pais')) {
            $query->where('responsible_id', $user->id);
        } elseif ($user->hasRole('professional')) {
            $professionalId = $user->professional->first()?->id;
            if ($professionalId) {
                $query->whereHas('professionals', function ($q) use ($professionalId) {
                    $q->where('professional_id', $professionalId);
                });
            }
        }

        // Aplicar filtro de busca
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('responsible', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Aplicar ordenação
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDirection = $filters['sort_direction'] ?? 'asc';

        switch ($sortBy) {
            case 'responsible':
                $query->leftJoin('users as responsible', 'kids.responsible_id', '=', 'responsible.id')
                      ->orderBy('responsible.name', $sortDirection)
                      ->select('kids.*');
                break;
            case 'birth_date':
                $query->orderBy('birth_date', $sortDirection);
                break;
            case 'created_at':
                $query->orderBy('created_at', $sortDirection);
                break;
            default:
                $query->orderBy('name', $sortDirection);
                break;
        }

        return $query->paginate($perPage)->appends($filters);
    }

    public function getKidsForProfessional(int $professionalId): Collection
    {
        return $this->findByProfessional($professionalId);
    }

    public function attachProfessional(int $kidId, int $professionalId, array $pivotData = []): bool
    {
        $kid = $this->find($kidId);
        
        if (!$kid) {
            return false;
        }

        $defaultPivotData = [
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $kid->professionals()->attach($professionalId, array_merge($defaultPivotData, $pivotData));
        
        return true;
    }

    public function detachProfessional(int $kidId, int $professionalId): bool
    {
        $kid = $this->find($kidId);
        
        if (!$kid) {
            return false;
        }

        $kid->professionals()->detach($professionalId);
        
        return true;
    }

    public function syncProfessionals(int $kidId, array $professionalIds): bool
    {
        $kid = $this->find($kidId);
        
        if (!$kid) {
            return false;
        }

        $syncData = [];
        foreach ($professionalIds as $professionalId => $pivotData) {
            if (is_numeric($professionalId)) {
                $syncData[$professionalId] = array_merge([
                    'created_at' => now(),
                    'updated_at' => now(),
                ], is_array($pivotData) ? $pivotData : []);
            }
        }

        $kid->professionals()->sync($syncData);
        
        return true;
    }

    private function getKidsForProfessionalRole(?int $userId, $query): Collection
    {
        if (!$userId) {
            return new Collection();
        }

        return $query->where(function ($query) use ($userId) {
            $query->whereHas('professionals', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })->orWhere('created_by', $userId);
        })
        ->with(['professionals', 'responsible', 'checklists'])
        ->get();
    }

    private function getKidsForParentRole(?int $userId, $query): Collection
    {
        if (!$userId) {
            return new Collection();
        }

        return $query->where('responsible_id', $userId)
            ->with(['professionals', 'responsible', 'checklists'])
            ->get();
    }
}