<?php

namespace App\Policies;

use App\Models\Kid;
use App\Models\User;
use App\Services\AuthorizationService;
use Illuminate\Auth\Access\HandlesAuthorization;

class KidPolicy
{
    use HandlesAuthorization;

    private AuthorizationService $authService;

    public function __construct(AuthorizationService $authService)
    {
        $this->authService = $authService;
    }

    public function viewAny(User $user): bool
    {
        return $this->authService->canList('kids');
    }

    public function view(User $user, Kid $kid): bool
    {
        return $this->authService->canView('kids', $kid);
    }

    public function create(User $user): bool
    {
        return $this->authService->canCreate('kids');
    }

    public function update(User $user, Kid $kid): bool
    {
        return $this->authService->canEdit('kids', $kid);
    }

    public function delete(User $user, Kid $kid): bool
    {
        return $this->authService->canRemove('kids', $kid);
    }

    public function restore(User $user, Kid $kid): bool
    {
        return $this->authService->canEdit('kids', $kid);
    }

    public function forceDelete(User $user, Kid $kid): bool
    {
        return $this->authService->canRemove('kids', $kid);
    }
}