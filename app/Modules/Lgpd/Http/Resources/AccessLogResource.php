<?php

namespace App\Modules\Lgpd\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccessLogResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'operator_id' => $this->operator_id,
            'operator_name' => $this->whenLoaded('operator', fn () => $this->operator?->name),
            'medical_record_id' => $this->medical_record_id,
            'operation_type' => [
                'value' => $this->operation_type?->value,
                'label' => $this->operation_type?->label(),
            ],
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'accessed_at' => $this->accessed_at?->format('d/m/Y H:i'),
        ];
    }
}
