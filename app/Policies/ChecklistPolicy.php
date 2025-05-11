<?php

namespace App\Policies;

use App\Models\Checklist;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ChecklistPolicy
{
    use HandlesAuthorization;

    /**
     * Método executado antes de qualquer outra permissão.
     * Permite todas as ações para superadmin e admin.
     *
     * @param  string  $ability
     * @return bool|null
     */
    public function before(User $user, $ability)
    {
        if (method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('superadmin'))) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return (method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('professional') || $user->hasRole('pais') || $user->hasRole('superadmin')));
    }

    /**
     * Determina se o usuário pode visualizar o registro de um checklist.
     */
    public function view(User $user, Checklist $checklist): bool
    {
        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) return true;
        if (method_exists($user, 'hasRole') && $user->hasRole('professional')) {
            return $checklist->created_by === $user->id;
        }
        if (method_exists($user, 'hasRole') && $user->hasRole('pais')) {
            return $checklist->kid->responsible_id === $user->id;
        }
        return false;
    }

    /**
     * Determina se o usuário pode criar um registro de checklist.
     */
    public function create(User $user): bool
    {
        return (method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('professional')));
    }

    /**
     * Determina se o usuário pode atualizar o registro de um checklist.
     */
    public function update(User $user, Checklist $checklist): bool
    {
        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) return true;
        if (method_exists($user, 'hasRole') && $user->hasRole('professional')) {
            return $checklist->created_by === $user->id;
        }
        return false;
    }

    /**
     * Determina se o usuário pode deletar o registro de um checklist.
     */
    public function delete(User $user, Checklist $checklist): bool
    {
        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) return true;
        if (method_exists($user, 'hasRole') && $user->hasRole('professional')) {
            return $checklist->created_by === $user->id;
        }
        return false;
    }

    /**
     * Determina se o usuário pode restaurar o registro de um checklist deletado.
     */
    public function restore(User $user, Checklist $checklist): bool
    {
        return (method_exists($user, 'hasRole') && $user->hasRole('admin'));
    }

    /**
     * Determina se o usuário pode forçar a exclusão do registro de um checklist.
     */
    public function forceDelete(User $user, Checklist $checklist): bool
    {
        return (method_exists($user, 'hasRole') && $user->hasRole('admin'));
    }
}
