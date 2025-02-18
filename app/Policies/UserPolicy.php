<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Método executado antes de qualquer outra permissão.
     * Permite todas as ações para superadmin e admin.
     *
     * @param  string  $ability
     * @return bool|null
     */
    /*public function before(User $user, $ability): ?bool
    {
        if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
            return true;
        }

        return null;
    }*/

    /**
     * Determina se o usuário pode visualizar qualquer usuário (listagem).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('list users');
    }

    /**
     * Determina se o usuário pode visualizar um usuário específico.
     */
    public function view(User $user, User $model): bool
    {
        // Permite visualizar se o usuário tem a permissão 'view users' ou se está visualizando a si mesmo
        return $user->can('view users') || $user->id === $model->id;
    }

    /**
     * Determina se o usuário pode criar novos usuários.
     */
    public function create(User $user): bool
    {
        return $user->can('create users');
    }

    /**
     * Determina se o usuário pode atualizar um usuário específico.
     */
    public function update(User $user, User $model): bool
    {
        // Permite atualizar se o usuário tem a permissão ou se está atualizando a si mesmo
        return $user->can('edit users') || $user->id === $model->id;
    }

    /**
     * Determina se o usuário pode remover um usuário específico.
     */
    public function delete(User $user, User $model): bool
    {
        // Impede que o usuário remova a si mesmo
        if ($user->id === $model->id) {
            return false; // Não permite remover a si mesmo
        }

        // Verifica se o usuário tem permissão para remover outros usuários
        return $user->can('remove users');
    }

    /**
     * Determina se o usuário pode restaurar um usuário específico.
     */
    public function restore(User $user, User $model): bool
    {
        // Implementar se necessário
        return $user->can('restore users');
    }

    /**
     * Determina se o usuário pode forçar a remoção de um usuário específico.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Implementar se necessário
        return $user->can('force delete users');
    }
}
