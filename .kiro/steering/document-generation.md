---
inclusion: manual
---

# Geração de Documentos

## Visão Geral

O sistema gera 6 modelos de documentos clínicos em PDF via DomPDF. O fluxo é sempre:

```
Formulário (Blade) → POST → Controller valida → renderiza Blade para HTML string
→ salva HTML + form_data em generated_documents → DomPDF converte → download forçado
```

O HTML renderizado é persistido no banco (`html_content`). O download posterior **re-renderiza** o template Blade com os dados originais — não usa o HTML salvo — garantindo que melhorias no layout sejam aplicadas retroativamente.

---

## Os 6 Modelos

| `model_type` | Nome | Campos específicos | Profissionais |
|---|---|---|---|
| 1 | Declaração | `dias_horarios`, `previsao_termino` (opcional) | 1 (do paciente) |
| 2 | Declaração Simplificada | `mes_inicio` (auto: `kid->created_at`) | 1 (do paciente) |
| 3 | Laudo Psicológico | `nome_informante`, `sintomas`, `consequencias`, `hipotese_diagnostico`, `numero_encontros`, `duracao_horas`, `procedimentos_texto`, `analise_texto`, `diagnostico`, `sintoma_principal`, `cid`, `referencias` | 1..N (selecionáveis) |
| 4 | Parecer Psicológico | `solicitante`*, `finalidade`*, `descricao_demanda`*, `analise`*, `conclusao`*, `referencias`* | 1..N (selecionáveis) |
| 5 | Relatório Multiprofissional | `descricao_demanda`*, `procedimentos_texto`*, `analise`*, `conclusao`*, `numero_encontros`, `duracao_horas` | 1..N (**obrigatório**) |
| 6 | Relatório Psicológico | `solicitante`, `finalidade`, `descricao_demanda`*, `numero_encontros`, `duracao_horas`, `procedimentos_texto`*, `analise`*, `conclusao`* | 1 (do paciente) |

`*` = campo obrigatório na validação.

---

## Schema da Tabela `generated_documents`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint PK | |
| `model_type` | tinyint | 1–6 |
| `documentable_id` | bigint | ID do paciente (polimórfico) |
| `documentable_type` | string | Classe do paciente — sempre `App\Models\Kid` na prática |
| `professional_id` | FK nullable | Profissional que assina (`professionals.id`) |
| `generated_by` | FK | Usuário que gerou (`users.id`) |
| `html_content` | longText | HTML renderizado no momento da geração |
| `form_data` | JSON | Dados originais do request (para re-renderização) |
| `metadata` | JSON | `ip`, `user_agent`, `document_title` |
| `generated_at` | timestamp | Momento da geração |
| `created_by`, `updated_by`, `deleted_by` | FK nullable | Auditoria |
| `deleted_at` | timestamp | SoftDeletes |

---

## Model: `GeneratedDocument`

### Relacionamentos

```php
$doc->documentable()   // morphTo → Kid (ou User, mas na prática sempre Kid)
$doc->professional()   // belongsTo Professional (quem assina)
$doc->generatedBy()    // belongsTo User (quem gerou)
```

### Accessors

```php
$doc->model_type_name  // 'Declaração - Modelo 1', 'Laudo Psicológico - Modelo 3', etc.
$doc->filename         // nome do arquivo PDF: '{tipo}_{data}.pdf' (sem acentos, snake_case)
```

### Scopes

```php
GeneratedDocument::forKids()       // documentable_type = Kid::class
GeneratedDocument::forUsers()      // documentable_type = User::class
GeneratedDocument::forAuthUser()   // filtra por permissão do usuário autenticado
```

**`forAuthUser()` — lógica de visibilidade:**
- Admin (`document-list-all`) → vê tudo.
- Profissional → vê documentos que **ele gerou** OU onde `professional_id = seu professional->id`.
- Outros → apenas os que geraram (`generated_by = auth()->id()`).

---

## Fluxo de Geração (por modelo)

### Modelos 1 e 2 (simples — 1 profissional)

