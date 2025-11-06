<?php

namespace App\Services\Logging;

use App\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Centralized logging service for Role (Perfil) operations.
 *
 * All logs include contextual information for debugging, security audit, and compliance.
 * Follows the same pattern as KidLogger, UserLogger, ChecklistLogger, and ProfessionalLogger.
 */
class RoleLogger
{
    /**
     * Log when a role is created.
     *
     * @param Role $role
     * @param array $additionalContext
     * @return void
     */
    public function created(Role $role, array $additionalContext = []): void
    {
        Log::notice('Role (perfil) criado', array_merge([
            'role_id' => $role->id,
            'role_name' => $role->name,
            'guard_name' => $role->guard_name,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a role is updated.
     *
     * @param Role $role
     * @param array $changes Array of changed fields ['field' => ['old' => ..., 'new' => ...]]
     * @param array $additionalContext
     * @return void
     */
    public function updated(Role $role, array $changes = [], array $additionalContext = []): void
    {
        $changedFields = !empty($changes) ? array_keys($changes) : [];

        Log::notice('Role (perfil) atualizado', array_merge([
            'role_id' => $role->id,
            'role_name' => $role->name,
            'changed_fields' => $changedFields,
            'changes' => $changes,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a role is soft deleted (moved to trash).
     *
     * @param Role $role
     * @param array $additionalContext
     * @return void
     */
    public function deleted(Role $role, array $additionalContext = []): void
    {
        Log::notice('Role (perfil) movido para lixeira', array_merge([
            'role_id' => $role->id,
            'role_name' => $role->name,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a role is restored from trash.
     *
     * @param Role $role
     * @param array $additionalContext
     * @return void
     */
    public function restored(Role $role, array $additionalContext = []): void
    {
        Log::notice('Role (perfil) restaurado da lixeira', array_merge([
            'role_id' => $role->id,
            'role_name' => $role->name,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a role is permanently deleted.
     *
     * @param Role $role
     * @param array $additionalContext
     * @return void
     */
    public function forceDeleted(Role $role, array $additionalContext = []): void
    {
        Log::alert('Role (perfil) excluído permanentemente', array_merge([
            'role_id' => $role->id,
            'role_name' => $role->name,
            'warning' => 'Role permanentemente deletado do banco de dados',
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a permission is assigned to a role.
     *
     * @param Role $role
     * @param string $permissionName
     * @param array $additionalContext
     * @return void
     */
    public function permissionAssigned(Role $role, string $permissionName, array $additionalContext = []): void
    {
        Log::notice('Permissão atribuída ao role', array_merge([
            'role_id' => $role->id,
            'role_name' => $role->name,
            'permission_name' => $permissionName,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a permission is removed from a role.
     *
     * @param Role $role
     * @param string $permissionName
     * @param array $additionalContext
     * @return void
     */
    public function permissionRemoved(Role $role, string $permissionName, array $additionalContext = []): void
    {
        Log::notice('Permissão removida do role', array_merge([
            'role_id' => $role->id,
            'role_name' => $role->name,
            'permission_name' => $permissionName,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when permissions are synchronized with a role.
     *
     * @param Role $role
     * @param array $oldPermissions
     * @param array $newPermissions
     * @param array $additionalContext
     * @return void
     */
    public function permissionsSynced(Role $role, array $oldPermissions, array $newPermissions, array $additionalContext = []): void
    {
        $added = array_diff($newPermissions, $oldPermissions);
        $removed = array_diff($oldPermissions, $newPermissions);

        Log::notice('Permissões sincronizadas no role', array_merge([
            'role_id' => $role->id,
            'role_name' => $role->name,
            'old_permissions_count' => count($oldPermissions),
            'new_permissions_count' => count($newPermissions),
            'added_permissions' => array_values($added),
            'removed_permissions' => array_values($removed),
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a user is assigned to a role.
     *
     * @param Role $role
     * @param int $userId
     * @param array $additionalContext
     * @return void
     */
    public function userAssigned(Role $role, int $userId, array $additionalContext = []): void
    {
        Log::notice('Usuário atribuído ao role', array_merge([
            'role_id' => $role->id,
            'role_name' => $role->name,
            'user_id' => $userId,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a user is removed from a role.
     *
     * @param Role $role
     * @param int $userId
     * @param array $additionalContext
     * @return void
     */
    public function userRemoved(Role $role, int $userId, array $additionalContext = []): void
    {
        Log::notice('Usuário removido do role', array_merge([
            'role_id' => $role->id,
            'role_name' => $role->name,
            'user_id' => $userId,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a role is viewed (details page).
     *
     * @param Role $role
     * @param string $viewType Type of view (details, edit, abilities, etc.)
     * @param array $additionalContext
     * @return void
     */
    public function viewed(Role $role, string $viewType = 'details', array $additionalContext = []): void
    {
        Log::info('Role (perfil) visualizado', array_merge([
            'viewed_role_id' => $role->id,
            'role_name' => $role->name,
            'view_type' => $viewType,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when roles list/index is accessed.
     *
     * @param array $filters Applied filters
     * @param array $additionalContext
     * @return void
     */
    public function listed(array $filters = [], array $additionalContext = []): void
    {
        Log::debug('Lista de roles (perfis) acessada', array_merge([
            'filters' => $filters,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when trash/deleted roles list is accessed.
     *
     * @param array $additionalContext
     * @return void
     */
    public function trashViewed(array $additionalContext = []): void
    {
        Log::info('Lixeira de roles (perfis) visualizada', array_merge(
            $this->buildUserContext(),
            $additionalContext
        ));
    }

    /**
     * Log when an operation fails.
     *
     * @param string $operation Operation name (store, update, delete, etc.)
     * @param \Exception $exception
     * @param array $additionalContext
     * @return void
     */
    public function operationFailed(string $operation, \Exception $exception, array $additionalContext = []): void
    {
        Log::error("Operação de role (perfil) falhou: {$operation}", array_merge([
            'operation' => $operation,
            'error' => $exception->getMessage(),
            'exception_class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when access is denied to a role operation.
     *
     * @param string $operation
     * @param Role|null $role
     * @param array $additionalContext
     * @return void
     */
    public function accessDenied(string $operation, ?Role $role = null, array $additionalContext = []): void
    {
        $context = [
            'operation' => $operation,
        ];

        if ($role) {
            $context['target_role_id'] = $role->id;
            $context['role_name'] = $role->name;
        }

        Log::warning('Acesso negado à operação de role (perfil)', array_merge(
            $context,
            $this->buildUserContext(),
            $additionalContext
        ));
    }

    /**
     * Build user context for logging.
     *
     * @return array
     */
    private function buildUserContext(): array
    {
        if (!Auth::check()) {
            return [
                'actor_user_id' => null,
                'actor_user_name' => 'Guest',
                'ip' => request()->ip(),
            ];
        }

        $user = Auth::user();

        return [
            'actor_user_id' => $user->id,
            'actor_user_name' => $user->name,
            'actor_user_email' => $user->email,
            'ip' => request()->ip(),
        ];
    }
}
