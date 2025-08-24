<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuthorizationService
{
    public function __construct(
        private readonly ResourceContextService $contextService
    ) {
    }

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
        return $this->contextService->hasResourceContext($user, $resource);
    }

    public function getAccessibleResourcesQuery($user, string $modelClass)
    {
        return $this->contextService->getAccessibleResourcesQuery($user, $modelClass);
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