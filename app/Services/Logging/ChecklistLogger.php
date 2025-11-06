<?php

namespace App\Services\Logging;

use App\Models\Checklist;
use App\Models\Kid;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Centralized logging service for Checklist operations.
 *
 * All logs include contextual information for debugging, security audit, and compliance.
 * Follows the same pattern as KidLogger and UserLogger.
 */
class ChecklistLogger
{
    /**
     * Log when a checklist is created.
     *
     * @param Checklist $checklist
     * @param array $additionalContext
     * @return void
     */
    public function created(Checklist $checklist, array $additionalContext = []): void
    {
        Log::notice('Checklist criado', array_merge([
            'checklist_id' => $checklist->id,
            'kid_id' => $checklist->kid_id,
            'kid_initials' => $this->getKidInitials($checklist->kid),
            'status' => $checklist->status,
            'retroactive' => $checklist->retroactive,
            'retroactive_date' => $checklist->retroactive_date,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a checklist is updated.
     *
     * @param Checklist $checklist
     * @param array $changes Array of changed fields ['field' => ['old' => ..., 'new' => ...]]
     * @param array $additionalContext
     * @return void
     */
    public function updated(Checklist $checklist, array $changes = [], array $additionalContext = []): void
    {
        $changedFields = !empty($changes) ? array_keys($changes) : [];

        Log::notice('Checklist atualizado', array_merge([
            'checklist_id' => $checklist->id,
            'kid_id' => $checklist->kid_id,
            'kid_initials' => $this->getKidInitials($checklist->kid),
            'changed_fields' => $changedFields,
            'changes' => $this->sanitizeChanges($changes),
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a checklist is soft deleted (moved to trash).
     *
     * @param Checklist $checklist
     * @param array $additionalContext
     * @return void
     */
    public function deleted(Checklist $checklist, array $additionalContext = []): void
    {
        Log::notice('Checklist movido para lixeira', array_merge([
            'checklist_id' => $checklist->id,
            'kid_id' => $checklist->kid_id,
            'kid_initials' => $this->getKidInitials($checklist->kid),
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a checklist is restored from trash.
     *
     * @param Checklist $checklist
     * @param array $additionalContext
     * @return void
     */
    public function restored(Checklist $checklist, array $additionalContext = []): void
    {
        Log::notice('Checklist restaurado da lixeira', array_merge([
            'checklist_id' => $checklist->id,
            'kid_id' => $checklist->kid_id,
            'kid_initials' => $this->getKidInitials($checklist->kid),
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a checklist is permanently deleted.
     *
     * @param Checklist $checklist
     * @param array $additionalContext
     * @return void
     */
    public function forceDeleted(Checklist $checklist, array $additionalContext = []): void
    {
        Log::alert('Checklist excluído permanentemente', array_merge([
            'checklist_id' => $checklist->id,
            'kid_id' => $checklist->kid_id,
            'kid_initials' => $this->getKidInitials($checklist->kid),
            'warning' => 'Checklist permanentemente deletado do banco de dados',
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a checklist is cloned from another checklist.
     *
     * @param Checklist $originalChecklist
     * @param Checklist $newChecklist
     * @param array $additionalContext
     * @return void
     */
    public function cloned(Checklist $originalChecklist, Checklist $newChecklist, array $additionalContext = []): void
    {
        Log::notice('Checklist clonado', array_merge([
            'original_checklist_id' => $originalChecklist->id,
            'new_checklist_id' => $newChecklist->id,
            'kid_id' => $newChecklist->kid_id,
            'kid_initials' => $this->getKidInitials($newChecklist->kid),
            'competences_cloned' => $originalChecklist->competences()->count(),
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when the fill interface is accessed.
     *
     * @param Checklist $checklist
     * @param array $additionalContext
     * @return void
     */
    public function fillInterfaceAccessed(Checklist $checklist, array $additionalContext = []): void
    {
        Log::info('Interface de preenchimento acessada', array_merge([
            'checklist_id' => $checklist->id,
            'kid_id' => $checklist->kid_id,
            'kid_initials' => $this->getKidInitials($checklist->kid),
            'status' => $checklist->status,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a single competence note is updated.
     *
     * @param Checklist $checklist
     * @param int $competenceId
     * @param mixed $oldNote
     * @param mixed $newNote
     * @param array $additionalContext
     * @return void
     */
    public function competenceNoteUpdated(Checklist $checklist, int $competenceId, $oldNote, $newNote, array $additionalContext = []): void
    {
        Log::info('Nota de competência atualizada', array_merge([
            'checklist_id' => $checklist->id,
            'kid_id' => $checklist->kid_id,
            'kid_initials' => $this->getKidInitials($checklist->kid),
            'competence_id' => $competenceId,
            'old_note' => $oldNote,
            'new_note' => $newNote,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when multiple competence notes are updated in bulk.
     *
     * @param Checklist $checklist
     * @param int $updatedCount
     * @param array $additionalContext
     * @return void
     */
    public function competenceNotesBulkUpdated(Checklist $checklist, int $updatedCount, array $additionalContext = []): void
    {
        Log::notice('Notas de competências atualizadas em massa', array_merge([
            'checklist_id' => $checklist->id,
            'kid_id' => $checklist->kid_id,
            'kid_initials' => $this->getKidInitials($checklist->kid),
            'updated_count' => $updatedCount,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a checklist is automatically closed.
     *
     * @param Checklist $checklist
     * @param array $additionalContext
     * @return void
     */
    public function autoClosed(Checklist $checklist, array $additionalContext = []): void
    {
        Log::notice('Checklist fechado automaticamente', array_merge([
            'checklist_id' => $checklist->id,
            'kid_id' => $checklist->kid_id,
            'kid_initials' => $this->getKidInitials($checklist->kid),
            'reason' => 'Novo checklist criado para a mesma criança',
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a checklist chart/radar is viewed.
     *
     * @param Checklist $checklist
     * @param string $chartType
     * @param array $additionalContext
     * @return void
     */
    public function chartViewed(Checklist $checklist, string $chartType = 'radar', array $additionalContext = []): void
    {
        Log::info('Gráfico de checklist visualizado', array_merge([
            'checklist_id' => $checklist->id,
            'kid_id' => $checklist->kid_id,
            'kid_initials' => $this->getKidInitials($checklist->kid),
            'chart_type' => $chartType,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a plane is automatically generated from checklist.
     *
     * @param Checklist $checklist
     * @param int $planeId
     * @param array $additionalContext
     * @return void
     */
    public function planeAutoGenerated(Checklist $checklist, int $planeId, array $additionalContext = []): void
    {
        Log::notice('Plano gerado automaticamente a partir do checklist', array_merge([
            'checklist_id' => $checklist->id,
            'kid_id' => $checklist->kid_id,
            'kid_initials' => $this->getKidInitials($checklist->kid),
            'plane_id' => $planeId,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when kid data is updated via checklist form.
     *
     * @param Checklist $checklist
     * @param array $kidChanges
     * @param array $additionalContext
     * @return void
     */
    public function kidDataUpdatedViaChecklist(Checklist $checklist, array $kidChanges = [], array $additionalContext = []): void
    {
        Log::notice('Dados da criança atualizados via checklist', array_merge([
            'checklist_id' => $checklist->id,
            'kid_id' => $checklist->kid_id,
            'kid_initials' => $this->getKidInitials($checklist->kid),
            'kid_changes' => $kidChanges,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a retroactive checklist is created.
     *
     * @param Checklist $checklist
     * @param array $additionalContext
     * @return void
     */
    public function retroactiveCreated(Checklist $checklist, array $additionalContext = []): void
    {
        Log::notice('Checklist retroativo criado', array_merge([
            'checklist_id' => $checklist->id,
            'kid_id' => $checklist->kid_id,
            'kid_initials' => $this->getKidInitials($checklist->kid),
            'retroactive_date' => $checklist->retroactive_date,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when a checklist is viewed (details page).
     *
     * @param Checklist $checklist
     * @param string $viewType Type of view (details, chart, fill, pdf, etc.)
     * @param array $additionalContext
     * @return void
     */
    public function viewed(Checklist $checklist, string $viewType = 'details', array $additionalContext = []): void
    {
        Log::info('Checklist visualizado', array_merge([
            'checklist_id' => $checklist->id,
            'kid_id' => $checklist->kid_id,
            'kid_initials' => $this->getKidInitials($checklist->kid),
            'view_type' => $viewType,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when checklists list/index is accessed.
     *
     * @param array $filters Applied filters
     * @param array $additionalContext
     * @return void
     */
    public function listed(array $filters = [], array $additionalContext = []): void
    {
        Log::debug('Lista de checklists acessada', array_merge([
            'filters' => $filters,
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when trash/deleted checklists list is accessed.
     *
     * @param array $additionalContext
     * @return void
     */
    public function trashViewed(array $additionalContext = []): void
    {
        Log::info('Lixeira de checklists visualizada', array_merge(
            $this->buildUserContext(),
            $additionalContext
        ));
    }

    /**
     * Log when an operation fails.
     *
     * @param string $operation Operation name (store, update, delete, clone, etc.)
     * @param \Exception $exception
     * @param array $additionalContext
     * @return void
     */
    public function operationFailed(string $operation, \Exception $exception, array $additionalContext = []): void
    {
        Log::error("Operação de checklist falhou: {$operation}", array_merge([
            'operation' => $operation,
            'error' => $exception->getMessage(),
            'exception_class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ], $this->buildUserContext(), $additionalContext));
    }

    /**
     * Log when access is denied to a checklist operation.
     *
     * @param string $operation
     * @param Checklist|null $checklist
     * @param array $additionalContext
     * @return void
     */
    public function accessDenied(string $operation, ?Checklist $checklist = null, array $additionalContext = []): void
    {
        $context = [
            'operation' => $operation,
        ];

        if ($checklist) {
            $context['checklist_id'] = $checklist->id;
            $context['kid_id'] = $checklist->kid_id;
            $context['kid_initials'] = $this->getKidInitials($checklist->kid);
        }

        Log::warning('Acesso negado à operação de checklist', array_merge(
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
     * @param array $changes
     * @return array
     */
    private function sanitizeChanges(array $changes): array
    {
        $sanitized = [];

        // Campos que devem ser logados apenas como "[HIDDEN]" sem valores
        $sensitiveFields = ['password', 'api_token'];

        foreach ($changes as $field => $values) {
            if (in_array($field, $sensitiveFields)) {
                $sanitized[$field] = '[HIDDEN]';
            } else {
                $sanitized[$field] = $values;
            }
        }

        return $sanitized;
    }

    /**
     * Get kid initials for LGPD compliance.
     *
     * @param Kid|null $kid
     * @return string
     */
    private function getKidInitials(?Kid $kid): string
    {
        if (!$kid) {
            return 'N/A';
        }

        $names = explode(' ', $kid->name);

        if (count($names) === 1) {
            return strtoupper(substr($names[0], 0, 2));
        }

        $firstInitial = strtoupper(substr($names[0], 0, 1));
        $lastInitial = strtoupper(substr(end($names), 0, 1));

        return $firstInitial . $lastInitial;
    }
}
