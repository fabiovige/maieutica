<?php

namespace App\Services\Documents;

use App\Models\Kid;

class Modelo4Template extends AbstractDocumentTemplate
{
    public function rules(): array
    {
        return [
            'kid_id' => 'required|exists:kids,id',
            'solicitante' => 'required|string',
            'finalidade' => 'required|string',
            'descricao_demanda' => 'required|string',
            'analise' => 'required|string',
            'conclusao' => 'required|string',
            'referencias' => 'required|string',
            'professionals' => 'nullable|array',
            'professionals.*' => 'exists:professionals,id',
        ];
    }

    public function view(): string
    {
        return 'documents.modelo4';
    }

    public function title(): string
    {
        return 'Parecer Psicológico - Modelo 4';
    }

    public function filenamePrefix(): string
    {
        return 'parecer_psicologico_modelo_4';
    }

    public function buildData(Kid $kid, array $formData): array
    {
        $professionalsData = $this->resolveProfessionalsData($kid, $formData['professionals'] ?? []);

        return array_merge([
            'nome_paciente' => strtoupper($kid->name),
            'idade' => $kid->age ?? 'Não informada',
            'sexo' => $this->genderLabel($kid),
            'solicitante' => $formData['solicitante'] ?? null,
            'finalidade' => $formData['finalidade'] ?? null,

            'professionals' => $professionalsData,
            'nome_psicologo' => $professionalsData[0]['name'],
            'council' => $professionalsData[0]['council'],
            'crp' => $professionalsData[0]['crp'],
            'cidade' => $professionalsData[0]['city'],
            'data_formatada' => now()->locale('pt_BR')->isoFormat('D [de] MMMM [de] YYYY'),
        ], $this->assets('logotipo.png'), [
            'descricao_demanda' => $formData['descricao_demanda'] ?? null,
            'analise' => $formData['analise'] ?? null,
            'conclusao' => $formData['conclusao'] ?? null,
            'referencias' => $formData['referencias'] ?? null,
        ]);
    }
}
