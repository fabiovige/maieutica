<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Kid;

class DocumentsController extends Controller
{
    /**
     * Gera Declaração Modelo 1 para uma criança específica
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function modelo1(Request $request)
    {
        // Valida que o kid_id foi enviado
        $request->validate([
            'kid_id' => 'required|exists:kids,id'
        ]);

        // Busca a criança com seus profissionais e usuários relacionados
        $kid = Kid::with(['professionals.user'])->findOrFail($request->kid_id);

        // Busca o primeiro profissional associado à criança (opcional)
        $professional = $kid->professionals->first();
        $user = $professional ? $professional->user->first() : null;

        // Prepara os assets
        $watermark = base64_encode(file_get_contents(public_path('images/bg-doc.png')));
        $logo = base64_encode(file_get_contents(public_path('images/logo-doc.jpg')));

        // Prepara os dados dinâmicos
        $data = [
            'nome_paciente'    => strtoupper($kid->name),
            'dias_horarios'    => $request->input('dias_horarios', 'em horários estabelecidos'),
            'previsao_termino' => $request->input('previsao_termino', null),
            'nome_psicologo'   => $user ? strtoupper($user->name) : 'N/A',
            'crp'              => $professional->registration_number ?? 'N/A',
            'cidade'           => $user->city ?? 'Santana de Parnaíba',
            'data_formatada'   => now()->locale('pt_BR')->isoFormat('D [de] MMMM [de] YYYY'),
            'watermark'        => $watermark,
            'logo'             => $logo,
        ];

        // Gera o PDF
        $pdf = Pdf::loadView('documents.modelo1', $data)
            ->setPaper('A4', 'portrait');

        return $pdf->stream('declaracao_modelo_1.pdf');
    }

    /**
     * Gera Declaração Modelo 2 para uma criança específica
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function modelo2(Request $request)
    {
        // Validação
        $request->validate([
            'kid_id' => 'required|exists:kids,id',
            #'mes_inicio' => 'required|string',
        ]);

        // Busca a criança com seus profissionais e usuários relacionados
        $kid = Kid::with(['professionals.user'])->findOrFail($request->kid_id);

        // Busca o primeiro profissional associado à criança
        $professional = $kid->professionals->first();
        $user = $professional ? $professional->user->first() : null;

        // Prepara os assets
        $watermark = base64_encode(file_get_contents(public_path('images/bg-doc.png')));
        $logo = base64_encode(file_get_contents(public_path('images/logo-doc.jpg')));

        // Prepara os dados dinâmicos
        $data = [
            'nome_paciente'    => strtoupper($kid->name),
            'mes_inicio'       => $kid->created_at->format('d/m/Y'),
            'nome_psicologo'   => $user ? strtoupper($user->name) : 'N/A',
            'crp'              => $professional->registration_number ?? 'N/A',
            'cidade'           => $user->city ?? 'Santana de Parnaíba',
            'data_formatada'   => now()->locale('pt_BR')->isoFormat('D [de] MMMM [de] YYYY'),
            'watermark'        => $watermark,
            'logo'             => $logo,
        ];

        // Gera o PDF
        $pdf = Pdf::loadView('documents.modelo2', $data)
            ->setPaper('A4', 'portrait');

        return $pdf->stream('declaracao_modelo_2.pdf');
    }

}
