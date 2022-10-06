<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlaneResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'kid_id' => $this->kid_id,
            'created_at' => $this->created_at->format('d/m/Y'),
            'competences' => $this->competences()->get(),
        ];
    }
}
