# Sistema de Armazenamento de Documentos Gerados

## Visão Geral

Este documento descreve o plano de implementação do **Sistema de Histórico de Documentos Gerados**, que permite armazenar o HTML renderizado de documentos no banco de dados e regenerar PDFs on-demand, mantendo um histórico completo com auditoria e rastreamento.

## Índice

1. [Contexto e Motivação](#contexto-e-motivação)
2. [Arquitetura da Solução](#arquitetura-da-solução)
3. [Estrutura do Banco de Dados](#estrutura-do-banco-de-dados)
4. [Camada de Modelo](#camada-de-modelo)
5. [Camada de Autorização](#camada-de-autorização)
6. [Camada de Controle](#camada-de-controle)
7. [Rotas](#rotas)
8. [Interface de Usuário](#interface-de-usuário)
9. [Testes](#testes)
10. [Deploy](#deploy)
11. [Performance e Escalabilidade](#performance-e-escalabilidade)
12. [Melhorias Futuras](#melhorias-futuras)

---

## Contexto e Motivação

### Situação Atual

O sistema possui 6 modelos de documentos (Declarações, Laudos, Pareceres e Relatórios) acessíveis em `/documents`:

1. **Modelo 1** - Declaração
2. **Modelo 2** - Declaração Simplificada
3. **Modelo 3** - Laudo Psicológico
4. **Modelo 4** - Parecer Psicológico
5. **Modelo 5** - Relatório Multiprofissional
6. **Modelo 6** - Relatório Psicológico (Individual)

**Fluxo Atual:**
```
Usuário preenche formulário → POST /documents/modeloX →
Sistema gera PDF em memória → Stream para navegador →
PDF é perdido (não armazenado)
```

### Problema

- Documentos não são armazenados
- Sem histórico de documentos gerados
- Sem auditoria (quem gerou, quando, para quem)
- Impossível re-gerar PDF sem preencher formulário novamente
- Sem rastreamento para conformidade legal

### Solução Proposta

Armazenar o **HTML renderizado** (não o arquivo PDF) no banco de dados, permitindo:

- ✅ Histórico completo de documentos gerados
- ✅ Regeneração ilimitada de PDFs a partir do HTML salvo
- ✅ Auditoria completa (quem, quando, para quem, de onde)
- ✅ Zero armazenamento em disco (PDF gerado em memória)
- ✅ Relacionamento polimórfico (Kids e Users)
- ✅ Compatibilidade total com sistema existente

---

## Arquitetura da Solução

### Fluxo Atual (Sem Armazenamento)

```
┌─────────────┐     ┌──────────────────┐     ┌─────────────┐
│ Formulário  │────→│  Controller      │────→│  DomPDF     │
│ form-modeloX│     │  modeloX()       │     │  loadView() │
└─────────────┘     └──────────────────┘     └─────────────┘
                                                     │
                                                     ▼
                                              ┌─────────────┐
                                              │ Stream PDF  │
                                              │ (Navegador) │
                                              └─────────────┘
                                                     │
                                                     ▼
                                               PDF perdido
```

### Novo Fluxo (Com Armazenamento)

```
┌─────────────┐     ┌──────────────────────────────┐
│ Formulário  │────→│  Controller modeloX()        │
│ form-modeloX│     │                              │
└─────────────┘     │  1. Renderiza Blade → HTML  │
                    │  2. Salva HTML no banco      │
                    │  3. Gera PDF do HTML         │
                    │  4. Stream PDF               │
                    └──────────────────────────────┘
                                │              │
                                ▼              ▼
                    ┌─────────────────┐  ┌─────────────┐
                    │ generated_      │  │ Stream PDF  │
                    │ documents       │  │ (Navegador) │
                    │                 │  └─────────────┘
                    │ - html_content  │
                    │ - form_data     │
                    │ - metadata      │
                    └─────────────────┘
                                │
                                │
            ┌───────────────────┴────────────────────┐
            ▼                                        ▼
    ┌──────────────────┐                  ┌──────────────────┐
    │ Download Futuro  │                  │ Histórico/Lista  │
    │                  │                  │                  │
    │ Busca HTML       │                  │ DataTables com   │
    │ Regenera PDF     │                  │ filtros e busca  │
    └──────────────────┘                  └──────────────────┘
```

**Vantagens:**
- HTML é compacto (~20KB por documento vs. ~200KB para PDF)
- PDF sempre gerado fresh (sem cache corrompido)
- Permite atualizar template Blade e re-gerar documentos antigos
- Auditoria completa via `form_data` JSON

---

## Estrutura do Banco de Dados

### Migration: `create_generated_documents_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('generated_documents', function (Blueprint $table) {
            $table->id();

            // Tipo de documento (1-6 = modelo 1-6)
            $table->tinyInteger('model_type')
                ->comment('1=Declaração, 2=Decl.Simples, 3=Laudo, 4=Parecer, 5=Multip, 6=Relatório');

            // Relacionamento polimórfico - pode ser Kid ou User
            $table->morphs('documentable'); // documentable_id + documentable_type

            // Profissional que assina o documento
            $table->foreignId('professional_id')
                ->nullable()
                ->constrained('professionals')
                ->nullOnDelete();

            // Usuário que gerou o documento
            $table->foreignId('generated_by')
                ->constrained('users')
                ->cascadeOnDelete();

            // Armazenamento do HTML renderizado (núcleo da solução)
            $table->longText('html_content')
                ->comment('HTML renderizado do Blade para regeneração de PDF');

            // Dados do formulário original (JSON) para auditoria
            $table->json('form_data')
                ->nullable()
                ->comment('Dados originais do request para referência');

            // Metadata adicional (JSON)
            $table->json('metadata')
                ->nullable()
                ->comment('IP, user_agent, document_title, etc.');

            // Timestamps de auditoria
            $table->timestamp('generated_at')->useCurrent();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Índices para performance
            $table->index(['documentable_id', 'documentable_type'], 'idx_documentable');
            $table->index('model_type');
            $table->index('professional_id');
            $table->index('generated_by');
            $table->index('generated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('generated_documents');
    }
};
```

### Campos Principais

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint | Chave primária |
| `model_type` | tinyint | 1-6 (modelo1 até modelo6) |
| `documentable_id` | bigint | ID do Kid ou User |
| `documentable_type` | string | `App\Models\Kid` ou `App\Models\User` |
| `professional_id` | bigint | Profissional que assina |
| `generated_by` | bigint | Usuário que gerou (pode diferir do profissional) |
| `html_content` | longtext | HTML renderizado (até 4GB) |
| `form_data` | json | Dados originais do formulário |
| `metadata` | json | IP, user_agent, título, etc. |
| `generated_at` | timestamp | Data/hora de geração |
| `created_by/updated_by/deleted_by` | bigint | Auditoria (BaseModel) |
| `timestamps` | timestamp | created_at, updated_at |
| `deleted_at` | timestamp | Soft delete |

### Índices de Performance

- `idx_documentable`: Busca por paciente/usuário (polimórfico)
- `model_type`: Filtro por tipo de documento
- `professional_id`: Filtro por profissional
- `generated_by`: Filtro por gerador
- `generated_at`: Ordenação por data

**Performance esperada:**
- Listagem com 1.000 documentos: ~50-100ms
- Download/regeneração: ~200-300ms

---

## Camada de Modelo

### Model: `GeneratedDocument.php`

**Arquivo:** `app/Models/GeneratedDocument.php`

```php
<?php

namespace App\Models;

class GeneratedDocument extends BaseModel
{
    protected $fillable = [
        'model_type',
        'documentable_id',
        'documentable_type',
        'professional_id',
        'generated_by',
        'html_content',
        'form_data',
        'metadata',
        'generated_at',
    ];

    protected $casts = [
        'form_data' => 'array',
        'metadata' => 'array',
        'generated_at' => 'datetime',
    ];

    // Relacionamento polimórfico
    public function documentable()
    {
        return $this->morphTo();
    }

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // Accessor para nome legível do tipo
    public function getModelTypeNameAttribute()
    {
        return match($this->model_type) {
            1 => 'Declaração - Modelo 1',
            2 => 'Declaração Simplificada - Modelo 2',
            3 => 'Laudo Psicológico - Modelo 3',
            4 => 'Parecer Psicológico - Modelo 4',
            5 => 'Relatório Multiprofissional - Modelo 5',
            6 => 'Relatório Psicológico - Modelo 6',
            default => 'Documento Desconhecido',
        };
    }

    // Accessor para nome do arquivo PDF
    public function getFilenameAttribute()
    {
        $type = str_replace([' - Modelo ', ' '], ['_', '_'], $this->model_type_name);
        $type = strtolower(remove_accents($type));
        $date = $this->generated_at->format('Y-m-d');
        return "{$type}_{$date}.pdf";
    }

    // Scopes para filtrar por tipo de documentable
    public function scopeForKids($query)
    {
        return $query->where('documentable_type', Kid::class);
    }

    public function scopeForUsers($query)
    {
        return $query->where('documentable_type', User::class);
    }

    // Scope para respeitar permissões do usuário autenticado
    public function scopeForAuthUser($query)
    {
        $user = auth()->user();

        // Admin vê tudo
        if ($user->can('document-list-all')) {
            return $query;
        }

        // Profissional vê documentos gerados por ele ou de seus pacientes
        $professional = $user->professional->first();

        if ($professional) {
            return $query->where(function($q) use ($user, $professional) {
                $q->where('generated_by', $user->id)
                  ->orWhere('professional_id', $professional->id);
            });
        }

        // Outros veem apenas os que geraram
        return $query->where('generated_by', $user->id);
    }
}
```

### Relações Inversas

**Kid.php** - Adicionar:
```php
public function generatedDocuments()
{
    return $this->morphMany(GeneratedDocument::class, 'documentable');
}
```

**User.php** - Adicionar:
```php
public function generatedDocuments()
{
    return $this->morphMany(GeneratedDocument::class, 'documentable');
}
```

---

## Camada de Autorização

### Permissões

| Permissão | Descrição |
|-----------|-----------|
| `document-list` | Listar próprios documentos gerados |
| `document-list-all` | Listar TODOS documentos (admin) |
| `document-show` | Visualizar próprios documentos |
| `document-show-all` | Visualizar TODOS documentos (admin) |
| `document-download` | Download/regenerar PDF |
| `document-delete` | Soft delete próprios documentos |
| `document-delete-all` | Soft delete QUALQUER documento (admin) |

### Atribuição de Permissões

**Admin:**
- Todas as permissões (incluindo `-all`)

**Profissional:**
- `document-list`
- `document-show`
- `document-download`

**Responsável:**
- Nenhuma permissão de documentos (não gera documentos profissionais)

### Policy: `GeneratedDocumentPolicy.php`

**Arquivo:** `app/Policies/GeneratedDocumentPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\GeneratedDocument;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GeneratedDocumentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('document-list') || $user->can('document-list-all');
    }

    public function view(User $user, GeneratedDocument $document): bool
    {
        // Admin vê tudo
        if ($user->can('document-show-all')) {
            return true;
        }

        // Requer permissão base
        if (!$user->can('document-show')) {
            return false;
        }

        // Pode ver se gerou
        if ($document->generated_by === $user->id) {
            return true;
        }

        // Profissional pode ver documentos que assinou
        $professional = $user->professional->first();
        if ($professional && $document->professional_id === $professional->id) {
            return true;
        }

        // Se for documento de Kid, profissional vinculado pode ver
        if ($document->documentable_type === \App\Models\Kid::class) {
            $kid = $document->documentable;
            if ($professional && $kid->professionals->contains($professional->id)) {
                return true;
            }
        }

        return false;
    }

    public function delete(User $user, GeneratedDocument $document): bool
    {
        if ($user->can('document-delete-all')) {
            return true;
        }

        return $user->can('document-delete') && $document->generated_by === $user->id;
    }

    public function viewTrash(User $user): bool
    {
        return $user->can('document-list-all');
    }

    public function restore(User $user, GeneratedDocument $document): bool
    {
        return $user->can('document-delete-all');
    }

    public function forceDelete(User $user, GeneratedDocument $document): bool
    {
        return $user->can('document-delete-all');
    }

    public function download(User $user, GeneratedDocument $document): bool
    {
        // Mesma lógica de view - quem pode ver, pode baixar
        return $this->view($user, $document);
    }
}
```

**Registro no AuthServiceProvider:**

```php
protected $policies = [
    // ... políticas existentes
    \App\Models\GeneratedDocument::class => \App\Policies\GeneratedDocumentPolicy::class,
];
```

---

## Camada de Controle

### Modificações no DocumentsController

**Adicionar import:**
```php
use App\Models\GeneratedDocument;
```

### Padrão para os 6 Métodos `modeloX()`

**ANTES:**
```php
public function modelo1(Request $request)
{
    $request->validate([...]);
    $kid = $this->getKidWithRelations($request->kid_id);
    $data = array_merge(
        $this->getCommonDocumentData($kid),
        $this->prepareAssets(),
        [...]
    );

    $pdf = Pdf::loadView('documents.modelo1', $data)
        ->setPaper('A4', 'portrait');

    return $pdf->stream('declaracao_modelo_1.pdf');
}
```

**DEPOIS:**
```php
public function modelo1(Request $request)
{
    $request->validate([...]);
    $kid = $this->getKidWithRelations($request->kid_id);
    $data = array_merge(
        $this->getCommonDocumentData($kid),
        $this->prepareAssets(),
        [...]
    );

    // NOVO: Renderizar HTML para string
    $html = view('documents.modelo1', $data)->render();

    // NOVO: Salvar no banco de dados
    $document = GeneratedDocument::create([
        'model_type' => 1,
        'documentable_id' => $kid->id,
        'documentable_type' => Kid::class,
        'professional_id' => $kid->professionals->first()?->id,
        'generated_by' => auth()->id(),
        'html_content' => $html,
        'form_data' => $request->except(['_token']),
        'metadata' => [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'document_title' => 'Declaração - Modelo 1',
        ],
        'generated_at' => now(),
    ]);

    // MODIFICADO: Gerar PDF do HTML (não da view)
    $pdf = Pdf::loadHTML($html)->setPaper('A4', 'portrait');

    return $pdf->stream('declaracao_modelo_1.pdf');
}
```

### Mapeamento por Modelo

| Método | model_type | metadata.document_title |
|--------|------------|-------------------------|
| `modelo1()` | 1 | Declaração - Modelo 1 |
| `modelo2()` | 2 | Declaração Simplificada - Modelo 2 |
| `modelo3()` | 3 | Laudo Psicológico - Modelo 3 |
| `modelo4()` | 4 | Parecer Psicológico - Modelo 4 |
| `modelo5()` | 5 | Relatório Multiprofissional - Modelo 5 |
| `modelo6()` | 6 | Relatório Psicológico - Modelo 6 |

### Novos Métodos

**Adicionar no final do DocumentsController:**

```php
/**
 * Exibe histórico de documentos gerados
 */
public function history(Request $request)
{
    $this->authorize('viewAny', GeneratedDocument::class);

    if ($request->ajax()) {
        return $this->historyData($request);
    }

    return view('documents.history');
}

/**
 * Dados para DataTables (server-side)
 */
public function historyData(Request $request)
{
    $query = GeneratedDocument::with(['documentable', 'professional.user', 'generatedBy'])
        ->forAuthUser(); // Aplica filtro de permissões

    return datatables()->eloquent($query)
        ->addColumn('documentable_name', function ($doc) {
            return $doc->documentable ? $doc->documentable->name : 'N/A';
        })
        ->addColumn('professional_name', function ($doc) {
            $user = $doc->professional?->user->first();
            return $user ? $user->name : 'N/A';
        })
        ->addColumn('action', function ($doc) {
            $btn = '<a href="' . route('documentos.download', $doc->id) . '"
                       class="btn btn-sm btn-primary" title="Download PDF">
                        <i class="bi bi-download"></i>
                    </a>';

            if (auth()->user()->can('delete', $doc)) {
                $btn .= ' <button class="btn btn-sm btn-danger delete-doc"
                                  data-id="' . $doc->id . '" title="Excluir">
                            <i class="bi bi-trash"></i>
                         </button>';
            }

            return $btn;
        })
        ->rawColumns(['action'])
        ->make(true);
}

/**
 * Download/regenera PDF a partir do HTML armazenado
 */
public function download(GeneratedDocument $document)
{
    $this->authorize('download', $document);

    // Regenera PDF do HTML salvo
    $pdf = Pdf::loadHTML($document->html_content)
        ->setPaper('A4', 'portrait');

    return $pdf->stream($document->filename);
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
        'message' => 'Documento excluído com sucesso.'
    ]);
}
```

---

## Rotas

**Arquivo:** `routes/web.php`

**Adicionar APÓS linha 131 (após rotas existentes de documentos):**

```php
// Histórico e gerenciamento de documentos gerados
Route::get('/documents/history', [DocumentsController::class, 'history'])
    ->name('documentos.history');
Route::get('/documents/history/datatable', [DocumentsController::class, 'historyData'])
    ->name('documentos.history.datatable');
Route::get('/documents/{document}/download', [DocumentsController::class, 'download'])
    ->name('documentos.download');
Route::delete('/documents/{document}', [DocumentsController::class, 'destroy'])
    ->name('documentos.destroy');
```

**Rotas existentes permanecem inalteradas** - compatibilidade 100%.

---

## Interface de Usuário

### View: `history.blade.php`

**Arquivo:** `resources/views/documents/history.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Histórico de Documentos Gerados')

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('documentos.index') }}">Documentos</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Histórico</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Histórico de Documentos Gerados
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="documents-table" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tipo</th>
                                    <th>Paciente/Usuário</th>
                                    <th>Profissional</th>
                                    <th>Gerado Por</th>
                                    <th>Data Geração</th>
                                    <th width="120">Ações</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const table = $('#documents-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('documentos.history.datatable') }}",
        columns: [
            { data: 'id', name: 'id', width: '50px' },
            { data: 'model_type_name', name: 'model_type' },
            { data: 'documentable_name', name: 'documentable_name', orderable: false },
            { data: 'professional_name', name: 'professional_name', orderable: false },
            { data: 'generated_by.name', name: 'generatedBy.name' },
            { data: 'generated_at', name: 'generated_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[5, 'desc']], // Ordenar por data DESC
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
        }
    });

    // Handler de exclusão
    $('#documents-table').on('click', '.delete-doc', function() {
        const docId = $(this).data('id');

        Swal.fire({
            title: 'Tem certeza?',
            text: "O documento será movido para a lixeira.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/documents/${docId}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire('Excluído!', response.message, 'success');
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire('Erro!', 'Não foi possível excluir o documento.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
```

### Atualização do Index

**Arquivo:** `resources/views/documents/index.blade.php`

Adicionar botão no topo:

```blade
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="bi bi-file-earmark-text"></i> Modelos de Documentos</h3>
    @can('document-list')
        <a href="{{ route('documentos.history') }}" class="btn btn-outline-primary">
            <i class="bi bi-clock-history"></i> Ver Histórico
        </a>
    @endcan
</div>
```

### Menu de Navegação

Modificar menu "Documentos" para dropdown (localização: `layouts/app.blade.php` ou similar):

```blade
@can('document-list')
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button"
           data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-file-earmark-text"></i> Documentos
        </a>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item" href="{{ route('documentos.index') }}">
                    <i class="bi bi-plus-circle"></i> Gerar Documentos
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('documentos.history') }}">
                    <i class="bi bi-clock-history"></i> Histórico
                </a>
            </li>
        </ul>
    </li>
@endcan
```

---

## Testes

### Checklist de Testes Manuais

#### Fase 1: Geração e Armazenamento
- [ ] Gerar Modelo 1 - verificar salvamento no banco
- [ ] Gerar Modelo 2 - verificar salvamento no banco
- [ ] Gerar Modelo 3 - verificar salvamento no banco
- [ ] Gerar Modelo 4 - verificar salvamento no banco
- [ ] Gerar Modelo 5 - verificar salvamento no banco
- [ ] Gerar Modelo 6 - verificar salvamento no banco
- [ ] Verificar `html_content` salvo corretamente
- [ ] Verificar `form_data` JSON contém dados do formulário
- [ ] Verificar `professional_id` e `generated_by` populados
- [ ] PDF ainda abre no navegador (compatibilidade)

#### Fase 2: Histórico
- [ ] Acessar `/documents/history` - página carrega
- [ ] DataTable exibe documentos gerados
- [ ] Filtro por tipo funciona
- [ ] Ordenação por data funciona
- [ ] Paginação funciona

#### Fase 3: Download
- [ ] Clicar em download regenera PDF
- [ ] PDF regenerado é idêntico ao original
- [ ] Filename correto (model_type_YYYY-MM-DD.pdf)
- [ ] Download funciona múltiplas vezes

#### Fase 4: Autorização
- [ ] Admin vê todos os documentos
- [ ] Profissional vê apenas próprios + pacientes
- [ ] Usuário sem permissão não acessa histórico
- [ ] Exclusão funciona apenas para documentos próprios (ou admin)

#### Fase 5: Performance
- [ ] Gerar 50+ documentos
- [ ] Medir tempo de load do histórico (< 2s esperado)
- [ ] Medir tempo de download (< 500ms esperado)
- [ ] Verificar queries N+1 (usar Laravel Debugbar)

### SQL para Verificação Manual

```sql
-- Verificar documentos salvos
SELECT
    id,
    model_type,
    documentable_type,
    professional_id,
    generated_by,
    LENGTH(html_content) as html_size_bytes,
    generated_at
FROM generated_documents
ORDER BY generated_at DESC
LIMIT 10;

-- Verificar metadata JSON
SELECT
    id,
    model_type,
    JSON_EXTRACT(metadata, '$.document_title') as title,
    JSON_EXTRACT(metadata, '$.ip') as ip,
    generated_at
FROM generated_documents
LIMIT 10;

-- Contar documentos por tipo
SELECT
    model_type,
    COUNT(*) as total,
    AVG(LENGTH(html_content)) as avg_html_size
FROM generated_documents
GROUP BY model_type;
```

---

## Deploy

### Pré-Deploy (Staging)

1. **Backup do banco:**
   ```bash
   mysqldump -u user -p maieutica > backup_pre_documents_$(date +%Y%m%d).sql
   ```

2. **Testar em staging:**
   - Rodar migrations
   - Gerar documentos de teste
   - Validar histórico
   - Validar download
   - Validar permissões

3. **Code review:**
   - Todos os 6 métodos `modeloX()` modificados
   - Imports corretos
   - Routes corretas

### Deploy em Produção

```bash
# 1. Código atualizado
git pull origin main

# 2. Dependências
composer install --no-dev --optimize-autoloader

# 3. Migrations
php artisan migrate --force

# 4. Permissions seed
php artisan db:seed --class=RoleAndPermissionSeeder --force

# 5. Cache clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 6. Cache rebuild
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 7. Assets (se necessário)
npm run production
```

### Pós-Deploy

1. Acessar `/documents` - forms funcionam
2. Gerar documento teste - salva no banco
3. Verificar tabela `generated_documents`
4. Acessar `/documents/history` - lista aparece
5. Download funciona
6. Monitorar logs: `tail -f storage/logs/laravel.log`

### Rollback

**Se houver problemas:**

1. **NÃO reverter migrations** (dados já criados)
2. **Desabilitar rotas:**
   ```php
   // Comentar em web.php:
   // Route::get('/documents/history', ...);
   ```
3. **Reverter controller:**
   - Remover save HTML
   - Restaurar `Pdf::loadView()`
4. **Sistema volta ao normal**
5. **Corrigir offline**
6. **Re-deploy**

**Rollback completo (APENAS se tabela vazia):**
```bash
php artisan migrate:rollback --step=1
# WARNING: Perde documentos salvos!
```

---

## Performance e Escalabilidade

### Armazenamento

**Tamanho médio por modelo:**
- Modelo 1-2: ~10-15 KB
- Modelo 3-6: ~20-40 KB

**Projeções:**
- 1.000 documentos = ~20-40 MB
- 10.000 documentos = ~200-400 MB
- 100.000 documentos = ~2-4 GB

**Limite MySQL LONGTEXT:** 4 GB

### Performance de Queries

**Índices criados:**
- `idx_documentable`: Busca por paciente (composto)
- `model_type`: Filtro por tipo
- `professional_id`: Filtro por profissional
- `generated_by`: Filtro por autor
- `generated_at`: Ordenação por data

**Benchmarks esperados:**
- Listagem 1.000 docs: ~50-100ms
- Download/regeneração: ~200-300ms

### Otimizações Futuras

**Cache de PDFs regenerados:**
```php
public function download(GeneratedDocument $document)
{
    $cacheKey = "pdf_doc_{$document->id}";

    $pdf = Cache::remember($cacheKey, 3600, function() use ($document) {
        return Pdf::loadHTML($document->html_content)
            ->setPaper('A4', 'portrait')
            ->output();
    });

    return response($pdf, 200)->header('Content-Type', 'application/pdf');
}
```

**Queue para documentos grandes:**
```php
dispatch(new GenerateDocumentJob($document));
```

---

## Melhorias Futuras

### Features
1. Envio de documentos por e-mail
2. Download em lote (ZIP)
3. Versionamento de templates (regenerar com novo template)
4. Assinatura digital integrada
5. Exportação do histórico (CSV/Excel)
6. Busca full-text em `form_data` JSON
7. Arquivamento automático após X meses
8. Compartilhamento de link público (com token)

### Refatorações Técnicas
1. Service layer: `DocumentGenerationService`
2. Form Requests: `GenerateModelo1Request`, etc.
3. Resource/Collection: `GeneratedDocumentResource`
4. Queue support para documentos grandes
5. Cache layer para PDFs regenerados
6. Observers para logging automático

---

## Arquivos de Implementação

### Arquivos a CRIAR:

1. `database/migrations/YYYY_MM_DD_HHMMSS_create_generated_documents_table.php`
2. `app/Models/GeneratedDocument.php`
3. `app/Policies/GeneratedDocumentPolicy.php`
4. `resources/views/documents/history.blade.php`

### Arquivos a MODIFICAR:

1. `app/Http/Controllers/DocumentsController.php`
   - Modificar 6 métodos: `modelo1()` até `modelo6()`
   - Adicionar 4 métodos novos: `history()`, `historyData()`, `download()`, `destroy()`

2. `routes/web.php`
   - Adicionar 4 rotas novas

3. `app/Models/Kid.php`
   - Adicionar `generatedDocuments()` relationship

4. `app/Models/User.php`
   - Adicionar `generatedDocuments()` relationship

5. `app/Providers/AuthServiceProvider.php`
   - Registrar `GeneratedDocumentPolicy`

6. `database/seeders/RoleAndPermissionSeeder.php`
   - Adicionar 7 permissões de documentos
   - Atribuir aos roles

7. `resources/views/documents/index.blade.php`
   - Adicionar botão "Ver Histórico"

8. Menu de navegação (localização a definir)
   - Converter "Documentos" em dropdown

---

## Resumo Executivo

**Complexidade:** Média
**Risco:** Baixo (não afeta funcionalidades existentes)
**Tempo estimado:** 4-6 horas
**Arquivos novos:** 4
**Arquivos modificados:** 8
**Linhas de código:** ~570

**Benefícios:**
- ✅ Histórico completo de documentos
- ✅ Auditoria e compliance
- ✅ Regeneração ilimitada de PDFs
- ✅ Zero armazenamento em disco
- ✅ Compatibilidade 100% com sistema atual

**Decisões Técnicas:**
- HTML em banco (LONGTEXT) vs. PDFs em disco → Escolhido HTML
- Relacionamento polimórfico vs. tabelas separadas → Escolhido polimórfico
- Cache de PDFs vs. regeneração on-demand → Escolhido regeneração (mais simples inicialmente)
- Permissions vs. Roles → Permissions (padrão do sistema)

---

**Documentação criada em:** 2025-12-06
**Última atualização:** 2025-12-06
**Versão:** 1.0
**Autor:** Claude Code (AI Assistant)
