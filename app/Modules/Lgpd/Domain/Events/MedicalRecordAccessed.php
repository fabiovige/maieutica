<?php

namespace App\Modules\Lgpd\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento recebido de outros módulos quando um prontuário médico é acessado.
 *
 * Este evento é disparado pelo módulo de Prontuários e escutado pelo
 * MedicalRecordAccessListener do módulo LGPD para criar o AccessLog.
 *
 * @see \App\Modules\Lgpd\Application\Listeners\MedicalRecordAccessListener
 */
class MedicalRecordAccessed
{
    use Dispatchable, SerializesModels;

    public int $operatorId;

    public int $recordId;

    public string $operationType;

    public string $accessedAt;

    /**
     * @param  int  $operatorId  ID do operador que acessou o prontuário
     * @param  int  $recordId  ID do prontuário acessado
     * @param  string  $operationType  Tipo de operação (view, download_pdf, edit, delete, restore)
     * @param  string  $accessedAt  Timestamp do acesso (ISO 8601)
     */
    public function __construct(
        int $operatorId,
        int $recordId,
        string $operationType,
        string $accessedAt
    ) {
        $this->operatorId = $operatorId;
        $this->recordId = $recordId;
        $this->operationType = $operationType;
        $this->accessedAt = $accessedAt;
    }
}
