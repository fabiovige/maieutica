<?php

namespace App\Http\Controllers;

use App\Models\GeneratedDocument;
use App\Models\Kid;
use App\Models\Professional;
use App\Services\Documents\DocumentGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DocumentsController extends Controller
{
    public function __construct(private DocumentGenerator $documentGenerator) {}

    /**
     * Lista de profissionais ativos para os selects dos formulários de documento.
     */
    private function activeProfessionalsForForm()
    {
        return Professional::with('user')
            ->whereHas('user')
            ->get()
            ->map(function ($professional) {
                $user = $professional->user->first();

                return [
                    'id' => $professional->id,
                    'name' => $user ? $user->name : 'N/A',
                    'council' => $professional->council_label,
                    'crp' => $professional->registration_number ?? 'N/A',
                ];
            });
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
        $professionals = $this->activeProfessionalsForForm();

        return view('documents.form-modelo3', compact('kids', 'professionals'));
    }

    /**
     * Exibe formulário do modelo 4 (Parecer Psicológico)
     *
     * @return \Illuminate\View\View
     */
    public function showFormModelo4()
    {
        $kids = Kid::getKids();
        $professionals = $this->activeProfessionalsForForm();

        return view('documents.form-modelo4', compact('kids', 'professionals'));
    }

    /**
     * Exibe formulário do modelo 5 (Relatório Multiprofissional)
     *
     * @return \Illuminate\View\View
     */
    public function showFormModelo5()
    {
        $kids = Kid::getKids();
        $professionals = $this->activeProfessionalsForForm();

        return view('documents.form-modelo5', compact('kids', 'professionals'));
    }

    /**
     * Exibe formulário do modelo 6 (Relatório Psicológico)
     *
     * @return \Illuminate\View\View
     */
    public function showFormModelo6()
    {
        $kids = Kid::getKids();

        return view('documents.form-modelo6', compact('kids'));
    }

    /**
     * Gera Declaração Modelo 1 para uma criança específica
     */
    public function modelo1(Request $request)
    {
        return $this->documentGenerator->generate(1, $request);
    }

    /**
     * Gera Declaração Modelo 2 para uma criança específica
     */
    public function modelo2(Request $request)
    {
        return $this->documentGenerator->generate(2, $request);
    }

    /**
     * Gera Laudo Psicológico Modelo 3 para uma criança específica
     */
    public function modelo3(Request $request)
    {
        return $this->documentGenerator->generate(3, $request);
    }

    /**
     * Gera Parecer Psicológico Modelo 4 para uma criança específica
     */
    public function modelo4(Request $request)
    {
        return $this->documentGenerator->generate(4, $request);
    }

    /**
     * Gera Relatório Multiprofissional Modelo 5 para uma criança específica
     */
    public function modelo5(Request $request)
    {
        return $this->documentGenerator->generate(5, $request);
    }

    /**
     * Gera Relatório Psicológico Modelo 6 para uma criança específica
     */
    public function modelo6(Request $request)
    {
        return $this->documentGenerator->generate(6, $request);
    }

    /**
     * Exibe histórico de documentos gerados
     */
    public function history(Request $request)
    {
        $this->authorize('viewAny', GeneratedDocument::class);

        $query = GeneratedDocument::with(['documentable', 'professional.user', 'generatedBy'])
            ->forAuthUser() // Aplica filtro de permissões
            ->orderBy('generated_at', 'desc');

        // Filtro de busca
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', '%'.$search.'%')
                    ->orWhere('model_type', 'like', '%'.$search.'%')
                    ->orWhereHas('documentable', function ($docQuery) use ($search) {
                        $docQuery->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('generatedBy', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        // Filtro por tipo de modelo
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        $documents = $query->paginate(15);

        return view('documents.history', compact('documents'));
    }

    /**
     * Download/regenera PDF re-renderizando o template Blade original.
     * Garante que sempre use o layout mais atual (signature, CSS, etc).
     */
    public function download(GeneratedDocument $document)
    {
        $this->authorize('download', $document);

        $html = $this->documentGenerator->rerender($document);

        $pdf = Pdf::loadHTML($html)->setPaper('A4', 'portrait');

        return $pdf->download($document->filename);
    }

    /**
     * Soft delete de documento gerado
     */
    public function destroy(GeneratedDocument $document)
    {
        $this->authorize('delete', $document);

        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Documento excluído com sucesso.',
        ]);
    }
}
