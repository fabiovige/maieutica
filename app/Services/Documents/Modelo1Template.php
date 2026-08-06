<?php

namespace App\Services\Documents;

use App\Models\Kid;

class Modelo1Template extends AbstractDocumentTemplate
{
    public function rules(): array
    {
        return [
            'kid_id' => 'required|exists:kids,id',
        ];
    }

    public function view(): string
    {
        return 'documents.modelo1';
    }

    public function title(): string
    {
        return 'Declaração - Modelo 1';
    }

    public function filenamePrefix(): string
    {
        return 'declaracao_modelo_1';
    }

    public function buildData(Kid $kid, array $formData): array
    {
        return array_merge(
            $this->commonData($kid),
            $this->assets(),
            [
                'dias_horarios' => $formData['dias_horarios'] ?? 'em horários estabelecidos',
                'previsao_termino' => $formData['previsao_termino'] ?? null,
            ]
        );
    }
}
