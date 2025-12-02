<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use App\Models\GeneratedDocument;
use App\Models\Kid;
use App\Models\User;
use App\Models\Checklist;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use TCPDF;

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

        // 3. Gerar PDF com TCPDF
        $pdf = $this->generateTCPDF($htmlContent);

        // 4. Salvar PDF no storage
        $filePath = $this->saveTCPDF($pdf, $template, $kid);

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
     * Coleta todos os dados necess�rios para substitui��o de placeholders.
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

        // Dados da crian�a
        $data['nome_completo'] = $kid->name ?? '';
        $data['nome_crianca'] = $kid->name ?? '';
        $data['cpf'] = '';  // Campo n�o existe na tabela kids
        $data['idade'] = $kid->age ?? '';
        $data['sexo'] = $kid->gender ?? '';
        $data['data_nascimento'] = $kid->birth_date ? $this->formatDate($kid->birth_date) : '';

        // Dados do respons�vel
        $responsible = $kid->responsible;
        $data['nome_responsavel'] = $responsible->name ?? '';
        $data['nome_acompanhante'] = $responsible->name ?? '';
        $data['telefone_responsavel'] = $responsible->phone ?? '';
        $data['email_responsavel'] = $responsible->email ?? '';

        // Endere�o completo do respons�vel
        if ($responsible) {
            $enderecoPartes = array_filter([
                $responsible->street ?? '',
                $responsible->number ? 'n� ' . $responsible->number : '',
                $responsible->complement ?? '',
                $responsible->neighborhood ?? '',
            ]);
            $data['endereco'] = implode(', ', $enderecoPartes);
            $data['cidade'] = $responsible->city ?? '';
            $data['estado'] = $responsible->state ?? 'SP';
            $data['cep'] = $responsible->postal_code ?? '';
        } else {
            $data['endereco'] = '';
            $data['cidade'] = '';
            $data['estado'] = 'SP';
            $data['cep'] = '';
        }

        // Dados do profissional (usu�rio que est� gerando)
        $professional = $user->professional ? $user->professional->first() : null;
        $data['profissional_nome'] = $user->name ?? '';
        $data['profissional_crp'] = $professional ? ($professional->registration_number ?? '') : '';
        $data['profissional_registro'] = $professional ? ($professional->registration_number ?? '') : '';
        $data['profissional_especialidade'] = $professional && $professional->specialty ? $professional->specialty->name : '';
        $data['profissional_titulo'] = 'Psic�logo(a)';  // Valor fixo
        $data['profissional_telefone'] = $user->phone ?? '';
        $data['profissional_email'] = $user->email ?? '';

        // Dados do atendimento
        // Usar data de cadastro da crian�a como data de in�cio se n�o fornecido em customData
        if (isset($customData['data_inicio']) && !empty($customData['data_inicio'])) {
            $data['data_inicio'] = $customData['data_inicio'];
            try {
                if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $customData['data_inicio'])) {
                    $firstAttendance = Carbon::createFromFormat('d/m/Y', $customData['data_inicio']);
                } else {
                    $firstAttendance = Carbon::parse($customData['data_inicio']);
                }
                $data['mes_inicio'] = $firstAttendance->format('m');
                $data['ano_inicio'] = $firstAttendance->format('Y');
            } catch (\Exception $e) {
                $data['mes_inicio'] = '';
                $data['ano_inicio'] = '';
            }
        } else {
            // Usar data de cadastro como fallback
            $data['data_inicio'] = $kid->created_at ? $kid->created_at->format('d/m/Y') : '';
            $data['mes_inicio'] = $kid->created_at ? $kid->created_at->format('m') : '';
            $data['ano_inicio'] = $kid->created_at ? $kid->created_at->format('Y') : '';
        }

        // Dados de atendimento (valores padr�o se n�o fornecidos em customData)
        $data['dias_semana'] = $customData['dias_semana'] ?? '';
        $data['horario_atendimento'] = $customData['horario_atendimento'] ?? '';
        $data['duracao_sessao'] = $customData['duracao_sessao'] ?? '50 minutos';
        $data['opcao_termino'] = $customData['opcao_termino'] ?? 'O tratamento encontra-se em andamento.';

        // Dados do checklist (se fornecido)
        if ($checklist) {
            $data['data_avaliacao'] = $checklist->created_at->format('d/m/Y');
            $data['numero_encontros'] = $checklist->sessions_count ?? '';
            $data['total_sessoes'] = $checklist->sessions_count ?? '';

            // Calcular percentual de desenvolvimento
            try {
                $checklistService = app(\App\Services\ChecklistService::class);
                $percentage = $checklistService->percentualDesenvolvimento($checklist->id);
                $data['percentual_desenvolvimento'] = number_format($percentage, 2) . '%';
            } catch (\Exception $e) {
                $data['percentual_desenvolvimento'] = '0%';
            }
        }

        // Campos de avalia��o (vazios por padr�o, podem ser preenchidos via customData)
        $avaliacaoFields = [
            'solicitante', 'finalidade', 'descricao_demanda', 'instrumentos_utilizados',
            'analise_resultados', 'diagnostico', 'cid', 'hipotese_diagnostica',
            'prognostico', 'recomendacoes', 'objetivo_parecer', 'historico_clinico',
            'avaliacao_psicologica', 'conclusao', 'desenvolvimento_processo',
            'evolucao', 'consideracoes_finais', 'observacoes'
        ];

        foreach ($avaliacaoFields as $field) {
            $data[$field] = $customData[$field] ?? '';
        }

        // Dados do sistema
        $data['data_emissao'] = Carbon::now()->format('d/m/Y');
        if (!isset($data['cidade']) || empty($data['cidade'])) {
            $data['cidade'] = 'Santana de Parna�ba';
        }
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

        // Remover placeholders n�o substitu�dos (deixar em branco)
        $html = preg_replace('/\{\{[a-z_]+\}\}/', '', $html);

        return $html;
    }

    /**
     * Gera PDF usando TCPDF com marca d'água
     *
     * @param string $htmlContent
     * @return TCPDF
     */
    protected function generateTCPDF(string $htmlContent): TCPDF
    {
        // Criar instância TCPDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Configurações do documento
        $pdf->SetCreator('Clínica Maiêutica');
        $pdf->SetAuthor('Clínica Maiêutica');
        $pdf->SetTitle('Declaração');

        // Remover header/footer padrão
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Margens
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(true, 20);

        // Adicionar página
        $pdf->AddPage();

        // Renderizar HTML primeiro (logo já está embutido)
        $pdf->writeHTML($htmlContent, true, false, true, false, '');

        // MARCA D'ÁGUA DEPOIS - Sobrepor no conteúdo
        $bgDocPath = public_path('images/bg-doc.png');
        if (file_exists($bgDocPath)) {
            // Voltar para o topo da página para sobrepor
            $pdf->SetY(80);

            // Usar SetAlpha para deixar transparente
            $pdf->SetAlpha(0.15);

            // Colocar imagem no centro (sem flag que cria nova linha)
            $pdf->Image($bgDocPath, 30, 80, 150, 0, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);

            // Restaurar opacidade
            $pdf->SetAlpha(1);
        }

        // Footer
        $pdf->SetY(-30);
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->SetTextColor(85, 85, 85);
        $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
        $pdf->Ln(2);
        $pdf->MultiCell(0, 0, 'Site: www.clinicamaieutica.com.br | Tel: 11 4554.4023 | WhatsApp: 55 11 9 7543.9667', 0, 'C', false, 1);
        $pdf->MultiCell(0, 0, 'Endereço: R. Prof. Edgar de Moraes, 168, Jardim Frediani - Santana de Parnaíba/SP - CEP: 06502-203', 0, 'C', false, 1);

        return $pdf;
    }

    /**
     * Salva o PDF gerado pelo TCPDF no storage
     *
     * @param TCPDF $pdf
     * @param DocumentTemplate $template
     * @param Kid $kid
     * @return string
     */
    protected function saveTCPDF(TCPDF $pdf, DocumentTemplate $template, Kid $kid): string
    {
        // Criar diretório se não existir
        $directory = 'documents/' . $kid->id;
        Storage::makeDirectory($directory);

        // Nome do arquivo
        $fileName = Str::slug($template->name) . '_' . $kid->id . '_' . time() . '.pdf';
        $filePath = $directory . '/' . $fileName;

        // Salvar PDF
        Storage::put($filePath, $pdf->Output('', 'S'));

        return $filePath;
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
        // Criar diret�rio se n�o existir
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
     * Gera um n�mero �nico para o documento.
     *
     * @return string
     */
    protected function generateDocumentNumber(): string
    {
        $count = GeneratedDocument::count() + 1;
        return str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Formata uma data para o padr�o brasileiro (d/m/Y).
     * Trata m�ltiplos formatos de entrada.
     *
     * @param mixed $date
     * @return string
     */
    protected function formatDate($date): string
    {
        if (empty($date)) {
            return '';
        }

        // Se j� � uma inst�ncia de Carbon
        if ($date instanceof Carbon) {
            return $date->format('d/m/Y');
        }

        // Se � uma string, tentar diferentes formatos
        if (is_string($date)) {
            // Tentar formato brasileiro dd/mm/yyyy
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
                try {
                    $carbon = Carbon::createFromFormat('d/m/Y', $date);
                    return $carbon->format('d/m/Y');
                } catch (\Exception $e) {
                    // Se falhar, continua para os pr�ximos formatos
                }
            }

            // Tentar formato ISO Y-m-d ou Y-m-d H:i:s
            try {
                $carbon = Carbon::parse($date);
                return $carbon->format('d/m/Y');
            } catch (\Exception $e) {
                // Se tudo falhar, retornar a string original
                return $date;
            }
        }

        // Fallback: retornar vazio
        return '';
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
     * Visualiza um documento gerado inline no navegador.
     *
     * @param GeneratedDocument $document
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function previewDocument(GeneratedDocument $document)
    {
        $filePath = Storage::path($document->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'Arquivo PDF n�o encontrado.');
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $document->file_name . '"'
        ]);
    }

    /**
     * Deleta o arquivo PDF do storage ao deletar o registro.
     *
     * @param GeneratedDocument $document
     * @return bool
     */
    public function deleteDocument(GeneratedDocument $document): bool
    {
        // Deletar arquivo f�sico
        if (Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }

        // Soft delete do registro
        return $document->delete();
    }
}
