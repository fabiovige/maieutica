<?php

namespace App\Services\Documents;

use App\Models\Kid;

class Modelo2Template extends AbstractDocumentTemplate
{
    public function rules(): array
    {
        return [
            'kid_id' => 'required|exists:kids,id',
        ];
    }

    public function view(): string
    {
        return 'documents.modelo2';
    }

    public function title(): string
    {
        return 'Declaração Simplificada - Modelo 2';
    }

    public function filenamePrefix(): string
    {
        return 'declaracao_modelo_2';
    }

    public function buildData(Kid $kid, array $formData): array
    {
        return array_merge(
            $this->commonData($kid),
            $this->assets(),
            [
                'mes_inicio' => $kid->created_at->format('d/m/Y'),
            ]
        );
    }
}
