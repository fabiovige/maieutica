<?php

namespace App\Policies;

use App\Models\DocumentTemplate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentTemplatePolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode visualizar qualquer template (listagem).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('template-list') || $user->can('template-list-all');
    }

    /**
     * Determina se o usuário pode visualizar um template específico.
     */
    public function view(User $user, DocumentTemplate $documentTemplate): bool
    {
        return $user->can('template-show') || $user->can('template-show-all');
    }

    /**
     * Determina se o usuário pode criar novos templates.
     */
    public function create(User $user): bool
    {
        return $user->can('template-create');
    }

    /**
     * Determina se o usuário pode atualizar um template específico.
     */
    public function update(User $user, DocumentTemplate $documentTemplate): bool
    {
        return $user->can('template-edit') || $user->can('template-edit-all');
    }

    /**
     * Determina se o usuário pode remover um template específico.
     */
    public function delete(User $user, DocumentTemplate $documentTemplate): bool
    {
        return $user->can('template-delete') || $user->can('template-delete-all');
    }

    /**
     * Determina se o usuário pode visualizar a lixeira de templates.
     */
    public function viewTrash(User $user): bool
    {
        return $user->can('template-edit') || $user->can('template-list-all');
    }

    /**
     * Determina se o usuário pode restaurar um template específico.
     */
    public function restore(User $user, DocumentTemplate $documentTemplate): bool
    {
        return $user->can('template-edit') || $user->can('template-edit-all');
    }

    /**
     * Determina se o usuário pode forçar a remoção de um template específico.
     */
    public function forceDelete(User $user, DocumentTemplate $documentTemplate): bool
    {
        return $user->can('template-delete-all');
    }
}
