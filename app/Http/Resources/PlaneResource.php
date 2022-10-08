<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlaneResource extends JsonResource
{
    public function toArray($request): array
    {
        $arrCompetences = $this->competences()->get();
        $competences = [];
        foreach($arrCompetences as $k => $c) {
            $competences[$c->id] = $c;
            $competences[$c->id]['domain'] = $c->domain()->first();
        }
        return [
            'id' => $this->id,
            'kid_id' => $this->kid_id,
            'created_at' => $this->created_at->format('d/m/Y'),
            'competences' => $competences
        ];
    }
}
