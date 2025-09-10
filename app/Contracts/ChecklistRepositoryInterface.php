<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Checklist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ChecklistRepositoryInterface
{
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function getChecklistsForUser(int $userId, array $filters = []): LengthAwarePaginator;

    public function getChecklistsByKid(int $kidId, array $filters = []): LengthAwarePaginator;

    public function markAsDeleted(Checklist $checklist, int $deletedBy): bool;
}
