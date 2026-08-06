<?php

namespace App\Services\Documents;

use App\Models\Kid;

class Modelo3Template extends AbstractDocumentTemplate
{
    public function rules(): array
    {
        return [
            'kid_id' => 'required|exists:kids,id',
            'professionals' => 'nullable|array',
            'professionals.*' => 'exists:professionals,id',
        ];
    }

    public function view(): string
    {
        return 'documents.modelo3';
    }

    public function title(): string
    {
        return 'Laudo Psicológico - Modelo 3';
    }

    public function filenamePrefix(): string
    {
        return 'laudo_psicologico_modelo_3';
    }

    public function buildData(Kid $kid, array $formData): array
    {
        $professionalsData = $this->resolveProfessionalsData($kid, $formData['professionals'] ?? []);

        return array_merge([
            'nome_paciente' => strtoupper($kid->name),
            'idade' => $kid->age ?? 'Não informada',
            'sexo' => $this->genderLabel($kid),
            'solicitante' => $formData['solicitante'] ?? null,
            'finalidade' => $formData['finalidade'] ?? 'Avaliação psicológica',

            'professionals' => $professionalsData,
            'nome_psicologo' => $professionalsData[0]['name'],
            'council' => $professionalsData[0]['council'],
            'crp' => $professionalsData[0]['crp'],
            'cidade' => $professionalsData[0]['city'],
            'data_formatada' => now()->locale('pt_BR')->isoFormat('D [de] MMMM [de] YYYY'),
        ], $this->assets('logo-doc.jpg'), [
            'nome_informante' => $formData['nome_informante'] ?? null,
            'sintomas' => $formData['sintomas'] ?? null,
            'consequencias' => $formData['consequencias'] ?? null,
            'hipotese_diagnostico' => $formData['hipotese_diagnostico'] ?? null,
            'numero_encontros' => $formData['numero_encontros'] ?? null,
            'duracao_horas' => $formData['duracao_horas'] ?? null,
            'procedimentos_texto' => $formData['procedimentos_texto'] ?? null,
            'analise_texto' => $formData['analise_texto'] ?? null,
            'diagnostico' => $formData['diagnostico'] ?? null,
            'sintoma_principal' => $formData['sintoma_principal'] ?? null,
            'cid' => $formData['cid'] ?? null,
            'referencias' => $formData['referencias'] ?? null,
        ]);
    }
}
