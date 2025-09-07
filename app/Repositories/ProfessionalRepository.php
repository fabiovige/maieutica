<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\ProfessionalRepositoryInterface;
use App\Models\Professional;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProfessionalRepository extends BaseRepository implements ProfessionalRepositoryInterface
{
    public function __construct(Professional $model)
    {
        parent::__construct($model);
    }


    public function paginateWithFilters(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['user' => function($query) {
                $query->with('roles');
            }, 'specialty'])
            ->whereHas('user', function ($q) {
                $q->whereHas('roles', function ($roleQuery) {
                      $roleQuery->where('name', 'professional');
                  });
            });

        if (!empty($filters['search'])) {
            $query = $this->applySearchFilterProfessional($query, $filters['search']);
        }

        $query = $this->applySortFilterProfessional($query, $filters);

        return $this->paginateWithQueryString($query, $perPage);
    }

    public function findBySpecialty(int $specialtyId): Collection
    {
        return $this->model->with(['user', 'specialty'])
            ->where('specialty_id', $specialtyId)
            ->get();
    }

    public function findByRegistrationNumber(string $registrationNumber): ?object
    {
        return $this->model->where('registration_number', $registrationNumber)->first();
    }

    public function getActiveProfessionals(): Collection
    {
        return $this->model->with(['user', 'specialty'])
            ->whereHas('user', function ($q) {
                $q->where('allow', true);
            })->get();
    }


    public function attachUser(int $professionalId, int $userId): bool
    {
        $professional = $this->model->find($professionalId);
        if (!$professional) {
            return false;
        }

        $professional->user()->attach($userId);
        return true;
    }

    public function detachUser(int $professionalId, int $userId): bool
    {
        $professional = $this->model->find($professionalId);
        if (!$professional) {
            return false;
        }

        $professional->user()->detach($userId);
        return true;
    }

    public function findByUser(int $userId): ?object
    {
        return $this->model->whereHas('user', function ($q) use ($userId) {
            $q->where('users.id', $userId);
        })->first();
    }


    private function applySearchFilterProfessional($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->whereHas('user', function ($userQuery) use ($search) {
                $userQuery->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            })
            ->orWhere('registration_number', 'LIKE', "%{$search}%")
            ->orWhereHas('specialty', function ($specialtyQuery) use ($search) {
                $specialtyQuery->where('name', 'LIKE', "%{$search}%");
            });
        });
    }

    private function applySortFilterProfessional($query, array $filters)
    {
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDirection = $filters['sort_direction'] ?? 'asc';

        switch ($sortBy) {
            case 'name':
                return $query->orderBy(
                    DB::raw('(SELECT name FROM users INNER JOIN user_professional ON users.id = user_professional.user_id WHERE user_professional.professional_id = professionals.id LIMIT 1)'),
                    $sortDirection
                );
            case 'specialty':
                return $query->orderBy(
                    DB::raw('(SELECT name FROM specialties WHERE specialties.id = professionals.specialty_id)'),
                    $sortDirection
                );
            case 'registration':
                return $query->orderBy('registration_number', $sortDirection);
            case 'status':
                return $query->orderBy(
                    DB::raw('(SELECT allow FROM users INNER JOIN user_professional ON users.id = user_professional.user_id WHERE user_professional.professional_id = professionals.id LIMIT 1)'),
                    $sortDirection
                );
            case 'id':
                return $query->orderBy('id', $sortDirection);
            default:
                return $query->orderBy('created_at', 'desc');
        }
    }
}