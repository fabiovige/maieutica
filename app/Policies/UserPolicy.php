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
     * @param User $user
     * @param string $ability
     * @return bool|null
     */
    public function before(User $user, $ability): ?bool
    {
        if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    /**
     * Determina se o usuário pode visualizar qualquer usuário (listagem).
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('list users');
    }

    /**
     * Determina se o usuário pode visualizar um usuário específico.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function view(User $user, User $model): bool
    {
        // Permite visualizar se o usuário tem a permissão 'view users' ou se está visualizando a si mesmo
        return $user->can('view users') || $user->id === $model->id;
    }

    /**
     * Determina se o usuário pode criar novos usuários.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create users');
    }

    /**
     * Determina se o usuário pode atualizar um usuário específico.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function update(User $user, User $model): bool
    {
        // Permite atualizar se o usuário tem a permissão ou se está atualizando a si mesmo
        return $user->can('update users') || $user->id === $model->id;
    }

    /**
     * Determina se o usuário pode remover um usuário específico.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function delete(User $user, User $model): bool
    {
        // Impede que o usuário remova a si mesmo
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can('remove users');
    }

    /**
     * Determina se o usuário pode restaurar um usuário específico.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function restore(User $user, User $model): bool
    {
        // Implementar se necessário
        return $user->can('restore users');
    }

    /**
     * Determina se o usuário pode forçar a remoção de um usuário específico.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Implementar se necessário
        return $user->can('force delete users');
    }
}
