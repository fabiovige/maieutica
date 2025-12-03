<?php

namespace App\Http\Controllers;

use App\Models\Kid;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DocumentsController extends Controller
{
    /**
     * Prepara os assets (watermark e logo) em base64
     */
    private function prepareAssets(): array
    {
        return [
            'watermark' => base64_encode(file_get_contents(public_path('images/bg-doc.png'))),
            'logo' => base64_encode(file_get_contents(public_path('images/logotipo.png'))),
        ];
    }

    /**
     * Busca Kid com profissionais e usuários relacionados
     */
    private function getKidWithRelations(int $kidId): Kid
    {
        return Kid::with(['professionals.user'])->findOrFail($kidId);
    }

    /**
     * Prepara dados comuns para todos os documentos
     */
    private function getCommonDocumentData(Kid $kid): array
    {
        $professional = $kid->professionals->first();
        $user = $professional ? $professional->user->first() : null;

        return [
            'nome_paciente' => strtoupper($kid->name),
            'nome_psicologo' => $user ? strtoupper($user->name) : 'N/A',
            'crp' => $professional->registration_number ?? 'N/A',
            'cidade' => $user->city ?? 'Santana de Parnaíba',
            'data_formatada' => now()->locale('pt_BR')->isoFormat('D [de] MMMM [de] YYYY'),
        ];
    }

    /**
     * Exibe página inicial com os modelos de documentos disponíveis
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('documents.index');
    }

    /**
     * Exibe formulário do modelo 1 (Declaração)
     *
     * @return \Illuminate\View\View
     */
    public function showFormModelo1()
    {
        $kids = Kid::getKids();

        return view('documents.form-modelo1', compact('kids'));
    }

    /**
     * Exibe formulário do modelo 2 (Declaração Simplificada)
     *
     * @return \Illuminate\View\View
     */
    public function showFormModelo2()
    {
        $kids = Kid::getKids();

        return view('documents.form-modelo2', compact('kids'));
    }

    /**
     * Exibe formulário do modelo 3 (Laudo Psicológico)
     *
     * @return \Illuminate\View\View
     */
    public function showFormModelo3()
    {
        $kids = Kid::getKids();
        $professionals = \App\Models\Professional::with('user')
            ->whereHas('user')
            ->get()
            ->map(function ($professional) {
                $user = $professional->user->first();
                return [
                    'id' => $professional->id,
                    'name' => $user ? $user->name : 'N/A',
                    'crp' => $professional->registration_number ?? 'N/A',
                ];
            });

        return view('documents.form-modelo3', compact('kids', 'professionals'));
    }

    /**
     * Gera Declaração Modelo 1 para uma criança específica
     *
     * @return \Illuminate\Http\Response
     */
    public function modelo1(Request $request)
    {
        // Validação
        $request->validate([
            'kid_id' => 'required|exists:kids,id',
        ]);

        // Busca dados
        $kid = $this->getKidWithRelations($request->kid_id);

        // Monta dados do documento
        $data = array_merge(
            $this->getCommonDocumentData($kid),
            $this->prepareAssets(),
            [
                'dias_horarios' => $request->input('dias_horarios', 'em horários estabelecidos'),
                'previsao_termino' => $request->input('previsao_termino', null),
            ]
        );

        // Gera o PDF
        $pdf = Pdf::loadView('documents.modelo1', $data)
            ->setPaper('A4', 'portrait');

        return $pdf->stream('declaracao_modelo_1.pdf');
    }

    /**
     * Gera Declaração Modelo 2 para uma criança específica
     *
     * @return \Illuminate\Http\Response
     */
    public function modelo2(Request $request)
    {
        // Validação
        $request->validate([
            'kid_id' => 'required|exists:kids,id',
        ]);

        // Busca dados
        $kid = $this->getKidWithRelations($request->kid_id);

        // Monta dados do documento
        $data = array_merge(
            $this->getCommonDocumentData($kid),
            $this->prepareAssets(),
            [
                'mes_inicio' => $kid->created_at->format('d/m/Y'),
            ]
        );

        // Gera o PDF
        $pdf = Pdf::loadView('documents.modelo2', $data)
            ->setPaper('A4', 'portrait');

        return $pdf->stream('declaracao_modelo_2.pdf');
    }

    /**
     * Gera Laudo Psicológico Modelo 3 para uma criança específica
     *
     * @return \Illuminate\Http\Response
     */
    public function modelo3(Request $request)
    {
        // Validação
        $request->validate([
            'kid_id' => 'required|exists:kids,id',
            'professionals' => 'nullable|array',
            'professionals.*' => 'exists:professionals,id',
        ]);

        // Busca dados
        $kid = $this->getKidWithRelations($request->kid_id);

        // Busca profissionais selecionados ou usa o profissional do paciente
        $professionalsData = [];
        if ($request->has('professionals') && count($request->professionals) > 0) {
            $selectedProfessionals = \App\Models\Professional::with('user')
                ->whereIn('id', $request->professionals)
                ->get();

            foreach ($selectedProfessionals as $prof) {
                $user = $prof->user->first();
                $professionalsData[] = [
                    'name' => $user ? strtoupper($user->name) : 'N/A',
                    'crp' => $prof->registration_number ?? 'N/A',
                    'city' => $user->city ?? 'Santana de Parnaíba',
                ];
            }
        } else {
            // Se não foi selecionado nenhum, usa o profissional do paciente
            $professional = $kid->professionals->first();
            $user = $professional ? $professional->user->first() : null;
            $professionalsData[] = [
                'name' => $user ? strtoupper($user->name) : 'N/A',
                'crp' => $professional->registration_number ?? 'N/A',
                'city' => $user->city ?? 'Santana de Parnaíba',
            ];
        }

        // Prepara os assets
        $watermark = base64_encode(file_get_contents(public_path('images/bg-doc.png')));
        $logo = base64_encode(file_get_contents(public_path('images/logo-doc.jpg')));

        // Monta dados do documento
        $data = [
            // Dados básicos
            'nome_paciente' => strtoupper($kid->name),
            'idade' => $kid->age ?? 'Não informada',
            'sexo' => isset($kid->gender) ? ($kid->gender == 'M' ? 'Masculino' : 'Feminino') : 'Não informado',
            'solicitante' => $request->input('solicitante', null),
            'finalidade' => $request->input('finalidade', 'Avaliação psicológica'),

            // Profissionais
            'professionals' => $professionalsData,

            // Profissional principal (para assinatura)
            'nome_psicologo' => $professionalsData[0]['name'],
            'crp' => $professionalsData[0]['crp'],
            'cidade' => $professionalsData[0]['city'],
            'data_formatada' => now()->locale('pt_BR')->isoFormat('D [de] MMMM [de] YYYY'),

            // Assets
            'watermark' => $watermark,
            'logo' => $logo,

            // Dados específicos do laudo
            'nome_informante' => $request->input('nome_informante', null),
            'sintomas' => $request->input('sintomas', null),
            'consequencias' => $request->input('consequencias', null),
            'hipotese_diagnostico' => $request->input('hipotese_diagnostico', null),
            'numero_encontros' => $request->input('numero_encontros', null),
            'duracao_horas' => $request->input('duracao_horas', null),
            'procedimentos_texto' => $request->input('procedimentos_texto', null),
            'analise_texto' => $request->input('analise_texto', null),
            'diagnostico' => $request->input('diagnostico', null),
            'sintoma_principal' => $request->input('sintoma_principal', null),
            'cid' => $request->input('cid', null),
            'referencias' => $request->input('referencias', null),
        ];

        // Gera o PDF
        $pdf = Pdf::loadView('documents.modelo3', $data)
            ->setPaper('A4', 'portrait');

        return $pdf->stream('laudo_psicologico_modelo_3.pdf');
    }

    /**
     * Exibe formulário do modelo 4 (Parecer Psicológico)
     *
     * @return \Illuminate\View\View
     */
    public function showFormModelo4()
    {
        $kids = Kid::getKids();
        $professionals = \App\Models\Professional::with('user')
            ->whereHas('user')
            ->get()
            ->map(function ($professional) {
                $user = $professional->user->first();
                return [
                    'id' => $professional->id,
                    'name' => $user ? $user->name : 'N/A',
                    'crp' => $professional->registration_number ?? 'N/A',
                ];
            });

        return view('documents.form-modelo4', compact('kids', 'professionals'));
    }

    /**
     * Gera Parecer Psicológico Modelo 4 para uma criança específica
     *
     * @return \Illuminate\Http\Response
     */
    public function modelo4(Request $request)
    {
        // Validação
        $request->validate([
            'kid_id' => 'required|exists:kids,id',
            'solicitante' => 'required|string',
            'finalidade' => 'required|string',
            'descricao_demanda' => 'required|string',
            'analise' => 'required|string',
            'conclusao' => 'required|string',
            'referencias' => 'required|string',
            'professionals' => 'nullable|array',
            'professionals.*' => 'exists:professionals,id',
        ]);

        // Busca dados
        $kid = $this->getKidWithRelations($request->kid_id);

        // Busca profissionais selecionados ou usa o profissional do paciente
        $professionalsData = [];
        if ($request->has('professionals') && count($request->professionals) > 0) {
            $selectedProfessionals = \App\Models\Professional::with('user')
                ->whereIn('id', $request->professionals)
                ->get();

            foreach ($selectedProfessionals as $prof) {
                $user = $prof->user->first();
                $professionalsData[] = [
                    'name' => $user ? strtoupper($user->name) : 'N/A',
                    'crp' => $prof->registration_number ?? 'N/A',
                    'city' => $user->city ?? 'Santana de Parnaíba',
                ];
            }
        } else {
            // Se não foi selecionado nenhum, usa o profissional do paciente
            $professional = $kid->professionals->first();
            $user = $professional ? $professional->user->first() : null;
            $professionalsData[] = [
                'name' => $user ? strtoupper($user->name) : 'N/A',
                'crp' => $professional->registration_number ?? 'N/A',
                'city' => $user->city ?? 'Santana de Parnaíba',
            ];
        }

        // Prepara os assets
        $watermark = base64_encode(file_get_contents(public_path('images/bg-doc.png')));
        $logo = base64_encode(file_get_contents(public_path('images/logotipo.png')));

        // Monta dados do documento
        $data = [
            // Dados básicos
            'nome_paciente' => strtoupper($kid->name),
            'idade' => $kid->age ?? 'Não informada',
            'sexo' => isset($kid->gender) ? ($kid->gender == 'M' ? 'Masculino' : 'Feminino') : 'Não informado',
            'solicitante' => $request->input('solicitante'),
            'finalidade' => $request->input('finalidade'),

            // Profissionais
            'professionals' => $professionalsData,

            // Profissional principal (para assinatura)
            'nome_psicologo' => $professionalsData[0]['name'],
            'crp' => $professionalsData[0]['crp'],
            'cidade' => $professionalsData[0]['city'],
            'data_formatada' => now()->locale('pt_BR')->isoFormat('D [de] MMMM [de] YYYY'),

            // Assets
            'watermark' => $watermark,
            'logo' => $logo,

            // Dados específicos do parecer
            'descricao_demanda' => $request->input('descricao_demanda'),
            'analise' => $request->input('analise'),
            'conclusao' => $request->input('conclusao'),
            'referencias' => $request->input('referencias'),
        ];

        // Gera o PDF
        $pdf = Pdf::loadView('documents.modelo4', $data)
            ->setPaper('A4', 'portrait');

        return $pdf->stream('parecer_psicologico_modelo_4.pdf');
    }
}
