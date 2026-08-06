<?php

namespace App\Services\Pdf;

use App\Models\Checklist;
use App\Models\Kid;
use App\Models\Plane;
use Exception;

/**
 * Gera o PDF de visualização de um plano automático já criado
 * (KidsController::pdfPlaneAutoView).
 */
class PlaneAutoViewBuilder extends AbstractPlanePdfBuilder
{
    public function __construct(
        protected Kid $sourceKid,
        protected Checklist $checklist,
        protected Plane $sourcePlane
    ) {}

    protected function resolvePlaneData(): void
    {
        if ($this->sourcePlane->checklist_id != $this->checklist->id) {
            throw new Exception('Este plano não pertence a este checklist.');
        }

        $this->kid = $this->sourceKid;
        $this->plane = $this->sourcePlane;
        $this->therapist = $this->resolveTherapistNames($this->kid);

        $competences = $this->plane->competences()->get();

        if (count($competences) === 0) {
            throw new Exception('Não existem competências para este plano.');
        }

        $this->groupCompetencesByDomain($competences);
        // planeNameForDisplay permanece null: o método original também não
        // exibia o nome do plano nesta tela (única diferença de resolvePlaneData
        // entre este builder e os outros dois — preservado intencionalmente).
    }
}
