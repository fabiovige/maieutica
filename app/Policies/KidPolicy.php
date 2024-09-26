<?php

namespace App\Policies;

use App\Models\Kid;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KidPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Kid $kid)
    {
        // Permite que um administrador veja qualquer criança
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        // Se o usuário tiver o papel de profissional, verifica se ele está associado à criança
        if($user->isProfessional()){
            return $user->id === $kid->profession_id;
        }

        // Caso contrário, não tem permissão
        return false;
    }

    public function create(User $user)
    {
        // Permite que um administrador crie uma criança
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        // Se o usuário tiver o papel de profissional, ele pode criar uma criança
        if($user->isProfessional()){
            return $user->hasPermissionTo('kids.store');
        }

        // Caso contrário, não tem permissão
        return false;
    }

    public function update(User $user, Kid $kid)
    {
        // Permite que um administrador atualize qualquer criança
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        // Se o usuário tiver o papel de profissional, verifica se ele está associado à criança
        if($user->isProfessional()){
            return $user->id === $kid->profession_id;
        }

        // Caso contrário, não tem permissão
        return false;
    }
}
