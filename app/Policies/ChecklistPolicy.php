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
        // Admin pode visualizar qualquer checklist
        if ($user->can('checklist-show-all')) {
            return true;
        }

        // Profissionais podem visualizar checklists que criaram
        if ($user->can('checklist-show') && $checklist->created_by === $user->id) {
            return true;
        }

        // Profissionais podem visualizar checklists de kids vinculados a eles
        if ($user->can('checklist-show')) {
            $professional = $user->professional->first();
            if ($professional && $checklist->kid && $checklist->kid->professionals->contains($professional->id)) {
                return true;
            }
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
        // Admin pode editar qualquer checklist
        if ($user->can('checklist-edit-all')) {
            return true;
        }

        // Profissionais podem editar checklists que criaram
        if ($user->can('checklist-edit') && $checklist->created_by === $user->id) {
            return true;
        }

        // Profissionais podem editar checklists de kids vinculados a eles
        if ($user->can('checklist-edit')) {
            $professional = $user->professional->first();
            if ($professional && $checklist->kid && $checklist->kid->professionals->contains($professional->id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enviar checklist para a lixeira (soft delete).
     */
    public function delete(User $user, Checklist $checklist): bool
    {
        // Admin pode deletar qualquer checklist
        if ($user->can('checklist-delete-all')) {
            return true;
        }

        // Profissionais podem deletar checklists que criaram
        if ($user->can('checklist-delete') && $checklist->created_by === $user->id) {
            return true;
        }

        // Profissionais podem deletar checklists de kids vinculados a eles
        if ($user->can('checklist-delete')) {
            $professional = $user->professional->first();
            if ($professional && $checklist->kid && $checklist->kid->professionals->contains($professional->id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Visualizar a lixeira de checklists.
     */
    public function viewTrash(User $user): bool
    {
        // Apenas usuários com permissão -all podem ver a lixeira completa
        return $user->can('checklist-list-all');
    }

    /**
     * Restaurar um checklist.
     */
    public function restore(User $user, Checklist $checklist): bool
    {
        // Admin pode restaurar qualquer checklist
        if ($user->can('checklist-edit-all')) {
            return true;
        }

        // Profissionais podem restaurar checklists que criaram
        if ($user->can('checklist-edit') && $checklist->created_by === $user->id) {
            return true;
        }

        // Profissionais podem restaurar checklists de kids vinculados a eles
        if ($user->can('checklist-edit')) {
            $professional = $user->professional->first();
            if ($professional && $checklist->kid && $checklist->kid->professionals()->withTrashed()->get()->contains($professional->id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Forçar exclusão permanente.
     */
    public function forceDelete(User $user, Checklist $checklist): bool
    {
        return $user->can('checklist-delete-all');
    }
}
