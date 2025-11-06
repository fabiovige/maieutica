<?php

namespace App\Policies;

use App\Models\Plane;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlanePolicy
{
    use HandlesAuthorization;

    /**
     * Listar planos.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('plan-list') || $user->can('plan-list-all');
    }

    /**
     * Visualizar um plano específico.
     */
    public function view(User $user, Plane $plane): bool
    {
        // Admin pode visualizar qualquer plano
        if ($user->can('plan-show-all')) {
            return true;
        }

        // Profissionais podem visualizar planos que criaram
        if ($user->can('plan-show') && $plane->created_by === $user->id) {
            return true;
        }

        // Profissionais podem visualizar planos de kids vinculados a eles
        if ($user->can('plan-show')) {
            $professional = $user->professional->first();
            if ($professional && $plane->kid && $plane->kid->professionals->contains($professional->id)) {
                return true;
            }
        }

        // Responsáveis podem visualizar planos de suas crianças
        if ($plane->kid && $plane->kid->responsible_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Criar novos planos.
     */
    public function create(User $user): bool
    {
        return $user->can('plan-create') || $user->can('plan-create-all');
    }

    /**
     * Atualizar um plano específico.
     */
    public function update(User $user, Plane $plane): bool
    {
        // Admin pode editar qualquer plano
        if ($user->can('plan-edit-all')) {
            return true;
        }

        // Profissionais podem editar planos que criaram
        if ($user->can('plan-edit') && $plane->created_by === $user->id) {
            return true;
        }

        // Profissionais podem editar planos de kids vinculados a eles
        if ($user->can('plan-edit')) {
            $professional = $user->professional->first();
            if ($professional && $plane->kid && $plane->kid->professionals->contains($professional->id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enviar plano para a lixeira (soft delete).
     */
    public function delete(User $user, Plane $plane): bool
    {
        // Admin pode deletar qualquer plano
        if ($user->can('plan-delete-all')) {
            return true;
        }

        // Profissionais podem deletar planos que criaram
        if ($user->can('plan-delete') && $plane->created_by === $user->id) {
            return true;
        }

        // Profissionais podem deletar planos de kids vinculados a eles
        if ($user->can('plan-delete')) {
            $professional = $user->professional->first();
            if ($professional && $plane->kid && $plane->kid->professionals->contains($professional->id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Visualizar a lixeira de planos.
     */
    public function viewTrash(User $user): bool
    {
        // Apenas usuários com permissão -all podem ver a lixeira completa
        return $user->can('plan-list-all');
    }

    /**
     * Restaurar um plano.
     */
    public function restore(User $user, Plane $plane): bool
    {
        // Admin pode restaurar qualquer plano
        if ($user->can('plan-edit-all')) {
            return true;
        }

        // Profissionais podem restaurar planos que criaram
        if ($user->can('plan-edit') && $plane->created_by === $user->id) {
            return true;
        }

        // Profissionais podem restaurar planos de kids vinculados a eles
        if ($user->can('plan-edit')) {
            $professional = $user->professional->first();
            if ($professional && $plane->kid && $plane->kid->professionals()->withTrashed()->get()->contains($professional->id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Forçar exclusão permanente.
     */
    public function forceDelete(User $user, Plane $plane): bool
    {
        return $user->can('plan-delete-all');
    }
}
