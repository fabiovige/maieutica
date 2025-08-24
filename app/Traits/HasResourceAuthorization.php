<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Kid;
use App\Services\AuthorizationService;
use Illuminate\Database\Eloquent\Model;

trait HasResourceAuthorization
{
    private function getAuthorizationService(): AuthorizationService
    {
        return app(AuthorizationService::class);
    }

    public function canAccessResource(string $permission, ?Model $resource = null): bool
    {
        return $this->getAuthorizationService()->canAccessResource($permission, $resource);
    }

    public function canListKids(): bool
    {
        return $this->can('list kids');
    }

    public function canViewKid(?Kid $kid = null): bool
    {
        return $this->canAccessResource('view kids', $kid);
    }

    public function canCreateKids(): bool
    {
        return $this->can('create kids');
    }

    public function canEditKid(?Kid $kid = null): bool
    {
        return $this->canAccessResource('edit kids', $kid);
    }

    public function canRemoveKid(?Kid $kid = null): bool
    {
        return $this->canAccessResource('remove kids', $kid);
    }

    public function getAccessibleKidsQuery()
    {
        return $this->getAuthorizationService()->getAccessibleResourcesQuery($this, Kid::class);
    }

    // Métodos genéricos para qualquer recurso
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
}