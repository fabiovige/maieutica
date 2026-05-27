<?php

namespace App\Modules\Lgpd\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento disparado quando o prazo de uma DataRequest está próximo do vencimento.
 *
 * @see \App\Modules\Lgpd\Jobs\CheckDataRequestDeadlinesJob
 */
class DataRequestDeadlineAlert
{
    use Dispatchable, SerializesModels;

    public int $dataRequestId;

    public string $requestType;

    public string $deadline;

    public int $businessDaysRemaining;

    /**
     * @param  int  $dataRequestId  ID da requisição de direitos
     * @param  string  $requestType  Tipo da requisição (acesso, retificacao, eliminacao, portabilidade, revogacao)
     * @param  string  $deadline  Data de vencimento (ISO 8601)
     * @param  int  $businessDaysRemaining  Dias úteis restantes até o vencimento
     */
    public function __construct(
        int $dataRequestId,
        string $requestType,
        string $deadline,
        int $businessDaysRemaining
    ) {
        $this->dataRequestId = $dataRequestId;
        $this->requestType = $requestType;
        $this->deadline = $deadline;
        $this->businessDaysRemaining = $businessDaysRemaining;
    }
}
