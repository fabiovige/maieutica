<?php

namespace App\Policies;

use App\Models\Competence;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompetencePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        // Verifica se o usuário tem a permissão de listar competences
        return $user->can('list competences');
    }

    public function update(User $user, Competence $competence): bool
    {
        // Permite atualizar competences
        return $user->can('edit competences');
    }
}
