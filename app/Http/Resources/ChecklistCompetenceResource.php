<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChecklistCompetenceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'checklist_id' => $this->pivot->checklist_id,
            'competence_id' => $this->pivot->competence_id,
            'note' => $this->pivot->note,
        ];
    }
}
