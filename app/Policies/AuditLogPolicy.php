<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AuditLogPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['SuperAdmin', 'Admin']) ||
               $user->can('view-audit-logs');
    }

    public function view(User $user, AuditLog $auditLog): bool
    {
        if ($user->hasAnyRole(['SuperAdmin', 'Admin'])) {
            return true;
        }

        if ($user->can('view-own-audit-logs')) {
            return $auditLog->user_id === $user->id;
        }

        return false;
    }

    public function export(User $user): bool
    {
        return $user->hasAnyRole(['SuperAdmin', 'Admin']) ||
               $user->can('export-audit-logs');
    }

    public function viewStats(User $user): bool
    {
        return $user->hasAnyRole(['SuperAdmin', 'Admin']) ||
               $user->can('view-audit-stats');
    }

    public function delete(User $user, AuditLog $auditLog): bool
    {
        return $user->hasRole('SuperAdmin') ||
               $user->can('delete-audit-logs');
    }
}
