<?php

namespace App\Services\Logging;

use App\Models\MedicalRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Centralized logging service for MedicalRecord operations.
 *
 * Privacy (LGPD): Does NOT log sensitive medical content (complaint, evolution_notes, etc.).
 * Uses patient identifiers (Kid initials or User ID) instead of full names.
 * All logs include contextual information for debugging and audit trails.
 */
class MedicalRecordLogger
{
    /**
     * Log when a medical record is created.
     */
    public function created(MedicalRecord $medicalRecord, array $additionalContext = []): void
    {
        Log::notice('Medical record created', array_merge([
            'medical_record_id' => $medicalRecord->id,
            'patient_identifier' => $this->getPatientIdentifier($medicalRecord),
            'patient_type' => $medicalRecord->patient_type_name,
            'session_date' => $medicalRecord->session_date,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a medical record is updated.
     */
    public function updated(MedicalRecord $medicalRecord, array $changes = [], array $additionalContext = []): void
    {
        $changedFields = !empty($changes) ? array_keys($changes) : [];

        Log::notice('Medical record updated', array_merge([
            'medical_record_id' => $medicalRecord->id,
            'patient_identifier' => $this->getPatientIdentifier($medicalRecord),
            'changed_fields' => $changedFields,
            'changes' => $this->sanitizeChanges($changes),
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a medical record is soft deleted (moved to trash).
     */
    public function deleted(MedicalRecord $medicalRecord, array $additionalContext = []): void
    {
        Log::notice('Medical record moved to trash', array_merge([
            'medical_record_id' => $medicalRecord->id,
            'patient_identifier' => $this->getPatientIdentifier($medicalRecord),
            'session_date' => $medicalRecord->session_date,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a medical record is restored from trash.
     */
    public function restored(MedicalRecord $medicalRecord, array $additionalContext = []): void
    {
        Log::notice('Medical record restored from trash', array_merge([
            'medical_record_id' => $medicalRecord->id,
            'patient_identifier' => $this->getPatientIdentifier($medicalRecord),
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a medical record is viewed (details page).
     */
    public function viewed(MedicalRecord $medicalRecord, array $additionalContext = []): void
    {
        Log::info('Medical record viewed', array_merge([
            'medical_record_id' => $medicalRecord->id,
            'patient_identifier' => $this->getPatientIdentifier($medicalRecord),
            'session_date' => $medicalRecord->session_date,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when medical records list/index is accessed.
     */
    public function listed(array $additionalContext = []): void
    {
        Log::debug('Medical records list accessed', array_merge(
            $this->buildUserContext(),
            $additionalContext
        ));
    }

    /**
     * Log when trash/deleted medical records list is accessed.
     */
    public function trashViewed(array $additionalContext = []): void
    {
        Log::info('Medical records trash viewed', array_merge(
            $this->buildUserContext(),
            $additionalContext
        ));
    }

    /**
     * Log when an operation fails.
     */
    public function operationFailed(string $operation, \Exception $exception, array $additionalContext = []): void
    {
        Log::error("Medical record operation failed: {$operation}", array_merge([
            'operation' => $operation,
            'error' => $exception->getMessage(),
            'exception_class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when access is denied to a medical record operation.
     */
    public function accessDenied(string $operation, ?MedicalRecord $medicalRecord = null, array $additionalContext = []): void
    {
        $context = [
            'operation' => $operation,
        ];

        if ($medicalRecord) {
            $context['medical_record_id'] = $medicalRecord->id;
            $context['patient_identifier'] = $this->getPatientIdentifier($medicalRecord);
        }

        Log::warning('Access denied to medical record operation', array_merge(
            $context,
            $this->buildUserContext(),
            $additionalContext
        ));
    }

    /**
     * Log when a new version of a medical record is created.
     */
    public function versionCreated(MedicalRecord $newVersion, MedicalRecord $oldVersion, array $additionalContext = []): void
    {
        Log::notice('Medical record new version created', array_merge([
            'new_version_id' => $newVersion->id,
            'old_version_id' => $oldVersion->id,
            'version_number' => $newVersion->version,
            'patient_identifier' => $this->getPatientIdentifier($newVersion),
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Build user context for logging.
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
     * Get safe patient identifier for logging (LGPD-compliant).
     *
     * For Kids: uses initials
     * For Users: uses only ID (no name)
     */
    private function getPatientIdentifier(MedicalRecord $medicalRecord): string
    {
        if (!$medicalRecord->patient) {
            return '[UNKNOWN PATIENT]';
        }

        // For Kids, use initials
        if ($medicalRecord->patient_type === 'App\\Models\\Kid') {
            return $medicalRecord->patient->initials ?? '[KID]';
        }

        // For Users, use only ID (don't expose name)
        if ($medicalRecord->patient_type === 'App\\Models\\User') {
            return '[USER-' . $medicalRecord->patient_id . ']';
        }

        return '[PATIENT]';
    }

    /**
     * Sanitize changes array to avoid logging sensitive medical data (LGPD).
     *
     * Medical content fields are marked as [CHANGED] without logging actual values.
     */
    private function sanitizeChanges(array $changes): array
    {
        $sanitized = [];

        // Medical content fields - NEVER log actual values (LGPD)
        $sensitiveFields = ['complaint', 'objective_technique', 'evolution_notes', 'referral_closure'];

        foreach ($changes as $field => $values) {
            if (in_array($field, $sensitiveFields)) {
                $sanitized[$field] = '[CHANGED]';
            } else {
                // Safe fields: patient_id, patient_type, session_date
                $sanitized[$field] = $values;
            }
        }

        return $sanitized;
    }
}
