<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\ChecklistRepositoryInterface;
use App\Models\Checklist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ChecklistRepository extends BaseRepository implements ChecklistRepositoryInterface
{
    public function __construct(Checklist $model)
    {
        parent::__construct($model);
    }

    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->buildBaseQuery();

        if (!empty($filters['kid_id'])) {
            $query->where('kid_id', $filters['kid_id']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('description', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('kid', function ($kidQuery) use ($filters) {
                      $kidQuery->where('name', 'like', '%' . $filters['search'] . '%');
                  });
            });
        }

        if (!empty($filters['situation'])) {
            $query->where('situation', $filters['situation']);
        }

        if (!empty($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        if (in_array($sortBy, ['id', 'created_at', 'level', 'situation'])) {
            $query->orderBy($sortBy, $sortDirection);
        }

        if ($sortBy !== 'id') {
            $query->orderBy('id', 'desc');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function getChecklistsForUser(int $userId, array $filters = []): LengthAwarePaginator
    {
        $cacheKey = "checklists.user.{$userId}." . md5(serialize($filters));
        
        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($userId, $filters) {
            return $this->getPaginated($filters['per_page'] ?? 15, $filters);
        });
    }

    public function getChecklistsByKid(int $kidId, array $filters = []): LengthAwarePaginator
    {
        $filters['kid_id'] = $kidId;
        return $this->getPaginated($filters['per_page'] ?? 15, $filters);
    }

    public function markAsDeleted(Checklist $checklist, int $deletedBy): bool
    {
        $checklist->deleted_by = $deletedBy;
        $checklist->save();
        
        $this->clearCache();
        
        return $checklist->delete();
    }

    private function buildBaseQuery(): Builder
    {
        $user = Auth::user();
        
        $query = $this->model->newQuery()
            ->with(['kid:id,name,responsible_id'])
            ->select(['id', 'kid_id', 'level', 'situation', 'created_at', 'description']);

        if ($user->can('viewAny', Checklist::class)) {
            return $query;
        }

        if ($user->can('viewOwn', Checklist::class)) {
            $query->whereHas('kid', function ($q) use ($user) {
                $q->where('responsible_id', $user->id);
            });
        }

        if ($user->can('viewAssigned', Checklist::class)) {
            $professionalId = $user->professional->first()?->id;
            if ($professionalId) {
                $query->whereHas('kid.professionals', function ($q) use ($professionalId) {
                    $q->where('professional_id', $professionalId);
                });
            }
        }

        return $query;
    }

    private function clearCache(): void
    {
        Cache::tags(['checklists'])->flush();
    }
}