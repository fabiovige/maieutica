<?php

namespace App\Policies;

use App\Models\Checklist;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChecklistPolicy
{
    use HandlesAuthorization;

    /**
     * Listar checklists.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('checklist-list') || $user->can('checklist-list-all');
    }

    /**
     * Visualizar um checklist específico.
     */
    public function view(User $user, Checklist $checklist): bool
    {
        // Pode visualizar se tem permissão
        if ($user->can('checklist-show') || $user->can('checklist-show-all')) {
            return true;
        }

        // Profissionais podem visualizar checklists que criaram
        if ($checklist->created_by === $user->id) {
            return true;
        }

        // Responsáveis podem visualizar checklists de suas crianças
        if ($checklist->kid && $checklist->kid->responsible_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Criar novos checklists.
     */
    public function create(User $user): bool
    {
        return $user->can('checklist-create') || $user->can('checklist-create-all');
    }

    /**
     * Atualizar um checklist específico.
     */
    public function update(User $user, Checklist $checklist): bool
    {
        // Pode editar se tem permissão
        if ($user->can('checklist-edit') || $user->can('checklist-edit-all')) {
            return true;
        }

        // Profissionais podem editar checklists que criaram
        if ($checklist->created_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Enviar checklist para a lixeira (soft delete).
     */
    public function delete(User $user, Checklist $checklist): bool
    {
        // Pode deletar se tem permissão
        if ($user->can('checklist-delete') || $user->can('checklist-delete-all')) {
            return true;
        }

        // Profissionais podem deletar checklists que criaram
        if ($checklist->created_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Visualizar a lixeira de checklists.
     */
    public function viewTrash(User $user): bool
    {
        return $user->can('checklist-edit') || $user->can('checklist-list-all');
    }

    /**
     * Restaurar um checklist.
     */
    public function restore(User $user, Checklist $checklist): bool
    {
        return $user->can('checklist-edit') || $user->can('checklist-edit-all');
    }

    /**
     * Forçar exclusão permanente.
     */
    public function forceDelete(User $user, Checklist $checklist): bool
    {
        return $user->can('checklist-delete-all');
    }
}
