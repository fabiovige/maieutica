<?php

declare(strict_types=1);

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class CompetenceDescriptionResource extends JsonResource
{

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'description' => $this->description,
        ];
    }
}
