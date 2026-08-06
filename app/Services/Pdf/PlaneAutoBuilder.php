<?php

namespace App\Services\Pdf;

use App\Models\Checklist;
use App\Models\Kid;
use App\Models\Plane;
use Exception;

/**
 * Cria (ou reaproveita) um plano automático a partir de uma nota de checklist
 * e gera o PDF (KidsController::pdfPlaneAuto).
 */
class PlaneAutoBuilder extends AbstractPlanePdfBuilder
{
    protected int $competencesCount = 0;

    public function __construct(
        protected Kid $sourceKid,
        protected Checklist $checklist,
        protected int $note
    ) {}

    public function getCompetencesCount(): int
    {
        return $this->competencesCount;
    }

    protected function resolvePlaneData(): void
    {
        $this->kid = $this->sourceKid;

        $dataCreatePlane = [
            'kid_id' => $this->kid->id,
            'name' => Plane::NOTES_DESCRIPTION[$this->note],
            'checklist_id' => $this->checklist->id,
            'created_by' => auth()->user()->id,
        ];

        $existingPlane = Plane::where('kid_id', $this->kid->id)
            ->where('checklist_id', $this->checklist->id)
            ->where('is_active', true)
            ->where('name', $dataCreatePlane['name'])
            ->first();

        $this->plane = $existingPlane ?: Plane::create($dataCreatePlane);

        $this->therapist = $this->resolveTherapistNames($this->kid);

        $competencesNotes = Checklist::getCompetencesByNote($this->checklist->id, $this->note)->pluck('id')->toArray();

        if (count($competencesNotes) === 0) {
            throw new Exception('Não existem competências para este checklist e nota.');
        }

        $this->plane->competences()->sync($competencesNotes);
        $this->competencesCount = count($competencesNotes);

        $this->groupCompetencesByDomain($this->plane->competences()->get());
        $this->planeNameForDisplay = $this->plane->name;
    }
}
