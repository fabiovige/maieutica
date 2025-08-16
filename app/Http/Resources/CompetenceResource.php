<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompetenceResource extends JsonResource
{
    public function toArray($request)
    {
        // $pivot = ($this->checklists()->first() ? $this->checklists()->first()->pivot : false);

        return [
            'id' => $this->id,
            'checklist_id' => $this->checklist_id,
            'level_id' => $this->level_id,
            'domain_id' => $this->domain_id,
            'domain_name' => $this->domain_name,
            'code' => $this->code,
            'description' => $this->description,
            'description_detail' => $this->description_detail,
            'note' => $this->note,
            'competence_id' => $this->id,
            'checked' => true,
        ];
    }
}
