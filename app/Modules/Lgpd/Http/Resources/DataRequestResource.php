<?php

namespace App\Modules\Lgpd\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DataRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => [
                'value' => $this->type?->value,
                'label' => $this->type?->label(),
            ],
            'requester_name' => $this->requester_name,
            'requester_document' => $this->requester_document,
            'contact_method' => $this->contact_method,
            'status' => [
                'value' => $this->status?->value,
                'label' => $this->status?->label(),
            ],
            'opened_at' => $this->opened_at?->format('d/m/Y H:i'),
            'deadline_at' => $this->deadline_at?->format('d/m/Y H:i'),
            'started_at' => $this->started_at?->format('d/m/Y H:i'),
            'completed_at' => $this->completed_at?->format('d/m/Y H:i'),
            'response' => $this->response,
            'retention_justification' => $this->retention_justification,
            'assigned_operator' => [
                'id' => $this->assigned_operator_id,
                'name' => $this->whenLoaded('assignedOperator', fn () => $this->assignedOperator?->name),
            ],
            'created_by' => [
                'id' => $this->created_by,
                'name' => $this->whenLoaded('createdBy', fn () => $this->createdBy?->name),
            ],
        ];
    }
}
