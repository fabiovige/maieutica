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
        return $user->can('professional-list') || $user->can('professional-list-all');
    }

    /**
     * Determina se o usuário pode visualizar um profissional específico.
     */
    public function view(User $user, Professional $professional): bool
    {
        // Admin pode visualizar qualquer profissional
        if ($user->can('professional-show-all')) {
            return true;
        }

        // Profissional só pode visualizar seu próprio perfil
        if ($user->can('professional-show')) {
            $userProfessional = $user->professional->first();
            if ($userProfessional && $userProfessional->id === $professional->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determina se o usuário pode criar novos profissionais.
     */
    public function create(User $user): bool
    {
        return $user->can('professional-create') || $user->can('professional-create-all');
    }

    /**
     * Determina se o usuário pode atualizar um profissional específico.
     */
    public function update(User $user, Professional $professional): bool
    {
        // Admin pode editar qualquer profissional
        if ($user->can('professional-edit-all')) {
            return true;
        }

        // Profissional só pode editar seu próprio perfil
        if ($user->can('professional-edit')) {
            $userProfessional = $user->professional->first();
            if ($userProfessional && $userProfessional->id === $professional->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determina se o usuário pode remover um profissional específico.
     */
    public function delete(User $user, Professional $professional): bool
    {
        // Apenas admin pode deletar profissionais
        return $user->can('professional-delete-all');
    }

    /**
     * Determina se o usuário pode visualizar a lixeira de profissionais.
     */
    public function viewTrash(User $user): bool
    {
        // Apenas admin pode ver a lixeira de profissionais
        return $user->can('professional-list-all');
    }

    /**
     * Determina se o usuário pode restaurar um profissional específico.
     */
    public function restore(User $user, Professional $professional): bool
    {
        // Apenas admin pode restaurar profissionais
        return $user->can('professional-edit-all');
    }

    /**
     * Determina se o usuário pode forçar a remoção de um profissional específico.
     */
    public function forceDelete(User $user, Professional $professional): bool
    {
        return $user->can('professional-delete-all');
    }
}
