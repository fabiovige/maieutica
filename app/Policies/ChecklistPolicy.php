<?php

namespace App\Policies;

use App\Models\Checklist;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChecklistPolicy
{
    use HandlesAuthorization;

    /**
     * Método executado antes de qualquer outra permissão.
     * Permite todas as ações para usuários com permissão bypass-all-checks
     */
    public function before(User $user, $ability)
    {
        if ($user->can('bypass-all-checks')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->can('list checklists');
    }

    /**
     * Determina se o usuário pode visualizar o registro de um checklist.
     */
    public function view(User $user, Checklist $checklist): bool
    {
        if ($user->can('view checklists')) {
            // Verifica se tem permissão para ver todos ou se é o criador
            if ($user->can('view-all-kids')) {
                return true;
            }

            // Professional só pode ver checklists que criou ou de crianças associadas
            if ($user->can('attach-to-kids-as-professional')) {
                return $checklist->created_by === $user->id ||
                       $checklist->kid->professionals->contains($user->id);
            }

            // Responsável só pode ver checklists de suas crianças
            return $checklist->kid->responsible_id === $user->id;
        }

        return false;
    }

    /**
     * Determina se o usuário pode criar um registro de checklist.
     */
    public function create(User $user): bool
    {
        return $user->can('create checklists');
    }

    /**
     * Determina se o usuário pode atualizar o registro de um checklist.
     */
    public function update(User $user, Checklist $checklist): bool
    {
        if ($user->can('edit checklists')) {
            // Admin/Superadmin podem editar qualquer checklist
            if ($user->can('manage-system')) {
                return true;
            }

            // Verifica se o checklist está fechado e se tem permissão para override
            if ($checklist->situation !== 'a' && !$user->can('override-checklist-status')) {
                return false;
            }

            // Professional só pode editar checklists que criou
            if ($user->can('attach-to-kids-as-professional')) {
                return $checklist->created_by === $user->id;
            }
        }

        return false;
    }

    /**
     * Determina se o usuário pode deletar o registro de um checklist.
     */
    public function delete(User $user, Checklist $checklist): bool
    {
        if ($user->can('remove checklists')) {
            // Admin/Superadmin podem deletar qualquer checklist
            if ($user->can('manage-system')) {
                return true;
            }

            // Professional só pode deletar checklists que criou
            if ($user->can('attach-to-kids-as-professional')) {
                return $checklist->created_by === $user->id;
            }
        }

        return false;
    }

    /**
     * Determina se o usuário pode restaurar o registro de um checklist deletado.
     */
    public function restore(User $user, Checklist $checklist): bool
    {
        return $user->can('manage-system');
    }

    /**
     * Determina se o usuário pode forçar a exclusão do registro de um checklist.
     */
    public function forceDelete(User $user, Checklist $checklist): bool
    {
        return $user->can('manage-system');
    }
}
