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
    /*public function before(User $user, $ability): ?bool
    {
        if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
            return true;
        }

        return false;
    }*/

    public function viewAny(User $user): bool
    {
        // Verifica se o usuário tem a permissão de listar checklists
        return $user->can('list checklists');
    }

    /**
     * Determina se o usuário pode visualizar o registro de um checklist.
     */
    public function view(User $user, Checklist $checklist): bool
    {
        // Permite visualizar se o usuário é o criador do checklist
        return $user->can('view checklists');
        // && ($user->id === $checklist->created_by || $user->id === $checklist->kid->profession_id || $user->id === $checklist->kid->responsible_id);
    }

    /**
     * Determina se o usuário pode criar um registro de checklist.
     */
    public function create(User $user): bool
    {
        // Permite criação se o usuário tem a permissão para criar checklists
        return $user->can('create checklists');
    }

    /**
     * Determina se o usuário pode atualizar o registro de um checklist.
     */
    public function update(User $user, Checklist $checklist): bool
    {
        if ($checklist->situation === 'f' && ! $user->hasRole('admin')) {
            return false;
        }

        // Se o usuário for admin, permitir
        if ($user->hasRole('admin') || $user->hasRole('professional')) {
            return true;
        }

        // Permite atualizar se o usuário é o criador do checklist
        return $user->can('edits checklists') &&
            ($user->id === $checklist->created_by || $user->id === $checklist->kid->profession_id);
    }

    /**
     * Determina se o usuário pode deletar o registro de um checklist.
     */
    public function delete(User $user, Checklist $checklist): bool
    {
        // Permite deletar se o usuário é o criador do checklist
        return $user->can('remove checklists') &&
            ($user->id === $checklist->created_by || $user->id === $checklist->kid->profession_id);
    }

    /**
     * Determina se o usuário pode restaurar o registro de um checklist deletado.
     */
    public function restore(User $user, Checklist $checklist): bool
    {
        // Permite restaurar se o usuário é o criador do checklist
        return $user->id === $checklist->created_by;
    }

    /**
     * Determina se o usuário pode forçar a exclusão do registro de um checklist.
     */
    public function forceDelete(User $user, Checklist $checklist): bool
    {
        // Permite exclusão definitiva se o usuário é o criador do checklist
        return $user->id === $checklist->created_by;
    }
}
