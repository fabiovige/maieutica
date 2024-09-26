<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Antes de qualquer método, verifica se o usuário é superadmin.
     * Se for, permite todas as ações.
     */
    public function before(User $user, $ability)
    {
        if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
            return true;
        }
    }

    /**
     * Determine se o usuário pode visualizar qualquer usuário.
     */
    public function viewAny(User $user)
    {
        return $user->can('list users');
    }

    /**
     * Determine se o usuário pode visualizar um usuário específico.
     */
    public function view(User $user, User $model)
    {
        return $user->can('view users');
    }

    /**
     * Determine se o usuário pode criar novos usuários.
     */
    public function create(User $user)
    {
        return $user->can('create users');
    }

    /**
     * Determine se o usuário pode atualizar um usuário específico.
     */
    public function update(User $user, User $model)
    {
        return $user->can('update users');
    }

    /**
     * Determine se o usuário pode remover um usuário específico.
     */
    public function delete(User $user, User $model)
    {
        return $user->can('remove users');
    }

    /**
     * Determine se o usuário pode restaurar um usuário específico.
     */
    public function restore(User $user, User $model)
    {
        // Implementar se necessário
        return false;
    }

    /**
     * Determine se o usuário pode forçar a remoção de um usuário específico.
     */
    public function forceDelete(User $user, User $model)
    {
        // Implementar se necessário
        return false;
    }
}
