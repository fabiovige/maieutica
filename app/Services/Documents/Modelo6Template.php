<?php

namespace App\Services\Documents;

use App\Models\Kid;

class Modelo6Template extends AbstractDocumentTemplate
{
    public function rules(): array
    {
        return [
            'kid_id' => 'required|exists:kids,id',
            'descricao_demanda' => 'required|string',
            'procedimentos_texto' => 'required|string',
            'analise' => 'required|string',
            'conclusao' => 'required|string',
        ];
    }

    public function view(): string
    {
        return 'documents.modelo6';
    }

    public function title(): string
    {
        return 'Relatório Psicológico - Modelo 6';
    }

    public function filenamePrefix(): string
    {
        return 'relatorio_psicologico_modelo_6';
    }

    public function buildData(Kid $kid, array $formData): array
    {
        return array_merge(
            $this->commonData($kid),
            $this->assets(),
            [
                'idade' => $kid->age ?? 'Não informada',
                'sexo' => $this->genderLabel($kid),
                'solicitante' => $formData['solicitante'] ?? null,
                'finalidade' => $formData['finalidade'] ?? null,
                'descricao_demanda' => $formData['descricao_demanda'] ?? null,
                'numero_encontros' => $formData['numero_encontros'] ?? null,
                'duracao_horas' => $formData['duracao_horas'] ?? null,
                'procedimentos_texto' => $formData['procedimentos_texto'] ?? null,
                'analise' => $formData['analise'] ?? null,
                'conclusao' => $formData['conclusao'] ?? null,
            ]
        );
    }
}
