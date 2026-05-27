<?php

namespace App\Modules\Lgpd\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConsentRecordResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'subject_id' => $this->subject_id,
            'subject_type' => $this->subject_type,
            'purpose' => $this->purpose,
            'legal_basis' => [
                'value' => $this->legal_basis?->value,
                'label' => $this->legal_basis?->label(),
            ],
            'term_version' => $this->term_version,
            'status' => [
                'value' => $this->status?->value,
                'label' => $this->status?->label(),
            ],
            'collected_at' => $this->collected_at?->format('d/m/Y H:i'),
            'revoked_at' => $this->revoked_at?->format('d/m/Y H:i'),
            'collected_by' => [
                'id' => $this->collected_by,
                'name' => $this->whenLoaded('collectedBy', fn () => $this->collectedBy?->name),
            ],
            'revoked_by' => [
                'id' => $this->revoked_by,
                'name' => $this->whenLoaded('revokedBy', fn () => $this->revokedBy?->name),
            ],
        ];
    }
}
