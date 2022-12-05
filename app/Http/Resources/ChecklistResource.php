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
            'created_at' => $this->created_at->format('d/m/Y'),
        ];
    }
}
