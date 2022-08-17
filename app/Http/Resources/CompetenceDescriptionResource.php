<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class CompetenceDescriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'level' => $this->level,
            'competence_id' => $this->competence_id,
            'code' => $this->code,
            'competence' => $this->competence->name,
            'description' => $this->description,
            'description_detail' => $this->description_detail,
        ];
    }
}
