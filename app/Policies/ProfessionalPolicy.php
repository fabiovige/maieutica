<?php

namespace App\Policies;

use App\Models\Professional;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProfessionalPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode visualizar qualquer profissional (listagem).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('professional-list');
    }

    /**
     * Determina se o usuário pode visualizar um profissional específico.
     */
    public function view(User $user, Professional $professional): bool
    {
        return $user->can('professional-list');
    }

    /**
     * Determina se o usuário pode criar novos profissionais.
     */
    public function create(User $user): bool
    {
        return $user->can('professional-create');
    }

    /**
     * Determina se o usuário pode atualizar um profissional específico.
     */
    public function update(User $user, Professional $professional): bool
    {
        return $user->can('professional-edit');
    }

    /**
     * Determina se o usuário pode remover um profissional específico.
     */
    public function delete(User $user, Professional $professional): bool
    {
        // Profissional não pode deletar a si mesmo
        $professionalUser = $professional->user->first();
        if ($professionalUser && $user->id === $professionalUser->id) {
            return false;
        }

        return $user->can('professional-delete');
    }

    /**
     * Determina se o usuário pode visualizar a lixeira de profissionais.
     */
    public function viewTrash(User $user): bool
    {
        return $user->can('professional-restore') || $user->can('professional-list-all');
    }

    /**
     * Determina se o usuário pode restaurar um profissional específico.
     */
    public function restore(User $user, Professional $professional): bool
    {
        return $user->can('professional-restore') || $user->can('professional-edit-all');
    }

    /**
     * Determina se o usuário pode forçar a remoção de um profissional específico.
     */
    public function forceDelete(User $user, Professional $professional): bool
    {
        return $user->can('professional-delete-all');
    }
}
