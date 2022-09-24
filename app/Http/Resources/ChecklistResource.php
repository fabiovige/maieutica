<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChecklistResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'level' => $this->level,
            'kid' => $this->kid()->get(),
            'checklists' => $this->kid->checklists()->get(),
        ];
    }
}
