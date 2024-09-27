<?php

namespace App\Policies;

use App\Models\Kid;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KidPolicy
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
     * Determina se o usuário pode visualizar o registro de uma criança.
     *
     * @param User $user
     * @param Kid $kid
     * @return bool
     */
    public function view(User $user, Kid $kid): bool
    {
        // Permite visualizar se o usuário é o professional associado à criança
        return $user->id === $kid->profession_id;
    }

    /**
     * Determina se o usuário pode criar um registro de criança.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Permite criação de registro se o usuário tem permissão para criar crianças
        return $user->can('create kids');
    }

    /**
     * Determina se o usuário pode atualizar o registro de uma criança.
     *
     * @param User $user
     * @param Kid $kid
     * @return bool
     */
    public function update(User $user, Kid $kid): bool
    {
        // Permite atualizar se o usuário é o professional associado à criança
        return $user->id === $kid->profession_id;
    }

    /**
     * Determina se o usuário pode deletar o registro de uma criança.
     *
     * @param User $user
     * @param Kid $kid
     * @return bool
     */
    public function delete(User $user, Kid $kid): bool
    {
        // Permite deletar se o usuário é o professional associado à criança
        return $user->id === $kid->profession_id;
    }

    /**
     * Determina se o usuário pode restaurar o registro de uma criança deletada.
     *
     * @param User $user
     * @param Kid $kid
     * @return bool
     */
    public function restore(User $user, Kid $kid): bool
    {
        // Permite restaurar se o usuário é o professional associado à criança
        return $user->id === $kid->profession_id;
    }

    /**
     * Determina se o usuário pode forçar a exclusão do registro de uma criança.
     *
     * @param User $user
     * @param Kid $kid
     * @return bool
     */
    public function forceDelete(User $user, Kid $kid): bool
    {
        // Permite exclusão definitiva se o usuário é o professional associado à criança
        return $user->id === $kid->profession_id;
    }
}
