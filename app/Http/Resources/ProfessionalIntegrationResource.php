<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfessionalIntegrationResource extends JsonResource
{
    public function toArray($request): array
    {
        $user = $this->user->first();

        return [
            'id' => $this->id,
            'full_name' => $user?->name,
            'email' => $user?->email,
            'phone' => $user?->phone,
            'profession' => $this->specialty?->name,
        ];
    }
}
