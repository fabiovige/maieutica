<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Kid;
use Illuminate\Database\Eloquent\Model;

class ResourceContextService
{
    /**
     * Determina se o usuário tem contexto para acessar um recurso específico
     */
    public function hasResourceContext(User $user, Model $resource): bool
    {
        return match (get_class($resource)) {
            Kid::class => $this->hasKidContext($user, $resource),
            default => true // Para outros recursos, permitir por padrão se tem a permissão
        };
    }

    /**
     * Obter query de recursos acessíveis baseado no contexto do usuário
     */
    public function getAccessibleResourcesQuery(User $user, string $modelClass)
    {
        return match ($modelClass) {
            Kid::class => $this->getAccessibleKidsQuery($user),
            default => $modelClass::query()
        };
    }

    /**
     * Contexto para Kids: usuário tem acesso se é responsável, criou, é profissional associado, ou tem permissão total
     */
    private function hasKidContext(User $user, Kid $kid): bool
    {
        // 1. Usuário é o responsável pela criança
        if ($kid->responsible_id === $user->id) {
            return true;
        }

        // 2. Usuário criou a criança
        if ($kid->created_by === $user->id) {
            return true;
        }

        // 3. Usuário é um profissional associado à criança
        if ($this->isUserAssociatedWithKid($user, $kid)) {
            return true;
        }

        // 4. Usuário tem permissão de gerenciar todos os recursos (admin/superadmin/qualquer role com essa permissão)
        if ($user->can('manage all resources')) {
            return true;
        }

        return false;
    }

    /**
     * Query de crianças acessíveis baseada no contexto do usuário
     */
    private function getAccessibleKidsQuery(User $user)
    {
        $query = Kid::query();

        // Se tem permissão para gerenciar todos os recursos, retorna tudo
        if ($user->can('manage all resources')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            // Crianças onde é responsável
            $q->where('responsible_id', $user->id)
              // OU crianças que criou
              ->orWhere('created_by', $user->id);

            // OU crianças onde é profissional associado
            $professional = $user->professional->first();
            if ($professional) {
                $q->orWhereHas('professionals', function ($query) use ($professional) {
                    $query->where('professional_id', $professional->id);
                });
            }
        });
    }

    private function isUserAssociatedWithKid(User $user, Kid $kid): bool
    {
        $professional = $user->professional->first();

        if (!$professional) {
            return false;
        }

        return $kid->professionals->contains('id', $professional->id);
    }
}
