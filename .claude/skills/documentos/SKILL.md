---
description: Geração de PDFs, 6 modelos, DomPDF, polimorfismo
---

Leia `docs/documentos.md` na íntegra. Use-o para responder perguntas sobre geração de documentos PDF.

## Fluxo de Geração

1. Profissional preenche formulário (Blade) com dados do paciente
2. Controller gera HTML a partir do template
3. HTML armazenado em `generated_documents` (polimórfico: `documentable_type` = Kid ou User)
4. PDF gerado sob demanda via DomPDF quando solicitado download

## 6 Modelos de Documentos

Cada modelo tem formulário (`showFormModeloN`) e gerador (`modeloN`) no `DocumentsController`.

## Regras Técnicas

**Templates:**
- Todos estendem `documents.layouts.pdf-base` — estilos compartilhados aqui
- Fonte obrigatória: `DejaVu Sans` (requisito do DomPDF para caracteres especiais)
- CSS inline nos templates (DomPDF não suporta CSS externo completo)
- Imagens em base64 (DomPDF não carrega URLs externas)

**Polimorfismo:**
- `documentable_type`: `App\Models\Kid` ou `App\Models\User`
- `documentable_id`: ID do paciente
- Dados do paciente derivados do modelo via relationship (não duplicados)

**Permissões:**
- `document-list`, `document-show`, `document-download`, `document-delete`
- `document-list-all`, `document-show-all`, `document-delete-all` (admin)

**Armazenamento:** HTML em banco (`generated_documents.content`), PDF gerado on-demand — não armazena arquivo PDF.

Após alterar templates de PDF: `php artisan view:clear`.