```php
// Controller usa helpers privados
$kid  = $this->getKidWithRelations($request->kid_id);  // eager load professionals.user
$data = array_merge(
    $this->getCommonDocumentData($kid),  // nome_paciente, nome_psicologo, council, crp, cidade, data_formatada
    $this->prepareAssets(),              // watermark, logo (base64)
    [ /* campos específicos do modelo */ ]
);
$html = view('documents.modelo1', $data)->render();
GeneratedDocument::create([...]);
return Pdf::loadHTML($html)->setPaper('A4', 'portrait')->download('nome.pdf');
```

### Modelos 3, 4, 5 (múltiplos profissionais)

- Formulário tem campo `professionals[]` (array de IDs).
- Se nenhum selecionado → usa o primeiro profissional vinculado ao paciente.
- Modelo 5: `professionals` é **obrigatório** (`required|array|min:1`).
- Array `$professionalsData` é passado ao template: `[['name', 'council', 'crp', 'city'], ...]`.
- Assinatura usa `$professionalsData[0]` como profissional principal.
- **Logo diferente:** modelos 3, 4, 5 usam `logo-doc.jpg`; modelos 1, 2, 6 usam `logotipo.png`.

### Modelo 6 (relatório individual)

- Usa `getCommonDocumentData()` + campos específicos via `array_merge`.
- Profissional sempre o primeiro vinculado ao paciente.

---

## Download / Re-renderização

**Rota:** `GET /documentos/{id}/download` → `DocumentsController@download`

O download **não usa** o `html_content` salvo. Ele chama `reRenderDocument()` que:
1. Recarrega o `Kid` com relações.
2. Recupera `form_data` do banco.
3. Reconstrói os assets (base64 das imagens).
4. Chama o template Blade correto com os dados originais.

Isso garante que correções no layout, CSS ou assinatura sejam aplicadas em downloads futuros sem precisar regravar o documento.

**Exceção:** se `model_type` for desconhecido, retorna o `html_content` salvo como fallback.

---

## Layout Base dos PDFs (`documents.layouts.pdf-base`)

Todos os modelos estendem este layout. Ele fornece:

- **Fonte:** `DejaVu Sans` — obrigatório para DomPDF (não usar Nunito ou outras fontes web).
- **Tamanho base:** 14px (DomPDF não suporta `rem` — usar sempre `px`).
- **Header fixo:** logo centralizado (`logotipo.png` ou `logo-doc.jpg` em base64).
- **Footer fixo:** site, telefone, WhatsApp e endereço da clínica.
- **Marca d'água:** `bg-doc.png` em base64, posição fixa, opacidade 0.60.
- **Página de assinatura:** `page-break-before: always`, posicionada no rodapé da última página.
- **Assinatura padrão:** nome em maiúsculas + `{council}: {crp}` (ex: `CRP: 06/12345`).
- **Data/local:** `{cidade}, {data_formatada}.` alinhado à direita.

### Seções Blade disponíveis

| Seção | Padrão | Uso |
|-------|--------|-----|
| `document-title` | `'Documento'` | `<title>` do HTML |
| `title` | `'DECLARAÇÃO'` | Título centralizado em maiúsculas |
| `content` | — | Conteúdo principal do documento |
| `signature` | Nome + council/CRP | Substituir para múltiplas assinaturas |
| `date-location` | Cidade + data | Substituir se necessário |
| `pdf-styles` | — | CSS adicional específico do modelo |

### Classes CSS disponíveis nos templates

```css
.pdf-fs-xs    /* 11px — captions, rodapé */
.pdf-fs-sm    /* 12px — referências, notas */
.pdf-fs-base  /* 14px — corpo de texto */
.pdf-fs-md    /* 16px — destaque */
.pdf-section-title  /* h3 numerado: bold, 14px, margin-top 25px */
.pdf-text     /* text-align: justify */
.pdf-note     /* 11px, itálico */
.pdf-reference /* 12px, justify */
```

---

## Dados Comuns (`getCommonDocumentData`)

Todos os modelos recebem estas variáveis via `getCommonDocumentData($kid)`:

