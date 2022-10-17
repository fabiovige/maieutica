<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KidResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'birth_date' => $this->birth_date,
            'responsible' => $this->responsible()->first(),
            'terapeuta' => $this->user()->first(),
            'checklists' => $this->checklists()->get(),
            'planes' => $this->planes()->get()
        ];
    }
}
