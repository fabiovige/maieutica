<?php

namespace App\Services\Documents;

use App\Contracts\DocumentTemplate;
use App\Models\GeneratedDocument;
use App\Models\Kid;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DocumentGenerator
{
    public function __construct(private DocumentTemplateFactory $factory) {}

    /**
     * Valida a requisição, renderiza, persiste o GeneratedDocument e devolve o PDF para download.
     */
    public function generate(int $modelType, Request $request): Response
    {
        $template = $this->factory->make($modelType);

        $request->validate($template->rules());

        $kid = $this->getKidWithRelations((int) $request->input('kid_id'));
        $formData = $request->except(['_token']);

        $html = $this->render($template, $kid, $formData);

        GeneratedDocument::create([
            'model_type' => $modelType,
            'documentable_id' => $kid->id,
            'documentable_type' => Kid::class,
            'professional_id' => $template->resolveProfessionalId($kid, $formData),
            'generated_by' => auth()->id(),
            'html_content' => $html,
            'form_data' => $formData,
            'metadata' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'document_title' => $template->title(),
            ],
            'generated_at' => now(),
        ]);

        return Pdf::loadHTML($html)->setPaper('A4', 'portrait')->download($template->filenamePrefix().'.pdf');
    }

    /**
     * Re-renderiza o HTML de um documento já gerado, a partir do template Blade
     * atual e do form_data armazenado — garante que download() sempre use o
     * layout mais recente (signature, CSS, etc), com a mesma lógica de dados
     * usada na geração inicial.
     */
    public function rerender(GeneratedDocument $document): string
    {
        $template = $this->factory->make($document->model_type);
        $kid = $this->getKidWithRelations($document->documentable_id);
        $formData = $document->form_data ?? [];

        return $this->render($template, $kid, $formData);
    }

    private function render(DocumentTemplate $template, Kid $kid, array $formData): string
    {
        return view($template->view(), $template->buildData($kid, $formData))->render();
    }

    private function getKidWithRelations(int $kidId): Kid
    {
        return Kid::with(['professionals.user'])->findOrFail($kidId);
    }
}
