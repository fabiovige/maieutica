<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function create(array $data): User
    {
        /** @var User $user */
        $user = parent::create($data);

        $this->clearCache();

        return $user->fresh();
    }

    public function find(int $id): ?User
    {
        return Cache::remember(
            "user.{$id}",
            now()->addMinutes(30),
            fn () => parent::find($id)
        );
    }


    public function update(int $id, array $data): bool
    {
        $result = parent::update($id, $data);

        if ($result) {
            $this->clearCache();
        }

        return $result;
    }

    public function updateUser(int $id, array $data): User
    {
        $user = $this->findById($id);

        if (!$user) {
            throw new ModelNotFoundException("User with ID {$id} not found");
        }

        $user->update($data);

        $this->clearCache();

        return $user->fresh();
    }


    public function delete(int $id): bool
    {
        $result = parent::delete($id);

        if ($result) {
            $this->clearCache();
        }

        return $result;
    }

    public function deleteUser(User $user): bool
    {
        $result = $user->delete();

        $this->clearCache();

        return $result;
    }

    public function findById(int $id): ?User
    {
        return Cache::remember(
            "user.{$id}",
            now()->addMinutes(30),
            fn () => parent::find($id)
        );
    }

    public function existsById(int $id): bool
    {
        return Cache::remember(
            "user.exists.{$id}",
            now()->addMinutes(15),
            fn () => parent::exists($id)
        );
    }


    public function findByEmail(string $email): ?User
    {
        return Cache::remember(
            "user.email.{$email}",
            now()->addMinutes(30),
            fn () => $this->findOneBy('email', $email)
        );
    }

    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->query()->with('roles');

        // Filtro para não mostrar Super Admin para usuários sem permissão total
        if (!auth()->user()?->can('bypass-all-checks')) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'superadmin');
            });
        }

        // Filtro de busca geral
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('roles', function ($roleQuery) use ($searchTerm) {
                      $roleQuery->where('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        // Filtro por nome específico
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        // Filtro por email específico
        if (!empty($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        // Filtro por role
        if (!empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        // Filtro por status ativo
        if (isset($filters['active'])) {
            $query->where('allow', (bool) $filters['active']);
        }

        // Ordenação
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDirection = $filters['sort_direction'] ?? 'asc';

        switch ($sortBy) {
            case 'id':
                $query->orderBy('id', $sortDirection);
                break;
            case 'name':
                $query->orderBy('name', $sortDirection);
                break;
            case 'email':
                $query->orderBy('email', $sortDirection);
                break;
            case 'created_at':
                $query->orderBy('created_at', $sortDirection);
                break;
            case 'role':
                $query->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                     ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
                     ->groupBy('users.id')
                     ->orderBy('roles.name', $sortDirection)
                     ->select('users.*');
                break;
            case 'status':
                $query->orderBy('allow', $sortDirection);
                break;
            default:
                $query->orderBy('name', 'asc');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function getAllForSelect(): array
    {
        return Cache::remember(
            'users.select',
            now()->addHours(1),
            function () {
                return $this->model->query()
                    ->when(!auth()->user()?->can('bypass-all-checks'), function ($query) {
                        $query->whereDoesntHave('roles', function ($q) {
                            $q->where('name', 'superadmin');
                        });
                    })
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->toArray();
            }
        );
    }

    public function getByRole(string $roleName): Collection
    {
        return Cache::remember(
            "users.role.{$roleName}",
            now()->addMinutes(30),
            function () use ($roleName) {
                return $this->model->whereHas('roles', function ($query) use ($roleName) {
                    $query->where('name', $roleName);
                })->get();
            }
        );
    }


    public function hasRoles(User $user): bool
    {
        return $user->roles()->exists();
    }

    public function markAsDeleted(User $user, int $deletedBy): bool
    {
        $user->deleted_by = $deletedBy;
        $result = $user->save();

        $this->clearCache();

        return $result;
    }

    public function getActiveUsers(): Collection
    {
        return $this->model->where('allow', true)->get();
    }

    public function getInactiveUsers(): Collection
    {
        return $this->model->where('allow', false)->get();
    }

    public function countByRole(string $roleName): int
    {
        return Cache::remember(
            "users.count.role.{$roleName}",
            now()->addMinutes(30),
            function () use ($roleName) {
                return $this->model->whereHas('roles', function ($query) use ($roleName) {
                    $query->where('name', $roleName);
                })->count();
            }
        );
    }

    public function getRecentUsers(int $limit = 10): Collection
    {
        return $this->model->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private function clearCache(): void
    {
        Cache::forget('users.select');

        // Limpar cache por roles comuns
        $commonRoles = ['professional', 'pais', 'admin'];
        foreach ($commonRoles as $role) {
            Cache::forget("users.role.{$role}");
            Cache::forget("users.count.role.{$role}");
        }
    }
}
