<?php

namespace App\Modules\Lgpd\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento disparado quando uma eliminação de dados é concluída.
 *
 * @see \App\Modules\Lgpd\Application\Services\DataRequestService::complete()
 */
class DataDeletionCompleted
{
    use Dispatchable, SerializesModels;

    public int $dataRequestId;

    public int $subjectId;

    public array $deletedCategories;

    /**
     * @param  int  $dataRequestId  ID da requisição de eliminação
     * @param  int  $subjectId  ID do titular
     * @param  string[]  $deletedCategories  Categorias de dados eliminados (ex.: ['prontuarios', 'dados_cadastrais'])
     */
    public function __construct(
        int $dataRequestId,
        int $subjectId,
        array $deletedCategories
    ) {
        $this->dataRequestId = $dataRequestId;
        $this->subjectId = $subjectId;
        $this->deletedCategories = $deletedCategories;
    }
}
