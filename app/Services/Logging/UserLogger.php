<?php

namespace App\Services\Logging;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Centralized logging service for User operations.
 *
 * All logs include contextual information for debugging, security audit, and compliance.
 * Sensitive fields (passwords, tokens) are masked in logs.
 */
class UserLogger
{
    /**
     * Log when a user is created.
     *
     * @param User $user
     * @param array $additionalContext
     * @return void
     */
    public function created(User $user, array $additionalContext = []): void
    {
        Log::notice('User created', array_merge([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->name,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a user is updated.
     *
     * @param User $user
     * @param array $changes Array of changed fields ['field' => ['old' => ..., 'new' => ...]]
     * @param array $additionalContext
     * @return void
     */
    public function updated(User $user, array $changes = [], array $additionalContext = []): void
    {
        $changedFields = !empty($changes) ? array_keys($changes) : [];

        Log::notice('User updated', array_merge([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'changed_fields' => $changedFields,
            'changes' => $this->sanitizeChanges($changes),
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a user is soft deleted (moved to trash).
     *
     * @param User $user
     * @param array $additionalContext
     * @return void
     */
    public function deleted(User $user, array $additionalContext = []): void
    {
        Log::notice('User moved to trash', array_merge([
            'user_id' => $user->id,
            'user_email' => $user->email,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a user is restored from trash.
     *
     * @param User $user
     * @param array $additionalContext
     * @return void
     */
    public function restored(User $user, array $additionalContext = []): void
    {
        Log::notice('User restored from trash', array_merge([
            'user_id' => $user->id,
            'user_email' => $user->email,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a user successfully logs in.
     *
     * @param User $user
     * @param array $additionalContext
     * @return void
     */
    public function login(User $user, array $additionalContext = []): void
    {
        Log::info('User logged in', array_merge([
            'user_id' => $user->id,
            'user_email' => $user->email,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a user logs out.
     *
     * @param User $user
     * @param array $additionalContext
     * @return void
     */
    public function logout(User $user, array $additionalContext = []): void
    {
        Log::info('User logged out', array_merge([
            'user_id' => $user->id,
            'user_email' => $user->email,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a login attempt fails.
     *
     * @param string $email
     * @param array $additionalContext
     * @return void
     */
    public function loginFailed(string $email, array $additionalContext = []): void
    {
        Log::warning('Login failed', array_merge([
            'attempted_email' => $email,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a user changes their password.
     *
     * @param User $user
     * @param array $additionalContext
     * @return void
     */
    public function passwordChanged(User $user, array $additionalContext = []): void
    {
        Log::notice('User password changed', array_merge([
            'user_id' => $user->id,
            'user_email' => $user->email,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a user requests a password reset.
     *
     * @param User $user
     * @param array $additionalContext
     * @return void
     */
    public function passwordResetRequested(User $user, array $additionalContext = []): void
    {
        Log::info('User requested password reset', array_merge([
            'user_id' => $user->id,
            'user_email' => $user->email,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a user is viewed (details page).
     *
     * @param User $user
     * @param string $viewType Type of view (details, edit, profile, etc.)
     * @param array $additionalContext
     * @return void
     */
    public function viewed(User $user, string $viewType = 'details', array $additionalContext = []): void
    {
        Log::info('User viewed', array_merge([
            'viewed_user_id' => $user->id,
            'viewed_user_email' => $user->email,
            'view_type' => $viewType,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when users list/index is accessed.
     *
     * @param array $filters Applied filters
     * @param array $additionalContext
     * @return void
     */
    public function listed(array $filters = [], array $additionalContext = []): void
    {
        Log::debug('Users list accessed', array_merge([
            'filters' => $filters,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when trash/deleted users list is accessed.
     *
     * @param array $additionalContext
     * @return void
     */
    public function trashViewed(array $additionalContext = []): void
    {
        Log::info('Users trash viewed', array_merge(
            $this->buildUserContext(),
            $additionalContext
        ));
    }

    /**
     * Log when a role is assigned to a user.
     *
     * @param User $user
     * @param string $roleName
     * @param array $additionalContext
     * @return void
     */
    public function roleAssigned(User $user, string $roleName, array $additionalContext = []): void
    {
        Log::notice('Role assigned to user', array_merge([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'role_name' => $roleName,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a role is removed from a user.
     *
     * @param User $user
     * @param string $roleName
     * @param array $additionalContext
     * @return void
     */
    public function roleRemoved(User $user, string $roleName, array $additionalContext = []): void
    {
        Log::notice('Role removed from user', array_merge([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'role_name' => $roleName,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a professional is linked to a user.
     *
     * @param User $user
     * @param int $professionalId
     * @param array $additionalContext
     * @return void
     */
    public function professionalLinked(User $user, int $professionalId, array $additionalContext = []): void
    {
        Log::notice('Professional linked to user', array_merge([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'professional_id' => $professionalId,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a professional is unlinked from a user.
     *
     * @param User $user
     * @param int $professionalId
     * @param array $additionalContext
     * @return void
     */
    public function professionalUnlinked(User $user, int $professionalId, array $additionalContext = []): void
    {
        Log::notice('Professional unlinked from user', array_merge([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'professional_id' => $professionalId,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a user account is activated.
     *
     * @param User $user
     * @param array $additionalContext
     * @return void
     */
    public function activated(User $user, array $additionalContext = []): void
    {
        Log::notice('User account activated', array_merge([
            'user_id' => $user->id,
            'user_email' => $user->email,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a user account is deactivated.
     *
     * @param User $user
     * @param array $additionalContext
     * @return void
     */
    public function deactivated(User $user, array $additionalContext = []): void
    {
        Log::alert('User account deactivated', array_merge([
            'user_id' => $user->id,
            'user_email' => $user->email,
        ], $this->buildUserContext(), $additionalContext));
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
        Log::error("User operation failed: {$operation}", array_merge([
            'operation' => $operation,
            'error' => $exception->getMessage(),
            'exception_class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when access is denied to a user operation.
     *
     * @param string $operation
     * @param User|null $user
     * @param array $additionalContext
     * @return void
     */
    public function accessDenied(string $operation, ?User $user = null, array $additionalContext = []): void
    {
        $context = [
            'operation' => $operation,
        ];

        if ($user) {
            $context['target_user_id'] = $user->id;
            $context['target_user_email'] = $user->email;
        }

        Log::warning('Access denied to user operation', array_merge(
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

    /**
     * Sanitize changes array to avoid logging sensitive data.
     *
     * Masks sensitive fields like passwords, tokens.
     *
     * @param array $changes
     * @return array
     */
    private function sanitizeChanges(array $changes): array
    {
        $sanitized = [];

        // Campos que devem ser logados apenas como "[HIDDEN]" sem valores
        $sensitiveFields = ['password', 'remember_token', 'temporaryPassword'];

        foreach ($changes as $field => $values) {
            if (in_array($field, $sensitiveFields)) {
                $sanitized[$field] = '[HIDDEN]';
            } else {
                $sanitized[$field] = $values;
            }
        }

        return $sanitized;
    }
}
