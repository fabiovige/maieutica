<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChecklistRegisterResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'checklist_id' => $this->checklist_id,
            'competence_description_id' => $this->competence_description_id,
            'note' => $this->note,
        ];
    }
}
