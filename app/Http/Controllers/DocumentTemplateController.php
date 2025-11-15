<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentTemplateController extends Controller
{
    const PAGINATION_DEFAULT = 15;
    const MSG_CREATE_SUCCESS = 'Template criado com sucesso!';
    const MSG_CREATE_ERROR = 'Erro ao criar template.';
    const MSG_UPDATE_SUCCESS = 'Template atualizado com sucesso!';
    const MSG_UPDATE_ERROR = 'Erro ao atualizar template.';
    const MSG_DELETE_SUCCESS = 'Template excluído com sucesso!';
    const MSG_DELETE_ERROR = 'Erro ao excluir template.';
    const MSG_RESTORE_SUCCESS = 'Template restaurado com sucesso!';
    const MSG_RESTORE_ERROR = 'Erro ao restaurar template.';
    const MSG_NOT_FOUND = 'Template não encontrado.';

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', DocumentTemplate::class);

        $query = DocumentTemplate::query();

        // Filtro de busca
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('type', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtro por status (ativo/inativo)
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $templates = $query->orderBy('name')->paginate(self::PAGINATION_DEFAULT);

        $types = DocumentTemplate::getDocumentTypes();

        return view('document-templates.index', compact('templates', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', DocumentTemplate::class);

        $types = DocumentTemplate::getDocumentTypes();
        $placeholderCategories = DocumentTemplate::getAvailablePlaceholderCategories();

        return view('document-templates.create', compact('types', 'placeholderCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', DocumentTemplate::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', array_keys(DocumentTemplate::getDocumentTypes())),
            'html_content' => 'required|string',
            'description' => 'nullable|string',
            'available_placeholders' => 'nullable|array',
            'version' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $template = DocumentTemplate::create($validated);

            DB::commit();

            return redirect()->route('document-templates.index')
                ->with('success', self::MSG_CREATE_SUCCESS);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', self::MSG_CREATE_ERROR . ' ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DocumentTemplate $documentTemplate)
    {
        $this->authorize('view', $documentTemplate);

        $documentTemplate->load('generatedDocuments.kid', 'generatedDocuments.user');

        return view('document-templates.show', compact('documentTemplate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DocumentTemplate $documentTemplate)
    {
        $this->authorize('update', $documentTemplate);

        $types = DocumentTemplate::getDocumentTypes();
        $placeholderCategories = DocumentTemplate::getAvailablePlaceholderCategories();

        return view('document-templates.edit', compact('documentTemplate', 'types', 'placeholderCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DocumentTemplate $documentTemplate)
    {
        $this->authorize('update', $documentTemplate);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', array_keys(DocumentTemplate::getDocumentTypes())),
            'html_content' => 'required|string',
            'description' => 'nullable|string',
            'available_placeholders' => 'nullable|array',
            'version' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $documentTemplate->update($validated);

            DB::commit();

            return redirect()->route('document-templates.index')
                ->with('success', self::MSG_UPDATE_SUCCESS);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', self::MSG_UPDATE_ERROR . ' ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(DocumentTemplate $documentTemplate)
    {
        $this->authorize('delete', $documentTemplate);

        DB::beginTransaction();
        try {
            $documentTemplate->delete();

            DB::commit();

            return redirect()->route('document-templates.index')
                ->with('success', self::MSG_DELETE_SUCCESS);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', self::MSG_DELETE_ERROR . ' ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of trashed templates.
     */
    public function trash()
    {
        $this->authorize('viewTrash', DocumentTemplate::class);

        $templates = DocumentTemplate::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(self::PAGINATION_DEFAULT);

        return view('document-templates.trash', compact('templates'));
    }

    /**
     * Restore a trashed template.
     */
    public function restore($id)
    {
        $template = DocumentTemplate::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $template);

        DB::beginTransaction();
        try {
            $template->restore();

            DB::commit();

            return redirect()->route('document-templates.index')
                ->with('success', self::MSG_RESTORE_SUCCESS);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', self::MSG_RESTORE_ERROR . ' ' . $e->getMessage());
        }
    }

    /**
     * Toggle template active status.
     */
    public function toggleActive(DocumentTemplate $documentTemplate)
    {
        $this->authorize('update', $documentTemplate);

        DB::beginTransaction();
        try {
            $documentTemplate->update([
                'is_active' => !$documentTemplate->is_active
            ]);

            DB::commit();

            $status = $documentTemplate->is_active ? 'ativado' : 'desativado';
            return back()->with('success', "Template {$status} com sucesso!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao alterar status do template.');
        }
    }
}
