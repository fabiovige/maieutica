<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function getAllRoles(): Collection
    {
        return Role::with('permissions')->get();
    }

    public function createRole(string $name, string $displayName, array $permissions = []): Role
    {
        $role = Role::create([
            'name' => $name,
            'guard_name' => 'web'
        ]);

        if (!empty($permissions)) {
            $this->assignPermissionsToRole($role, $permissions);
        }

        return $role;
    }

    public function assignPermissionsToRole(Role $role, array $permissions): void
    {
        $validPermissions = Permission::whereIn('name', $permissions)->pluck('name')->toArray();
        $role->syncPermissions($validPermissions);
    }

    public function getAvailablePermissions(): Collection
    {
        return Permission::all();
    }

    public function getRoleByName(string $name): ?Role
    {
        return Role::where('name', $name)->first();
    }

    public function updateRolePermissions(string $roleName, array $permissions): bool
    {
        $role = $this->getRoleByName($roleName);
        
        if (!$role) {
            return false;
        }

        $this->assignPermissionsToRole($role, $permissions);
        return true;
    }

    public function getUserRoleEnum(string $roleName): ?UserRole
    {
        return UserRole::getByName($roleName);
    }

    public function getRoleDisplayName(string $roleName): string
    {
        $roleEnum = $this->getUserRoleEnum($roleName);
        return $roleEnum ? $roleEnum->label() : $roleName;
    }

    public function deleteRole(string $roleName): bool
    {
        $role = $this->getRoleByName($roleName);
        
        if (!$role) {
            return false;
        }

        // Verificar se não é uma role padrão do sistema
        if (in_array($roleName, ['superadmin', 'admin', 'professional', 'pais'])) {
            throw new \Exception('Não é possível excluir roles padrão do sistema.');
        }

        return $role->delete();
    }

    public function getSystemRoles(): array
    {
        return [
            'superadmin' => 'Super Administrador',
            'admin' => 'Administrador', 
            'professional' => 'Profissional',
            'pais' => 'Pais/Responsável'
        ];
    }

    public function isSystemRole(string $roleName): bool
    {
        return array_key_exists($roleName, $this->getSystemRoles());
    }

    public function canCreateNewRole(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function getPermissionsForRole(UserRole $role): array
    {
        return match($role) {
            UserRole::SUPERADMIN => Permission::all()->pluck('name')->toArray(),
            UserRole::ADMIN => [
                'list users', 'view users', 'create users', 'edit users', 'remove users',
                'list kids', 'view kids', 'create kids', 'edit kids', 'remove kids',
                'list planes', 'view planes', 'create planes', 'edit planes', 'remove planes',
                'list roles', 'view roles', 'create roles', 'edit roles', 'remove roles',
                'list checklists', 'view checklists', 'create checklists', 'edit checklists', 'remove checklists',
                'fill checklists', 'clone checklists', 'plane automatic checklist', 'plane manual checklist', 'avaliation checklist',
                'list professionals', 'view professionals', 'create professionals', 'edit professionals', 'remove professionals',
                'activate professionals', 'deactivate professionals', 'list competences', 'edit competences', 'manage dashboard'
            ],
            UserRole::PROFESSIONAL => [
                'list kids', 'view kids', 'create kids', 'edit kids',
                'list planes', 'view planes', 'create planes', 'edit planes', 'remove planes',
                'list checklists', 'create checklists', 'edit checklists', 'clone checklists',
                'plane automatic checklist', 'plane manual checklist', 'avaliation checklist',
                'list competences', 'edit competences'
            ],
            UserRole::PARENT => [
                'list kids', 'view kids', 'edit kids', 'list checklists'
            ],
        };
    }
}