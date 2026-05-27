<?php

namespace App\Modules\Lgpd\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento disparado quando um consentimento é revogado.
 *
 * @see \App\Modules\Lgpd\Application\Services\ConsentService::revoke()
 */
class ConsentRevoked
{
    use Dispatchable, SerializesModels;

    public int $consentRecordId;

    public int $subjectId;

    public string $purpose;

    public string $revokedAt;

    /**
     * @param  int  $consentRecordId  ID do registro de consentimento revogado
     * @param  int  $subjectId  ID do titular
     * @param  string  $purpose  Finalidade do consentimento revogado
     * @param  string  $revokedAt  Timestamp da revogação (ISO 8601)
     */
    public function __construct(
        int $consentRecordId,
        int $subjectId,
        string $purpose,
        string $revokedAt
    ) {
        $this->consentRecordId = $consentRecordId;
        $this->subjectId = $subjectId;
        $this->purpose = $purpose;
        $this->revokedAt = $revokedAt;
    }
}
