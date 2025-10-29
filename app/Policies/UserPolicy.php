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

    }

    /**
     * Determina se o usuário pode visualizar um usuário específico.
     */
    public function view(User $user, User $model): bool
    {

    }

    /**
     * Determina se o usuário pode criar novos usuários.
     */
    public function create(User $user): bool
    {

    }

    /**
     * Determina se o usuário pode atualizar um usuário específico.
     */
    public function update(User $user, User $model): bool
    {
        return true;
    }

    /**
     * Determina se o usuário pode remover um usuário específico.
     */
    public function delete(User $user, User $model): bool
    {

    }

    /**
     * Determina se o usuário pode restaurar um usuário específico.
     */
    public function restore(User $user, User $model): bool
    {

    }

    /**
     * Determina se o usuário pode forçar a remoção de um usuário específico.
     */
    public function forceDelete(User $user, User $model): bool
    {

    }
}
