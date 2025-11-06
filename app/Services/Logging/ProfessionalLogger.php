<?php

namespace App\Services\Logging;

use App\Models\Professional;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Centralized logging service for Professional operations.
 *
 * All logs include contextual information for debugging, security audit, and compliance.
 * Sensitive fields (passwords, tokens) are masked in logs.
 * Follows the same pattern as KidLogger, UserLogger, and ChecklistLogger.
 */
class ProfessionalLogger
{
    /**
     * Log when a professional is created.
     *
     * @param Professional $professional
     * @param array $additionalContext
     * @return void
     */
    public function created(Professional $professional, array $additionalContext = []): void
    {
        Log::notice('Professional criado', array_merge([
            'professional_id' => $professional->id,
            'specialty_id' => $professional->specialty_id,
            'specialty_name' => $professional->specialty->name ?? 'N/A',
            'registration_number' => $professional->registration_number,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a professional is updated.
     *
     * @param Professional $professional
     * @param array $changes Array of changed fields ['field' => ['old' => ..., 'new' => ...]]
     * @param array $additionalContext
     * @return void
     */
    public function updated(Professional $professional, array $changes = [], array $additionalContext = []): void
    {
        $changedFields = !empty($changes) ? array_keys($changes) : [];

        Log::notice('Professional atualizado', array_merge([
            'professional_id' => $professional->id,
            'specialty_id' => $professional->specialty_id,
            'specialty_name' => $professional->specialty->name ?? 'N/A',
            'changed_fields' => $changedFields,
            'changes' => $this->sanitizeChanges($changes),
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a professional is soft deleted (moved to trash).
     *
     * @param Professional $professional
     * @param array $additionalContext
     * @return void
     */
    public function deleted(Professional $professional, array $additionalContext = []): void
    {
        Log::notice('Professional movido para lixeira', array_merge([
            'professional_id' => $professional->id,
            'specialty_id' => $professional->specialty_id,
            'registration_number' => $professional->registration_number,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a professional is restored from trash.
     *
     * @param Professional $professional
     * @param array $additionalContext
     * @return void
     */
    public function restored(Professional $professional, array $additionalContext = []): void
    {
        Log::notice('Professional restaurado da lixeira', array_merge([
            'professional_id' => $professional->id,
            'specialty_id' => $professional->specialty_id,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a professional is permanently deleted.
     *
     * @param Professional $professional
     * @param array $additionalContext
     * @return void
     */
    public function forceDeleted(Professional $professional, array $additionalContext = []): void
    {
        Log::alert('Professional excluído permanentemente', array_merge([
            'professional_id' => $professional->id,
            'registration_number' => $professional->registration_number,
            'warning' => 'Professional permanentemente deletado do banco de dados',
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a user is linked to a professional.
     *
     * @param Professional $professional
     * @param int $userId
     * @param array $additionalContext
     * @return void
     */
    public function userLinked(Professional $professional, int $userId, array $additionalContext = []): void
    {
        Log::notice('User vinculado ao professional', array_merge([
            'professional_id' => $professional->id,
            'user_id' => $userId,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a user is unlinked from a professional.
     *
     * @param Professional $professional
     * @param int $userId
     * @param array $additionalContext
     * @return void
     */
    public function userUnlinked(Professional $professional, int $userId, array $additionalContext = []): void
    {
        Log::notice('User desvinculado do professional', array_merge([
            'professional_id' => $professional->id,
            'user_id' => $userId,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a kid is linked to a professional.
     *
     * @param Professional $professional
     * @param int $kidId
     * @param array $additionalContext
     * @return void
     */
    public function kidLinked(Professional $professional, int $kidId, array $additionalContext = []): void
    {
        Log::notice('Criança vinculada ao professional', array_merge([
            'professional_id' => $professional->id,
            'kid_id' => $kidId,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a kid is unlinked from a professional.
     *
     * @param Professional $professional
     * @param int $kidId
     * @param array $additionalContext
     * @return void
     */
    public function kidUnlinked(Professional $professional, int $kidId, array $additionalContext = []): void
    {
        Log::notice('Criança desvinculada do professional', array_merge([
            'professional_id' => $professional->id,
            'kid_id' => $kidId,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a professional account is activated.
     *
     * @param Professional $professional
     * @param array $additionalContext
     * @return void
     */
    public function activated(Professional $professional, array $additionalContext = []): void
    {
        Log::notice('Professional ativado', array_merge([
            'professional_id' => $professional->id,
            'specialty_id' => $professional->specialty_id,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a professional account is deactivated.
     *
     * @param Professional $professional
     * @param array $additionalContext
     * @return void
     */
    public function deactivated(Professional $professional, array $additionalContext = []): void
    {
        Log::alert('Professional desativado', array_merge([
            'professional_id' => $professional->id,
            'specialty_id' => $professional->specialty_id,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a professional's specialty is changed.
     *
     * @param Professional $professional
     * @param int $oldSpecialtyId
     * @param int $newSpecialtyId
     * @param array $additionalContext
     * @return void
     */
    public function specialtyChanged(Professional $professional, int $oldSpecialtyId, int $newSpecialtyId, array $additionalContext = []): void
    {
        Log::notice('Especialidade do professional alterada', array_merge([
            'professional_id' => $professional->id,
            'old_specialty_id' => $oldSpecialtyId,
            'new_specialty_id' => $newSpecialtyId,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a professional is viewed (details page).
     *
     * @param Professional $professional
     * @param string $viewType Type of view (details, edit, profile, etc.)
     * @param array $additionalContext
     * @return void
     */
    public function viewed(Professional $professional, string $viewType = 'details', array $additionalContext = []): void
    {
        Log::info('Professional visualizado', array_merge([
            'viewed_professional_id' => $professional->id,
            'specialty_id' => $professional->specialty_id,
            'view_type' => $viewType,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when professionals list/index is accessed.
     *
     * @param array $filters Applied filters
     * @param array $additionalContext
     * @return void
     */
    public function listed(array $filters = [], array $additionalContext = []): void
    {
        Log::debug('Lista de professionals acessada', array_merge([
            'filters' => $filters,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when trash/deleted professionals list is accessed.
     *
     * @param array $additionalContext
     * @return void
     */
    public function trashViewed(array $additionalContext = []): void
    {
        Log::info('Lixeira de professionals visualizada', array_merge(
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
        Log::error("Operação de professional falhou: {$operation}", array_merge([
            'operation' => $operation,
            'error' => $exception->getMessage(),
            'exception_class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when access is denied to a professional operation.
     *
     * @param string $operation
     * @param Professional|null $professional
     * @param array $additionalContext
     * @return void
     */
    public function accessDenied(string $operation, ?Professional $professional = null, array $additionalContext = []): void
    {
        $context = [
            'operation' => $operation,
        ];

        if ($professional) {
            $context['target_professional_id'] = $professional->id;
            $context['specialty_id'] = $professional->specialty_id;
        }

        Log::warning('Acesso negado à operação de professional', array_merge(
            $context,
            $this->buildUserContext(),
            $additionalContext
        ));
    }

    /**
     * Log when a role is missing during professional creation.
     *
     * @param int $userId
     * @param string $roleName
     * @param array $additionalContext
     * @return void
     */
    public function roleMissing(int $userId, string $roleName, array $additionalContext = []): void
    {
        Log::warning('Role não encontrada durante criação de professional', array_merge([
            'user_id' => $userId,
            'role_name' => $roleName,
        ], $this->buildUserContext(), $additionalContext));
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
        $sensitiveFields = ['password', 'remember_token', 'api_token'];

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
