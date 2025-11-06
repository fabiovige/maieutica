<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Listar usuários.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('user-list') || $user->can('user-list-all');
    }

    /**
     * Visualizar um usuário específico.
     */
    public function view(User $user, User $model): bool
    {
        // Pode visualizar se tem permissão
        if ($user->can('user-show') || $user->can('user-show-all')) {
            return true;
        }

        // Pode visualizar a si mesmo
        return $user->id === $model->id;
    }

    /**
     * Criar novos usuários.
     */
    public function create(User $user): bool
    {
        return $user->can('user-create') || $user->can('user-create-all');
    }

    /**
     * Atualizar um usuário específico.
     */
    public function update(User $user, User $model): bool
    {
        // Pode editar se tem permissão
        if ($user->can('user-edit') || $user->can('user-edit-all')) {
            return true;
        }

        // Pode editar o próprio perfil
        return $user->id === $model->id;
    }

    /**
     * Enviar usuário para a lixeira (soft delete).
     */
    public function delete(User $user, User $model): bool
    {
        // Apenas admin pode deletar usuários
        return $user->can('user-delete-all');
    }

    /**
     * Visualizar a lixeira de usuários.
     */
    public function viewTrash(User $user): bool
    {
        // Apenas admin pode ver a lixeira de usuários
        return $user->can('user-list-all');
    }

    /**
     * Restaurar um usuário.
     */
    public function restore(User $user, User $model): bool
    {
        // Apenas admin pode restaurar usuários
        return $user->can('user-edit-all');
    }

    /**
     * Forçar exclusão permanente.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Ninguém pode se excluir
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can('user-delete-all');
    }
}
