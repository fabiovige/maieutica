<?php

namespace App\Contracts;

use App\Models\Kid;

interface DocumentTemplate
{
    /**
     * Regras de validação aplicadas apenas na geração inicial (via Request).
     */
    public function rules(): array;

    /**
     * View Blade que renderiza o documento.
     */
    public function view(): string;

    /**
     * Título legível, usado em metadata do GeneratedDocument.
     */
    public function title(): string;

    /**
     * Prefixo do nome do arquivo PDF gerado na criação inicial.
     */
    public function filenamePrefix(): string;

    /**
     * Monta os dados para a view a partir do Kid e de form_data.
     * Usado tanto na geração inicial quanto na re-renderização
     * (mesma fonte de dados: form_data armazenado ou recém-validado).
     */
    public function buildData(Kid $kid, array $formData): array;

    /**
     * Profissional a ser vinculado ao GeneratedDocument.
     */
    public function resolveProfessionalId(Kid $kid, array $formData): ?int;
}
