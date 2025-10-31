<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode visualizar qualquer perfil (listagem).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('role-list') || $user->can('role-list-all');
    }

    /**
     * Determina se o usuário pode visualizar um perfil específico.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->can('role-show') || $user->can('role-show-all');
    }

    /**
     * Determina se o usuário pode criar novos perfis.
     */
    public function create(User $user): bool
    {
        return $user->can('role-create') || $user->can('role-create-all');
    }

    /**
     * Determina se o usuário pode atualizar um perfil específico.
     */
    public function update(User $user, Role $role): bool
    {
        return $user->can('role-edit') || $user->can('role-edit-all');
    }

    /**
     * Determina se o usuário pode remover um perfil específico.
     */
    public function delete(User $user, Role $role): bool
    {
        // Apenas admin pode deletar roles
        return $user->can('role-delete-all');
    }

    /**
     * Determina se o usuário pode visualizar a lixeira de perfis.
     */
    public function viewTrash(User $user): bool
    {
        // Apenas admin pode ver a lixeira de roles
        return $user->can('role-list-all');
    }

    /**
     * Determina se o usuário pode restaurar um perfil específico.
     */
    public function restore(User $user, Role $role): bool
    {
        // Apenas admin pode restaurar roles
        return $user->can('role-edit-all');
    }

    /**
     * Determina se o usuário pode forçar a remoção de um perfil específico.
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return $user->can('role-delete-all');
    }
}
