<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Kid;
use App\Services\AuthorizationService;
use App\Services\ResourceContextService;
use Illuminate\Database\Eloquent\Model;

trait HasResourceAuthorization
{
    private function getAuthorizationService(): AuthorizationService
    {
        return app(AuthorizationService::class);
    }

    private function getResourceContextService(): ResourceContextService
    {
        return app(ResourceContextService::class);
    }

    public function canAccessResource(string $permission, ?Model $resource = null): bool
    {
        return $this->getAuthorizationService()->canAccessResource($permission, $resource);
    }

    // Métodos genéricos baseados puramente em permissões + contexto
    public function canListResource(string $resource): bool
    {
        return $this->can("list {$resource}");
    }

    public function canViewResource(string $resource, ?Model $instance = null): bool
    {
        return $this->canAccessResource("view {$resource}", $instance);
    }

    public function canCreateResource(string $resource): bool
    {
        return $this->can("create {$resource}");
    }

    public function canEditResource(string $resource, ?Model $instance = null): bool
    {
        return $this->canAccessResource("edit {$resource}", $instance);
    }

    public function canRemoveResource(string $resource, ?Model $instance = null): bool
    {
        return $this->canAccessResource("remove {$resource}", $instance);
    }

    public function getAccessibleResourcesQuery(string $modelClass)
    {
        return $this->getResourceContextService()->getAccessibleResourcesQuery($this, $modelClass);
    }

    // Métodos de conveniência para Kids (mais usados)
    public function canListKids(): bool
    {
        return $this->canListResource('kids');
    }

    public function canViewKid(?Kid $kid = null): bool
    {
        return $this->canViewResource('kids', $kid);
    }

    public function canCreateKids(): bool
    {
        return $this->canCreateResource('kids');
    }

    public function canEditKid(?Kid $kid = null): bool
    {
        return $this->canEditResource('kids', $kid);
    }

    public function canRemoveKid(?Kid $kid = null): bool
    {
        return $this->canRemoveResource('kids', $kid);
    }

    public function getAccessibleKidsQuery()
    {
        return $this->getAccessibleResourcesQuery(Kid::class);
    }
}