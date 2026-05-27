<?php

namespace App\Modules\Lgpd\Application\Services;

use App\Modules\Lgpd\Application\DTOs\CreateConsentDTO;
use App\Modules\Lgpd\Domain\Events\ConsentRevoked;
use App\Modules\Lgpd\Domain\Exceptions\DuplicateActiveConsentException;
use App\Modules\Lgpd\Domain\Exceptions\InvalidLegalBasisException;
use App\Modules\Lgpd\Domain\ValueObjects\ConsentStatus;
use App\Modules\Lgpd\Domain\ValueObjects\LegalBasis;
use App\Modules\Lgpd\Infrastructure\Models\ConsentLegalBasisHistoryModel;
use App\Modules\Lgpd\Infrastructure\Models\ConsentRecordModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class ConsentService
{
    /**
     * Cria um novo registro de consentimento.
     *
     * Valida unicidade de consentimento ativo por titular+finalidade,
     * valida base legal contra o enum LegalBasis e cria o registro.
     *
     * @throws DuplicateActiveConsentException
     * @throws InvalidLegalBasisException
     */
    public function create(CreateConsentDTO $dto): ConsentRecordModel
    {
        $this->validateLegalBasis($dto->legalBasis);

        $existing = $this->findActiveForSubject($dto->subjectId, $dto->purpose);

        if ($existing !== null) {
            throw DuplicateActiveConsentException::forSubjectAndPurpose($dto->subjectId, $dto->purpose);
        }

        return ConsentRecordModel::create([
            'subject_id' => $dto->subjectId,
            'subject_type' => $dto->subjectType,
            'purpose' => $dto->purpose,
            'legal_basis' => $dto->legalBasis,
            'term_version' => $dto->termVersion,
            'status' => ConsentStatus::ATIVO->value,
            'collected_at' => now(),
            'collected_by' => $dto->operatorId,
        ]);
    }

    /**
     * Revoga um consentimento ativo.
     *
     * Altera status para revogado, preenche revoked_at e revoked_by,
     * e dispara o evento ConsentRevoked.
     */
    public function revoke(int $consentId, int $operatorId): ConsentRecordModel
    {
        $consent = ConsentRecordModel::findOrFail($consentId);

        $consent->update([
            'status' => ConsentStatus::REVOGADO->value,
            'revoked_at' => now(),
            'revoked_by' => $operatorId,
        ]);

        $this->safeDispatch(new ConsentRevoked(
            consentRecordId: $consent->id,
            subjectId: $consent->subject_id,
            purpose: $consent->purpose,
            revokedAt: $consent->revoked_at->toIso8601String(),
        ));

        return $consent->fresh();
    }

    /**
     * Busca consentimento ativo por titular+finalidade.
     */
    public function findActiveForSubject(int $subjectId, string $purpose): ?ConsentRecordModel
    {
        return ConsentRecordModel::active()
            ->where('subject_id', $subjectId)
            ->where('purpose', $purpose)
            ->first();
    }

    /**
     * Lista todos os consentimentos de um titular.
     */
    public function listBySubject(int $subjectId): Collection
    {
        return ConsentRecordModel::where('subject_id', $subjectId)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Lista consentimentos agrupados por base legal.
     */
    public function listByLegalBasis(string $legalBasis): Collection
    {
        $this->validateLegalBasis($legalBasis);

        return ConsentRecordModel::where('legal_basis', $legalBasis)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Altera a base legal de um consentimento com registro no histórico.
     *
     * @throws InvalidLegalBasisException
     */
    public function changeLegalBasis(int $consentId, string $newBasis, string $justification, int $operatorId): ConsentRecordModel
    {
        $this->validateLegalBasis($newBasis);

        $consent = ConsentRecordModel::findOrFail($consentId);

        $previousBasis = $consent->getRawOriginal('legal_basis') ?? $consent->legal_basis->value ?? $consent->legal_basis;

        // Registrar no histórico
        ConsentLegalBasisHistoryModel::create([
            'consent_record_id' => $consent->id,
            'previous_legal_basis' => is_string($previousBasis) ? $previousBasis : $previousBasis->value,
            'new_legal_basis' => $newBasis,
            'justification' => $justification,
            'changed_by' => $operatorId,
            'changed_at' => now(),
        ]);

        $consent->update([
            'legal_basis' => $newBasis,
        ]);

        return $consent->fresh();
    }

    /**
     * Valida se a base legal informada pertence ao enum LegalBasis.
     *
     * @throws InvalidLegalBasisException
     */
    private function validateLegalBasis(string $legalBasis): void
    {
        $validBases = array_column(LegalBasis::cases(), 'value');

        if (! in_array($legalBasis, $validBases, true)) {
            throw InvalidLegalBasisException::forBasis($legalBasis);
        }
    }

    /**
     * Dispara evento de forma segura, sem interromper a operação principal.
     */
    private function safeDispatch(object $event): void
    {
        try {
            event($event);
        } catch (\Throwable $e) {
            Log::error('[LGPD] Event dispatch failed', [
                'event' => get_class($event),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