```php
[
    'nome_paciente'  => strtoupper($kid->name),
    'nome_psicologo' => strtoupper($user->name),   // primeiro profissional do kid
    'council'        => $professional->council_label ?? 'Reg.',
    'crp'            => $professional->registration_number ?? 'N/A',
    'cidade'         => $user->city ?? 'Santana de Parnaíba',
    'data_formatada' => now()->locale('pt_BR')->isoFormat('D [de] MMMM [de] YYYY'),
]
```

E via `prepareAssets()`:

```php
[
    'watermark' => base64_encode(file_get_contents(public_path('images/bg-doc.png'))),
    'logo'      => base64_encode(file_get_contents(public_path('images/logotipo.png'))),
]
```

**Fallback de cidade:** `$user->city ?? 'Santana de Parnaíba'` — sempre presente.

---

## Autorização

### Permissões

| Permissão | Descrição |
|-----------|-----------|
| `document-list` | Listar documentos próprios |
| `document-list-all` | Listar todos + ver lixeira |
| `document-show` | Ver documento próprio ou de paciente vinculado |
| `document-show-all` | Ver qualquer documento |
| `document-delete` | Deletar apenas os que gerou |
| `document-delete-all` | Deletar qualquer documento |

**Download** usa a mesma lógica de `view` — quem pode ver, pode baixar.

**Quem pode ver um documento (Policy):**
1. Admin (`document-show-all`) → qualquer um.
2. Usuário que gerou (`generated_by === user->id`).
3. Profissional cujo `professional_id` está no documento.
4. Profissional vinculado ao `Kid` do documento.

---

## Rotas

```
GET  /documentos                          index (galeria de modelos)
GET  /documentos/modelo1                  showFormModelo1
POST /documentos/modelo1                  modelo1 (gera + download)
GET  /documentos/modelo2                  showFormModelo2
POST /documentos/modelo2                  modelo2
GET  /documentos/modelo3                  showFormModelo3
POST /documentos/modelo3                  modelo3
GET  /documentos/modelo4                  showFormModelo4
POST /documentos/modelo4                  modelo4
GET  /documentos/modelo5                  showFormModelo5
POST /documentos/modelo5                  modelo5
GET  /documentos/modelo6                  showFormModelo6
POST /documentos/modelo6                  modelo6
GET  /documentos/historico                history (listagem com filtros)
GET  /documentos/{id}/download            download (re-renderiza + PDF)
```

---

## Regras de Negócio Críticas

1. **Sempre salvar antes de retornar o PDF** — `GeneratedDocument::create()` deve ser chamado antes de `$pdf->download()`. Nunca retornar o PDF sem persistir o registro.
2. **Download re-renderiza, não usa `html_content`** — o campo `html_content` é apenas para auditoria/snapshot. O download sempre reconstrói via Blade + `form_data`.
3. **`form_data` deve conter todos os campos do request** (exceto `_token`) — é a fonte de verdade para re-renderização futura.
4. **Fonte obrigatória: `DejaVu Sans`** — DomPDF não suporta Google Fonts. Nunca usar `Nunito` ou outras fontes web em templates PDF.
5. **Tamanhos em `px`, não `rem`** — DomPDF não suporta unidades relativas.
6. **Nomes em maiúsculas** — `nome_paciente` e `nome_psicologo` sempre via `strtoupper()`.
7. **Logo diferente para modelos 3/4/5** — usar `logo-doc.jpg`; modelos 1/2/6 usam `logotipo.png`.
8. **Modelo 5 exige ao menos 1 profissional** — validação `required|array|min:1` em `professionals`.
9. **Fallback de profissional** — se nenhum `professionals[]` enviado nos modelos 3/4, usar o primeiro profissional vinculado ao kid. Nunca quebrar por ausência de profissional.
10. **`Kid::getKids()`** — usar este scope para popular os selects de paciente nos formulários (respeita filtros de visibilidade por perfil).
11. **Assets em base64** — imagens são embutidas no HTML via base64 para garantir que o PDF funcione sem dependência de paths do servidor.
12. **`metadata` sempre inclui** `ip`, `user_agent` e `document_title` — preencher em toda geração.
