<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Professional;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProfessionalPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('list professionals') || $user->hasRole(['admin', 'superadmin']);
    }

    public function view(User $user, Professional $professional): bool
    {
        if ($user->can('view professionals') || $user->hasRole(['admin', 'superadmin'])) {
            return true;
        }
        
        return $user->id === $professional->user->first()?->id;
    }

    public function create(User $user): bool
    {
        return $user->can('create professionals') || $user->hasRole(['admin', 'superadmin']);
    }

    public function update(User $user, Professional $professional): bool
    {
        if ($user->can('edit professionals') || $user->hasRole(['admin', 'superadmin'])) {
            return true;
        }
        
        return $user->id === $professional->user->first()?->id;
    }

    public function delete(User $user, Professional $professional): bool
    {
        if ($user->id === $professional->user->first()?->id) {
            return false;
        }

        return $user->can('remove professionals') || $user->hasRole(['admin', 'superadmin']);
    }

    public function restore(User $user, Professional $professional): bool
    {
        return $user->can('restore professionals') || $user->hasRole(['admin', 'superadmin']);
    }

    public function forceDelete(User $user, Professional $professional): bool
    {
        return $user->can('force delete professionals') || $user->hasRole(['admin', 'superadmin']);
    }
}