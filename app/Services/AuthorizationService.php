<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Kid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuthorizationService
{
    public function canAccessResource(string $permission, ?Model $resource = null): bool
    {
        $user = Auth::user();

        if (!$user || !$user->can($permission)) {
            return false;
        }

        // Se não há resource específico, a permissão geral já é suficiente
        if (!$resource) {
            return true;
        }

        // Aplicar contexto específico baseado no tipo do resource
        return $this->hasResourceContext($user, $resource);
    }

    public function getAccessibleResourcesQuery(User $user, string $modelClass)
    {
        return match ($modelClass) {
            Kid::class => $this->getAccessibleKidsQuery($user),
            default => $modelClass::query()
        };
    }

    private function hasResourceContext(User $user, Model $resource): bool
    {
        return match (get_class($resource)) {
            Kid::class => $this->hasKidContext($user, $resource),
            default => true // Para outros recursos, permitir por padrão se tem a permissão
        };
    }

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

        // 4. Usuário tem permissão de gerenciar todos os recursos (admin/superadmin)
        if ($user->can('manage all resources')) {
            return true;
        }

        return false;
    }

    private function isUserAssociatedWithKid(User $user, Kid $kid): bool
    {
        $professional = $user->professional->first();
        
        if (!$professional) {
            return false;
        }

        return $kid->professionals->contains('id', $professional->id);
    }

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

    public function canPerformAction(string $action, string $resource, ?Model $instance = null): bool
    {
        $permission = "{$action} {$resource}";
        return $this->canAccessResource($permission, $instance);
    }

    // Métodos helper para ações específicas
    public function canList(string $resource): bool
    {
        return $this->canPerformAction('list', $resource);
    }

    public function canView(string $resource, ?Model $instance = null): bool
    {
        return $this->canPerformAction('view', $resource, $instance);
    }

    public function canCreate(string $resource): bool
    {
        return $this->canPerformAction('create', $resource);
    }

    public function canEdit(string $resource, ?Model $instance = null): bool
    {
        return $this->canPerformAction('edit', $resource, $instance);
    }

    public function canRemove(string $resource, ?Model $instance = null): bool
    {
        return $this->canPerformAction('remove', $resource, $instance);
    }
}