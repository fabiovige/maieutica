<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use App\Models\GeneratedDocument;
use App\Models\Kid;
use App\Models\User;
use App\Models\Checklist;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DocumentGeneratorService
{
    /**
     * Gera um documento PDF a partir de um template.
     *
     * @param DocumentTemplate $template
     * @param Kid $kid
     * @param User $user
     * @param array $customData Dados adicionais personalizados
     * @param Checklist|null $checklist
     * @return GeneratedDocument
     */
    public function generateDocument(
        DocumentTemplate $template,
        Kid $kid,
        User $user,
        array $customData = [],
        ?Checklist $checklist = null
    ): GeneratedDocument {
        // 1. Coletar todos os dados necessários
        $data = $this->collectData($kid, $user, $checklist, $customData);

        // 2. Substituir placeholders no HTML
        $htmlContent = $this->replacePlaceholders($template->html_content, $data);

        // 3. Gerar PDF
        $pdf = Pdf::loadHTML($htmlContent);
        $pdf->setPaper('A4', 'portrait');

        // 4. Salvar PDF no storage
        $filePath = $this->savePDF($pdf, $template, $kid);

        // 5. Criar registro no banco
        $generatedDocument = $this->createRecord(
            $template,
            $kid,
            $user,
            $checklist,
            $filePath,
            $data
        );

        return $generatedDocument;
    }

    /**
     * Coleta todos os dados necessários para substituição de placeholders.
     *
     * @param Kid $kid
     * @param User $user
     * @param Checklist|null $checklist
     * @param array $customData
     * @return array
     */
    protected function collectData(
        Kid $kid,
        User $user,
        ?Checklist $checklist,
        array $customData
    ): array {
        $data = [];

        // Dados da criança
        $data['nome_completo'] = $kid->name ?? '';
        $data['nome_crianca'] = $kid->name ?? '';
        $data['cpf'] = $kid->cpf ?? '';
        $data['idade'] = $kid->age ?? '';
        $data['sexo'] = $kid->gender ?? '';
        $data['data_nascimento'] = $kid->birth_date ? Carbon::parse($kid->birth_date)->format('d/m/Y') : '';

        // Dados do responsável
        $responsible = $kid->responsible;
        $data['nome_responsavel'] = $responsible->name ?? '';
        $data['nome_acompanhante'] = $responsible->name ?? '';

        // Dados do profissional (usuário que está gerando)
        $professional = $user->professional;
        $data['profissional_nome'] = $professional->name ?? $user->name;
        $data['profissional_crp'] = $professional->crp ?? '';
        $data['profissional_especialidade'] = $professional->specialty ?? '';

        // Dados do atendimento
        if ($kid->first_attendance) {
            $data['data_inicio'] = Carbon::parse($kid->first_attendance)->format('d/m/Y');
        } else {
            $data['data_inicio'] = '';
        }

        // Dados do checklist (se fornecido)
        if ($checklist) {
            $data['data_avaliacao'] = $checklist->created_at->format('d/m/Y');
            $data['numero_encontros'] = $checklist->sessions_count ?? '';
            $data['total_sessoes'] = $checklist->sessions_count ?? '';

            // Calcular percentual de desenvolvimento se houver ChecklistService
            if (class_exists('\App\Services\ChecklistService')) {
                $checklistService = app(\App\Services\ChecklistService::class);
                $percentage = $checklistService->calculatePercentage($checklist);
                $data['percentual_desenvolvimento'] = number_format($percentage, 2) . '%';
            }
        }

        // Dados do sistema
        $data['data_emissao'] = Carbon::now()->format('d/m/Y');
        $data['cidade'] = 'Santana de Parnaíba';
        $data['numero_documento'] = $this->generateDocumentNumber();

        // Mesclar com dados personalizados (sobrescreve se existir)
        $data = array_merge($data, $customData);

        return $data;
    }

    /**
     * Substitui os placeholders no HTML pelos valores reais.
     *
     * @param string $html
     * @param array $data
     * @return string
     */
    protected function replacePlaceholders(string $html, array $data): string
    {
        foreach ($data as $key => $value) {
            // Substituir {{placeholder}} pelo valor
            $html = str_replace('{{' . $key . '}}', $value, $html);
        }

        // Remover placeholders não substituídos (deixar em branco)
        $html = preg_replace('/\{\{[a-z_]+\}\}/', '', $html);

        return $html;
    }

    /**
     * Salva o PDF no storage e retorna o caminho.
     *
     * @param \Barryvdh\DomPDF\PDF $pdf
     * @param DocumentTemplate $template
     * @param Kid $kid
     * @return string
     */
    protected function savePDF($pdf, DocumentTemplate $template, Kid $kid): string
    {
        // Criar diretório se não existir
        $directory = 'documents/' . $kid->id;
        Storage::makeDirectory($directory);

        // Nome do arquivo: template_kid_timestamp.pdf
        $fileName = Str::slug($template->name) . '_' . $kid->id . '_' . time() . '.pdf';
        $filePath = $directory . '/' . $fileName;

        // Salvar PDF
        Storage::put($filePath, $pdf->output());

        return $filePath;
    }

    /**
     * Cria o registro do documento gerado no banco de dados.
     *
     * @param DocumentTemplate $template
     * @param Kid $kid
     * @param User $user
     * @param Checklist|null $checklist
     * @param string $filePath
     * @param array $data
     * @return GeneratedDocument
     */
    protected function createRecord(
        DocumentTemplate $template,
        Kid $kid,
        User $user,
        ?Checklist $checklist,
        string $filePath,
        array $data
    ): GeneratedDocument {
        return GeneratedDocument::create([
            'document_template_id' => $template->id,
            'kid_id' => $kid->id,
            'checklist_id' => $checklist?->id,
            'user_id' => $user->id,
            'file_path' => $filePath,
            'data_used' => $data,
            'generated_at' => Carbon::now(),
        ]);
    }

    /**
     * Gera um número único para o documento.
     *
     * @return string
     */
    protected function generateDocumentNumber(): string
    {
        $count = GeneratedDocument::count() + 1;
        return str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Baixa um documento gerado.
     *
     * @param GeneratedDocument $document
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadDocument(GeneratedDocument $document)
    {
        return Storage::download($document->file_path, $document->file_name);
    }

    /**
     * Deleta o arquivo PDF do storage ao deletar o registro.
     *
     * @param GeneratedDocument $document
     * @return bool
     */
    public function deleteDocument(GeneratedDocument $document): bool
    {
        // Deletar arquivo físico
        if (Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }

        // Soft delete do registro
        return $document->delete();
    }
}
