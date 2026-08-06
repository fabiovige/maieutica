<?php

namespace App\Services\Documents;

use App\Contracts\DocumentTemplate;
use App\Models\Kid;
use App\Models\Professional;

abstract class AbstractDocumentTemplate implements DocumentTemplate
{
    public function resolveProfessionalId(Kid $kid, array $formData): ?int
    {
        return $kid->professionals->first()?->id;
    }

    /**
     * Dados comuns usados pelos modelos 1, 2 e 6 (profissional único do paciente).
     */
    protected function commonData(Kid $kid): array
    {
        $professional = $kid->professionals->first();
        $user = $professional ? $professional->user->first() : null;

        return [
            'nome_paciente' => strtoupper($kid->name),
            'nome_psicologo' => $user ? strtoupper($user->name) : 'N/A',
            'council' => $professional?->council_label ?? 'Reg.',
            'crp' => $professional?->registration_number ?? 'N/A',
            'cidade' => $user->city ?? 'Santana de Parnaíba',
            'data_formatada' => now()->locale('pt_BR')->isoFormat('D [de] MMMM [de] YYYY'),
        ];
    }

    protected function assets(string $logoFile = 'logotipo.png'): array
    {
        return [
            'watermark' => base64_encode(file_get_contents(public_path('images/bg-doc.png'))),
            'logo' => base64_encode(file_get_contents(public_path('images/'.$logoFile))),
        ];
    }

    protected function genderLabel(Kid $kid): string
    {
        if ($kid->gender === 'M') {
            return 'Masculino';
        }

        if ($kid->gender === 'F') {
            return 'Feminino';
        }

        return 'Não informado';
    }

    /**
     * Dados dos profissionais selecionados no formulário (modelos 3/4/5),
     * ou do profissional do paciente quando nenhum foi selecionado.
     */
    protected function resolveProfessionalsData(Kid $kid, array $professionalIds): array
    {
        if (! empty($professionalIds)) {
            $professionals = Professional::with('user')->whereIn('id', $professionalIds)->get();

            return $professionals->map(function ($prof) {
                $user = $prof->user->first();

                return [
                    'name' => $user ? strtoupper($user->name) : 'N/A',
                    'council' => $prof->council_label,
                    'crp' => $prof->registration_number ?? 'N/A',
                    'city' => $user->city ?? 'Santana de Parnaíba',
                ];
            })->toArray();
        }

        $professional = $kid->professionals->first();
        $user = $professional ? $professional->user->first() : null;

        return [[
            'name' => $user ? strtoupper($user->name) : 'N/A',
            'council' => $professional?->council_label ?? 'Reg.',
            'crp' => $professional?->registration_number ?? 'N/A',
            'city' => $user->city ?? 'Santana de Parnaíba',
        ]];
    }
}
