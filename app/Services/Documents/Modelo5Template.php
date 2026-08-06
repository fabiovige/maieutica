<?php

namespace App\Services\Documents;

use App\Models\Kid;

class Modelo5Template extends AbstractDocumentTemplate
{
    public function rules(): array
    {
        return [
            'kid_id' => 'required|exists:kids,id',
            'descricao_demanda' => 'required|string',
            'procedimentos_texto' => 'required|string',
            'analise' => 'required|string',
            'conclusao' => 'required|string',
            'professionals' => 'required|array|min:1',
            'professionals.*' => 'exists:professionals,id',
        ];
    }

    public function view(): string
    {
        return 'documents.modelo5';
    }

    public function title(): string
    {
        return 'Relatório Multiprofissional - Modelo 5';
    }

    public function filenamePrefix(): string
    {
        return 'relatorio_multiprofissional_modelo_5';
    }

    public function buildData(Kid $kid, array $formData): array
    {
        $professionalsData = $this->resolveProfessionalsData($kid, $formData['professionals'] ?? []);

        return array_merge([
            'nome_paciente' => strtoupper($kid->name),
            'idade' => $kid->age ?? 'Não informada',
            'sexo' => $this->genderLabel($kid),
            'solicitante' => $formData['solicitante'] ?? null,
            'finalidade' => $formData['finalidade'] ?? 'Avaliação multiprofissional',

            'professionals' => $professionalsData,
            'nome_psicologo' => $professionalsData[0]['name'],
            'council' => $professionalsData[0]['council'],
            'crp' => $professionalsData[0]['crp'],
            'cidade' => $professionalsData[0]['city'],
            'data_formatada' => now()->locale('pt_BR')->isoFormat('D [de] MMMM [de] YYYY'),
        ], $this->assets('logotipo.png'), [
            'descricao_demanda' => $formData['descricao_demanda'] ?? null,
            'numero_encontros' => $formData['numero_encontros'] ?? null,
            'duracao_horas' => $formData['duracao_horas'] ?? null,
            'procedimentos_texto' => $formData['procedimentos_texto'] ?? null,
            'analise' => $formData['analise'] ?? null,
            'conclusao' => $formData['conclusao'] ?? null,
        ]);
    }
}
