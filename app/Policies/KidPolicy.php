<?php

namespace App\Policies;

use App\Models\Kid;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KidPolicy
{
    use HandlesAuthorization;

    /**
     * Listar crianças.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('kid-list') || $user->can('kid-list-all');
    }

    /**
     * Visualizar uma criança específica.
     */
    public function view(User $user, Kid $kid): bool
    {
        // Pode visualizar se tem permissão
        if ($user->can('kid-show') || $user->can('kid-show-all')) {
            return true;
        }

        // Profissionais podem visualizar kids vinculados a eles
        $professional = $user->professional->first();
        if ($professional && $kid->professionals->contains($professional->id)) {
            return true;
        }

        // Responsável pode visualizar kids sob sua responsabilidade
        return $user->id === $kid->responsible_id;
    }

    /**
     * Criar novas crianças.
     */
    public function create(User $user): bool
    {
        return $user->can('kid-create') || $user->can('kid-create-all');
    }

    /**
     * Atualizar uma criança específica.
     */
    public function update(User $user, Kid $kid): bool
    {
        // Pode editar se tem permissão
        if ($user->can('kid-edit') || $user->can('kid-edit-all')) {
            return true;
        }

        // Profissionais podem editar kids vinculados a eles
        $professional = $user->professional->first();
        if ($professional && $kid->professionals->contains($professional->id)) {
            return true;
        }

        // Quem criou pode editar
        return $user->id === $kid->created_by;
    }

    /**
     * Enviar criança para a lixeira (soft delete).
     */
    public function delete(User $user, Kid $kid): bool
    {
        // Admin pode deletar qualquer kid
        if ($user->can('kid-delete-all')) {
            return true;
        }

        // Profissionais só podem deletar kids vinculados a eles
        if ($user->can('kid-delete')) {
            $professional = $user->professional->first();
            if ($professional && $kid->professionals->contains($professional->id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Visualizar a lixeira de crianças.
     */
    public function viewTrash(User $user): bool
    {
        // Apenas usuários com permissão -all podem ver a lixeira completa
        return $user->can('kid-list-all');
    }

    /**
     * Restaurar uma criança.
     */
    public function restore(User $user, Kid $kid): bool
    {
        // Admin pode restaurar qualquer kid
        if ($user->can('kid-edit-all')) {
            return true;
        }

        // Profissionais só podem restaurar kids vinculados a eles
        if ($user->can('kid-edit')) {
            $professional = $user->professional->first();
            if ($professional && $kid->professionals()->withTrashed()->get()->contains($professional->id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Forçar exclusão permanente.
     */
    public function forceDelete(User $user, Kid $kid): bool
    {
        return $user->can('kid-delete-all');
    }
}
