<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class CompetenceResource extends JsonResource
{
    public function toArray($request)
    {

        $pivot = ($this->checklists()->first() ? $this->checklists()->first()->pivot : false);

        return [
            'id' => $this->id,
            'level_id' => $this->level_id,
            'domain_id' => $this->domain_id,
            'domain_name' => $this->domain->name,
            'code' => $this->code,
            'description' => $this->description,
            'description_detail' => $this->description_detail,
            'note' => ($pivot) ? $pivot->note : null,
            'competence_id' => ($pivot) ? $pivot->competence_id : null,
            'checked' => (bool)($pivot)
        ];
    }
}
