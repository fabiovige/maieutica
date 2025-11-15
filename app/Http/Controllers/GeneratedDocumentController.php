<?php

namespace App\Http\Controllers;

use App\Models\GeneratedDocument;
use App\Models\DocumentTemplate;
use App\Models\Kid;
use App\Models\Checklist;
use App\Services\DocumentGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeneratedDocumentController extends Controller
{
    protected $documentGenerator;

    const PAGINATION_DEFAULT = 15;
    const MSG_GENERATE_SUCCESS = 'Documento gerado com sucesso!';
    const MSG_GENERATE_ERROR = 'Erro ao gerar documento.';
    const MSG_DELETE_SUCCESS = 'Documento excluído com sucesso!';
    const MSG_DELETE_ERROR = 'Erro ao excluir documento.';
    const MSG_NOT_FOUND = 'Documento não encontrado.';

    public function __construct(DocumentGeneratorService $documentGenerator)
    {
        $this->documentGenerator = $documentGenerator;
    }

    /**
     * Display a listing of generated documents.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', GeneratedDocument::class);

        $query = GeneratedDocument::with(['documentTemplate', 'kid', 'user', 'checklist']);

        // Filtro por criança
        if ($request->filled('kid_id')) {
            $query->where('kid_id', $request->kid_id);
        }

        // Filtro por template
        if ($request->filled('template_id')) {
            $query->where('document_template_id', $request->template_id);
        }

        // Filtro por usuário que gerou
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtro por data
        if ($request->filled('date_from')) {
            $query->whereDate('generated_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('generated_at', '<=', $request->date_to);
        }

        $documents = $query->orderBy('generated_at', 'desc')->paginate(self::PAGINATION_DEFAULT);

        // Dados para filtros
        $templates = DocumentTemplate::active()->orderBy('name')->get();
        $kids = Kid::orderBy('name')->get();

        return view('generated-documents.index', compact('documents', 'templates', 'kids'));
    }

    /**
     * Display the specified generated document.
     */
    public function show(GeneratedDocument $generatedDocument)
    {
        $this->authorize('view', $generatedDocument);

        $generatedDocument->load(['documentTemplate', 'kid', 'user', 'checklist']);

        return view('generated-documents.show', compact('generatedDocument'));
    }

    /**
     * Show the form for generating a new document.
     */
    public function create(Request $request)
    {
        $this->authorize('generate', GeneratedDocument::class);

        // Pré-selecionar kid se vier por parâmetro
        $kidId = $request->get('kid_id');
        $kid = $kidId ? Kid::find($kidId) : null;

        $templates = DocumentTemplate::active()->orderBy('name')->get();
        $kids = Kid::orderBy('name')->get();

        return view('generated-documents.create', compact('templates', 'kids', 'kid'));
    }

    /**
     * Generate a new document.
     */
    public function generate(Request $request)
    {
        $this->authorize('generate', GeneratedDocument::class);

        $validated = $request->validate([
            'template_id' => 'required|exists:document_templates,id',
            'kid_id' => 'required|exists:kids,id',
            'checklist_id' => 'nullable|exists:checklists,id',
            'custom_data' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $template = DocumentTemplate::findOrFail($validated['template_id']);
            $kid = Kid::findOrFail($validated['kid_id']);
            $checklist = isset($validated['checklist_id'])
                ? Checklist::find($validated['checklist_id'])
                : null;
            $customData = $validated['custom_data'] ?? [];

            // Gerar documento
            $generatedDocument = $this->documentGenerator->generateDocument(
                $template,
                $kid,
                auth()->user(),
                $customData,
                $checklist
            );

            DB::commit();

            return redirect()->route('generated-documents.show', $generatedDocument)
                ->with('success', self::MSG_GENERATE_SUCCESS);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', self::MSG_GENERATE_ERROR . ' ' . $e->getMessage());
        }
    }

    /**
     * Download the generated document PDF.
     */
    public function download(GeneratedDocument $generatedDocument)
    {
        $this->authorize('download', $generatedDocument);

        try {
            return $this->documentGenerator->downloadDocument($generatedDocument);
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao baixar documento: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified document (soft delete).
     */
    public function destroy(GeneratedDocument $generatedDocument)
    {
        $this->authorize('delete', $generatedDocument);

        DB::beginTransaction();
        try {
            $this->documentGenerator->deleteDocument($generatedDocument);

            DB::commit();

            return redirect()->route('generated-documents.index')
                ->with('success', self::MSG_DELETE_SUCCESS);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', self::MSG_DELETE_ERROR . ' ' . $e->getMessage());
        }
    }

    /**
     * Display documents for a specific kid (used in kid show page).
     */
    public function byKid(Kid $kid)
    {
        $this->authorize('viewAny', GeneratedDocument::class);

        $documents = GeneratedDocument::with(['documentTemplate', 'user', 'checklist'])
            ->where('kid_id', $kid->id)
            ->orderBy('generated_at', 'desc')
            ->paginate(self::PAGINATION_DEFAULT);

        return view('generated-documents.by-kid', compact('documents', 'kid'));
    }
}
