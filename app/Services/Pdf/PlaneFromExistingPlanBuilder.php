<?php

namespace App\Services\Pdf;

use App\Models\Kid;
use App\Models\Plane;

/**
 * Gera o PDF de um plano de intervenção já existente (KidsController::pdfPlane).
 */
class PlaneFromExistingPlanBuilder extends AbstractPlanePdfBuilder
{
    public function __construct(protected Plane $existingPlane) {}

    protected function resolvePlaneData(): void
    {
        $this->plane = $this->existingPlane;
        $this->kid = Kid::findOrFail($this->plane->kid()->first()->id);
        $this->therapist = $this->resolveTherapistNames($this->kid);

        $this->groupCompetencesByDomain($this->plane->competences()->get());
        $this->planeNameForDisplay = $this->plane->name;
    }
}
