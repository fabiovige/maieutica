<?php

namespace App\Services\Logging;

use App\Models\Kid;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Centralized logging service for Kid operations.
 *
 * Privacy (LGPD): Uses kid initials instead of full names to protect personal data.
 * All logs include contextual information for debugging and audit trails.
 */
class KidLogger
{
    /**
     * Log when a kid is created.
     *
     * @param Kid $kid
     * @param array $additionalContext
     * @return void
     */
    public function created(Kid $kid, array $additionalContext = []): void
    {
        Log::notice('Kid created', array_merge([
            'kid_id' => $kid->id,
            'kid_initials' => $kid->initials,
            'age_months' => $kid->months,
            'responsible_id' => $kid->responsible_id,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a kid is updated.
     *
     * @param Kid $kid
     * @param array $changes Array of changed fields ['field' => ['old' => ..., 'new' => ...]]
     * @param array $additionalContext
     * @return void
     */
    public function updated(Kid $kid, array $changes = [], array $additionalContext = []): void
    {
        $changedFields = !empty($changes) ? array_keys($changes) : [];

        Log::notice('Kid updated', array_merge([
            'kid_id' => $kid->id,
            'kid_initials' => $kid->initials,
            'changed_fields' => $changedFields,
            'changes' => $this->sanitizeChanges($changes),
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a kid is soft deleted (moved to trash).
     *
     * @param Kid $kid
     * @param array $additionalContext
     * @return void
     */
    public function deleted(Kid $kid, array $additionalContext = []): void
    {
        Log::notice('Kid moved to trash', array_merge([
            'kid_id' => $kid->id,
            'kid_initials' => $kid->initials,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a kid is restored from trash.
     *
     * @param Kid $kid
     * @param array $additionalContext
     * @return void
     */
    public function restored(Kid $kid, array $additionalContext = []): void
    {
        Log::notice('Kid restored from trash', array_merge([
            'kid_id' => $kid->id,
            'kid_initials' => $kid->initials,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a kid is viewed (details page).
     *
     * @param Kid $kid
     * @param string $viewType Type of view (details, plane, radar, etc.)
     * @param array $additionalContext
     * @return void
     */
    public function viewed(Kid $kid, string $viewType = 'details', array $additionalContext = []): void
    {
        Log::info('Kid viewed', array_merge([
            'kid_id' => $kid->id,
            'kid_initials' => $kid->initials,
            'view_type' => $viewType,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when kids list/index is accessed.
     *
     * @param array $filters Applied filters
     * @param array $additionalContext
     * @return void
     */
    public function listed(array $filters = [], array $additionalContext = []): void
    {
        Log::debug('Kids list accessed', array_merge([
            'filters' => $filters,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when trash/deleted kids list is accessed.
     *
     * @param array $additionalContext
     * @return void
     */
    public function trashViewed(array $additionalContext = []): void
    {
        Log::info('Kids trash viewed', array_merge(
            $this->buildUserContext(),
            $additionalContext
        ));
    }

    /**
     * Log when a professional is attached to a kid.
     *
     * @param Kid $kid
     * @param int $professionalId
     * @param array $additionalContext
     * @return void
     */
    public function professionalAttached(Kid $kid, int $professionalId, array $additionalContext = []): void
    {
        Log::notice('Professional attached to kid', array_merge([
            'kid_id' => $kid->id,
            'kid_initials' => $kid->initials,
            'professional_id' => $professionalId,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a professional is detached from a kid.
     *
     * @param Kid $kid
     * @param int $professionalId
     * @param array $additionalContext
     * @return void
     */
    public function professionalDetached(Kid $kid, int $professionalId, array $additionalContext = []): void
    {
        Log::notice('Professional detached from kid', array_merge([
            'kid_id' => $kid->id,
            'kid_initials' => $kid->initials,
            'professional_id' => $professionalId,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when kid's photo is uploaded.
     *
     * @param Kid $kid
     * @param string $photoPath
     * @param array $additionalContext
     * @return void
     */
    public function photoUploaded(Kid $kid, string $photoPath, array $additionalContext = []): void
    {
        Log::info('Kid photo uploaded', array_merge([
            'kid_id' => $kid->id,
            'kid_initials' => $kid->initials,
            'photo_path' => $photoPath,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when kid's photo is deleted.
     *
     * @param Kid $kid
     * @param string $photoPath
     * @param array $additionalContext
     * @return void
     */
    public function photoDeleted(Kid $kid, string $photoPath, array $additionalContext = []): void
    {
        Log::info('Kid photo deleted', array_merge([
            'kid_id' => $kid->id,
            'kid_initials' => $kid->initials,
            'photo_path' => $photoPath,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log successful PDF generation.
     *
     * @param Kid $kid
     * @param string $reportType Type of report (plane, overview, etc.)
     * @param array $additionalContext
     * @return void
     */
    public function pdfGenerated(Kid $kid, string $reportType, array $additionalContext = []): void
    {
        Log::info('PDF generated for kid', array_merge([
            'kid_id' => $kid->id,
            'kid_initials' => $kid->initials,
            'report_type' => $reportType,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log failed PDF generation.
     *
     * @param Kid $kid
     * @param string $reportType
     * @param \Exception $exception
     * @param array $additionalContext
     * @return void
     */
    public function pdfGenerationFailed(Kid $kid, string $reportType, \Exception $exception, array $additionalContext = []): void
    {
        Log::error('PDF generation failed', array_merge([
            'kid_id' => $kid->id,
            'kid_initials' => $kid->initials,
            'report_type' => $reportType,
            'error' => $exception->getMessage(),
            'exception_class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
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
        Log::error("Kid operation failed: {$operation}", array_merge([
            'operation' => $operation,
            'error' => $exception->getMessage(),
            'exception_class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when access is denied to a kid operation.
     *
     * @param string $operation
     * @param Kid|null $kid
     * @param array $additionalContext
     * @return void
     */
    public function accessDenied(string $operation, ?Kid $kid = null, array $additionalContext = []): void
    {
        $context = [
            'operation' => $operation,
        ];

        if ($kid) {
            $context['kid_id'] = $kid->id;
            $context['kid_initials'] = $kid->initials;
        }

        Log::warning('Access denied to kid operation', array_merge(
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
                'user_id' => null,
                'user_name' => 'Guest',
                'ip' => request()->ip(),
            ];
        }

        $user = Auth::user();

        return [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'ip' => request()->ip(),
        ];
    }

    /**
     * Sanitize changes array to avoid logging sensitive data.
     *
     * Removes or masks sensitive fields like full names, CPF, addresses.
     *
     * @param array $changes
     * @return array
     */
    private function sanitizeChanges(array $changes): array
    {
        $sanitized = [];

        // Fields que devem ser logados apenas como "changed" sem valores
        $sensitiveFields = ['name', 'photo'];

        foreach ($changes as $field => $values) {
            if (in_array($field, $sensitiveFields)) {
                $sanitized[$field] = '[CHANGED]';
            } else {
                $sanitized[$field] = $values;
            }
        }

        return $sanitized;
    }
}
