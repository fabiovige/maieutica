# ChecklistLogger - Sistema de Registro de Log para Checklists

## Visão Geral

O `ChecklistLogger` é um serviço centralizado de logging para todas as operações relacionadas a **Checklists** no sistema Maiêutica. Ele segue exatamente o mesmo padrão do `KidLogger` e `UserLogger`, garantindo consistência e padronização em todo o sistema de auditoria.

## Arquitetura

### Padrão de Duas Camadas

O sistema de logging para Checklists utiliza uma arquitetura de **duas camadas**:

1. **Observer (Camada de Modelo)**
   - Localização: `app/Observers/ChecklistObserver.php`
   - Responsabilidade: Logs automáticos de eventos do modelo Eloquent
   - Registra: `created`, `updated`, `deleted`, `restored`, `forceDeleted`
   - Context: `'source' => 'observer'`

2. **Controller (Camada de Negócio)**
   - Localização: `app/Http/Controllers/ChecklistController.php`
   - Responsabilidade: Logs de operações de negócio e contexto adicional
   - Registra: Operações específicas como clonagem, acesso a interface de preenchimento, visualização de gráficos
   - Context: `'source' => 'controller'`

### Diagrama de Fluxo

```
Operação CRUD no Checklist
         │
         ├─→ Observer detecta evento do modelo
         │   └─→ ChecklistLogger::created/updated/deleted/restored()
         │       └─→ Log com 'source' => 'observer'
         │
         └─→ Controller executa lógica de negócio
             └─→ ChecklistLogger::cloned/fillInterfaceAccessed/chartViewed()
                 └─→ Log com 'source' => 'controller'
```

## Estrutura do Arquivo

### Localização
- **Service**: `app/Services/Logging/ChecklistLogger.php`
- **Observer**: `app/Observers/ChecklistObserver.php`
- **Controller**: `app/Http/Controllers/ChecklistController.php`

### Namespace
```php
namespace App\Services\Logging;
```

### Dependências
```php
use App\Models\Checklist;
use App\Models\Kid;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
```

## Métodos Disponíveis

### 1. Métodos do Ciclo de Vida

#### `created(Checklist $checklist, array $additionalContext = []): void`

Registra quando um checklist é criado.

**Nível de Log**: `NOTICE`

**Uso**:
```php
// No Controller
$this->checklistLogger->created($checklist, [
    'source' => 'controller',
    'retroactive' => false,
    'cloned_from_active' => false,
]);

// No Observer
$this->checklistLogger->created($checklist, [
    'source' => 'observer',
]);
```

**Exemplo de Log**:
```
[2025-01-06 14:30:00] local.NOTICE: Checklist criado {
    "checklist_id": 123,
    "kid_id": 45,
    "kid_initials": "JS",
    "status": "a",
    "retroactive": 0,
    "retroactive_date": null,
    "source": "controller",
    "cloned_from_active": false,
    "actor_user_id": 5,
    "actor_user_name": "Dr. Maria Silva",
    "actor_user_email": "maria@exemplo.com",
    "ip": "192.168.1.10"
}
```

---

#### `updated(Checklist $checklist, array $changes = [], array $additionalContext = []): void`

Registra quando um checklist é atualizado, incluindo quais campos foram alterados.

**Nível de Log**: `NOTICE`

**Uso**:
```php
// Capturar dados originais
$originalData = $checklist->only(['situation', 'level', 'kid_id']);

// ... realizar atualizações ...

// Rastrear mudanças
$changes = [];
$newData = $checklist->only(['situation', 'level', 'kid_id']);
foreach ($newData as $key => $value) {
    if ($originalData[$key] != $value) {
        $changes[$key] = ['old' => $originalData[$key], 'new' => $value];
    }
}

// Logar atualização
$this->checklistLogger->updated($checklist, $changes, [
    'source' => 'controller',
    'kid_data_updated' => false,
]);
```

**Exemplo de Log**:
```
[2025-01-06 15:45:12] local.NOTICE: Checklist atualizado {
    "checklist_id": 123,
    "kid_id": 45,
    "kid_initials": "JS",
    "changed_fields": ["situation"],
    "changes": {
        "situation": {
            "old": "a",
            "new": "f"
        }
    },
    "source": "controller",
    "kid_data_updated": false,
    "actor_user_id": 5,
    "actor_user_name": "Dr. Maria Silva",
    "actor_user_email": "maria@exemplo.com",
    "ip": "192.168.1.10"
}
```

---

#### `deleted(Checklist $checklist, array $additionalContext = []): void`

Registra quando um checklist é movido para a lixeira (soft delete).

**Nível de Log**: `NOTICE`

**Uso**:
```php
$this->checklistLogger->deleted($checklist, [
    'source' => 'controller',
    'planes_also_deleted' => 2,
]);
```

**Exemplo de Log**:
```
[2025-01-06 16:20:30] local.NOTICE: Checklist movido para lixeira {
    "checklist_id": 123,
    "kid_id": 45,
    "kid_initials": "JS",
    "source": "controller",
    "planes_also_deleted": 2,
    "actor_user_id": 5,
    "actor_user_name": "Dr. Maria Silva",
    "actor_user_email": "maria@exemplo.com",
    "ip": "192.168.1.10"
}
```

---

#### `restored(Checklist $checklist, array $additionalContext = []): void`

Registra quando um checklist é restaurado da lixeira.

**Nível de Log**: `NOTICE`

**Uso**:
```php
$this->checklistLogger->restored($checklist, [
    'source' => 'controller',
    'planes_also_restored' => 2,
]);
```

---

#### `forceDeleted(Checklist $checklist, array $additionalContext = []): void`

Registra quando um checklist é **permanentemente deletado** do banco de dados.

**Nível de Log**: `ALERT` (nível crítico)

**Uso**:
```php
// Geralmente chamado apenas pelo Observer
$this->checklistLogger->forceDeleted($checklist, [
    'source' => 'observer',
]);
```

**⚠️ IMPORTANTE**: Force delete é uma operação **irreversível** e deve ser evitada em produção.

---

### 2. Métodos Específicos de Checklist

#### `cloned(Checklist $originalChecklist, Checklist $newChecklist, array $additionalContext = []): void`

Registra quando um checklist é clonado de outro checklist existente.

**Nível de Log**: `NOTICE`

**Uso**:
```php
$this->checklistLogger->cloned($checklistAtual, $checklist, [
    'source' => 'controller',
    'plane_id' => $plane->id,
]);
```

**Exemplo de Log**:
```
[2025-01-06 10:15:00] local.NOTICE: Checklist clonado {
    "original_checklist_id": 120,
    "new_checklist_id": 123,
    "kid_id": 45,
    "kid_initials": "JS",
    "competences_cloned": 48,
    "source": "controller",
    "plane_id": 89,
    "actor_user_id": 5,
    "actor_user_name": "Dr. Maria Silva",
    "actor_user_email": "maria@exemplo.com",
    "ip": "192.168.1.10"
}
```

---

#### `fillInterfaceAccessed(Checklist $checklist, array $additionalContext = []): void`

Registra quando a interface de preenchimento do checklist é acessada.

**Nível de Log**: `INFO`

**Uso**:
```php
$this->checklistLogger->fillInterfaceAccessed($checklist);
```

**Exemplo de Log**:
```
[2025-01-06 11:30:45] local.INFO: Interface de preenchimento acessada {
    "checklist_id": 123,
    "kid_id": 45,
    "kid_initials": "JS",
    "status": "a",
    "actor_user_id": 5,
    "actor_user_name": "Dr. Maria Silva",
    "actor_user_email": "maria@exemplo.com",
    "ip": "192.168.1.10"
}
```

---

#### `competenceNoteUpdated(Checklist $checklist, int $competenceId, $oldNote, $newNote, array $additionalContext = []): void`

Registra quando a nota de uma competência individual é atualizada.

**Nível de Log**: `INFO`

**Uso**:
```php
$this->checklistLogger->competenceNoteUpdated($checklist, 15, 1, 2, [
    'updated_by' => 'fill_interface',
]);
```

**Exemplo de Log**:
```
[2025-01-06 11:32:10] local.INFO: Nota de competência atualizada {
    "checklist_id": 123,
    "kid_id": 45,
    "kid_initials": "JS",
    "competence_id": 15,
    "old_note": 1,
    "new_note": 2,
    "updated_by": "fill_interface",
    "actor_user_id": 5,
    "actor_user_name": "Dr. Maria Silva",
    "actor_user_email": "maria@exemplo.com",
    "ip": "192.168.1.10"
}
```

---

#### `competenceNotesBulkUpdated(Checklist $checklist, int $updatedCount, array $additionalContext = []): void`

Registra quando múltiplas notas de competências são atualizadas em massa.

**Nível de Log**: `NOTICE`

**Uso**:
```php
$this->checklistLogger->competenceNotesBulkUpdated($checklist, 15, [
    'method' => 'api_batch_update',
]);
```

---

#### `autoClosed(Checklist $checklist, array $additionalContext = []): void`

Registra quando um checklist é automaticamente fechado (situação alterada para 'f').

**Nível de Log**: `NOTICE`

**Uso**:
```php
$this->checklistLogger->autoClosed($previousChecklist, [
    'reason' => 'Novo checklist criado',
    'new_checklist_id' => $newChecklist->id,
]);
```

---

#### `chartViewed(Checklist $checklist, string $chartType = 'radar', array $additionalContext = []): void`

Registra quando o gráfico/radar do checklist é visualizado.

**Nível de Log**: `INFO`

**Uso**:
```php
$this->checklistLogger->chartViewed($checklist, 'radar');
```

**Exemplo de Log**:
```
[2025-01-06 12:00:00] local.INFO: Gráfico de checklist visualizado {
    "checklist_id": 123,
    "kid_id": 45,
    "kid_initials": "JS",
    "chart_type": "radar",
    "actor_user_id": 5,
    "actor_user_name": "Dr. Maria Silva",
    "actor_user_email": "maria@exemplo.com",
    "ip": "192.168.1.10"
}
```

---

#### `planeAutoGenerated(Checklist $checklist, int $planeId, array $additionalContext = []): void`

Registra quando um plano é automaticamente gerado a partir do checklist.

**Nível de Log**: `NOTICE`

**Uso**:
```php
$this->checklistLogger->planeAutoGenerated($checklist, $plane->id, [
    'source' => 'controller',
]);
```

---

#### `kidDataUpdatedViaChecklist(Checklist $checklist, array $kidChanges = [], array $additionalContext = []): void`

Registra quando dados da criança são atualizados através do formulário de checklist.

**Nível de Log**: `NOTICE`

**Uso**:
```php
$kidChanges = [
    'name' => ['old' => 'João Silva', 'new' => 'João da Silva'],
    'birth_date' => ['old' => '2018-05-10', 'new' => '2018-05-15']
];

$this->checklistLogger->kidDataUpdatedViaChecklist($checklist, $kidChanges, [
    'source' => 'controller',
]);
```

**Exemplo de Log**:
```
[2025-01-06 13:15:20] local.NOTICE: Dados da criança atualizados via checklist {
    "checklist_id": 123,
    "kid_id": 45,
    "kid_initials": "JS",
    "kid_changes": {
        "name": {
            "old": "João Silva",
            "new": "João da Silva"
        },
        "birth_date": {
            "old": "2018-05-10",
            "new": "2018-05-15"
        }
    },
    "source": "controller",
    "actor_user_id": 5,
    "actor_user_name": "Dr. Maria Silva",
    "actor_user_email": "maria@exemplo.com",
    "ip": "192.168.1.10"
}
```

---

#### `retroactiveCreated(Checklist $checklist, array $additionalContext = []): void`

Registra quando um checklist retroativo é criado (com data no passado).

**Nível de Log**: `NOTICE`

**Uso**:
```php
$this->checklistLogger->retroactiveCreated($checklist, [
    'source' => 'observer',
]);
```

---

### 3. Métodos de Visualização

#### `viewed(Checklist $checklist, string $viewType = 'details', array $additionalContext = []): void`

Registra quando um checklist é visualizado.

**Nível de Log**: `INFO`

**Tipos de visualização**:
- `'details'` - Página de detalhes
- `'edit'` - Formulário de edição
- `'fill'` - Interface de preenchimento
- `'chart'` - Visualização de gráfico
- `'pdf'` - Geração de PDF

**Uso**:
```php
$this->checklistLogger->viewed($checklist, 'details');
$this->checklistLogger->viewed($checklist, 'edit');
$this->checklistLogger->viewed($checklist, 'pdf');
```

---

#### `listed(array $filters = [], array $additionalContext = []): void`

Registra quando a lista de checklists é acessada.

**Nível de Log**: `DEBUG`

**Uso**:
```php
$this->checklistLogger->listed([
    'search' => 'João',
    'kid_id' => 45,
    'total_results' => 8,
]);
```

---

#### `trashViewed(array $additionalContext = []): void`

Registra quando a lixeira de checklists é visualizada.

**Nível de Log**: `INFO`

**Uso**:
```php
$this->checklistLogger->trashViewed([
    'total_trashed' => 5,
]);
```

---

### 4. Métodos de Erro e Acesso

#### `operationFailed(string $operation, \Exception $exception, array $additionalContext = []): void`

Registra quando uma operação falha.

**Nível de Log**: `ERROR`

**Operações comuns**:
- `'index'`, `'store'`, `'show'`, `'edit'`, `'update'`, `'destroy'`
- `'restore'`, `'clone'`, `'fill'`, `'chart'`

**Uso**:
```php
try {
    // ... operação ...
} catch (\Exception $e) {
    $this->checklistLogger->operationFailed('store', $e, [
        'kid_id' => $request->kid_id,
    ]);

    flash('Erro ao criar checklist')->error();
    return redirect()->back();
}
```

**Exemplo de Log**:
```
[2025-01-06 14:00:00] local.ERROR: Operação de checklist falhou: store {
    "operation": "store",
    "error": "SQLSTATE[23000]: Integrity constraint violation",
    "exception_class": "Illuminate\\Database\\QueryException",
    "file": "/var/www/app/Http/Controllers/ChecklistController.php",
    "line": 140,
    "kid_id": 45,
    "actor_user_id": 5,
    "actor_user_name": "Dr. Maria Silva",
    "actor_user_email": "maria@exemplo.com",
    "ip": "192.168.1.10"
}
```

---

#### `accessDenied(string $operation, ?Checklist $checklist = null, array $additionalContext = []): void`

Registra quando o acesso a uma operação é negado.

**Nível de Log**: `WARNING`

**Uso**:
```php
$this->checklistLogger->accessDenied('delete', $checklist, [
    'reason' => 'Usuário não é owner nem admin',
    'attempted_by_role' => 'profissional',
]);
```

---

### 5. Métodos Auxiliares Privados

#### `buildUserContext(): array`

Constrói o contexto do usuário autenticado para todos os logs.

**Retorno**:
```php
[
    'actor_user_id' => 5,
    'actor_user_name' => 'Dr. Maria Silva',
    'actor_user_email' => 'maria@exemplo.com',
    'ip' => '192.168.1.10'
]
```

Se não houver usuário autenticado:
```php
[
    'actor_user_id' => null,
    'actor_user_name' => 'Guest',
    'ip' => '192.168.1.10'
]
```

---

#### `sanitizeChanges(array $changes): array`

Sanitiza o array de mudanças para evitar logging de dados sensíveis.

**Campos sensíveis mascarados**: `password`, `api_token`

**Exemplo**:
```php
// Input
$changes = [
    'situation' => ['old' => 'a', 'new' => 'f'],
    'api_token' => ['old' => 'abc123...', 'new' => 'xyz789...']
];

// Output
[
    'situation' => ['old' => 'a', 'new' => 'f'],
    'api_token' => '[HIDDEN]'
]
```

---

#### `getKidInitials(?Kid $kid): string`

Obtém as iniciais da criança para conformidade com LGPD (Lei Geral de Proteção de Dados).

**Lógica**:
- Se nome tem 1 palavra: primeiras 2 letras (ex: "João" → "JO")
- Se nome tem 2+ palavras: primeira letra do primeiro nome + primeira letra do último nome (ex: "João Silva" → "JS")
- Se kid for null: retorna "N/A"

**Exemplo**:
```php
getKidInitials($kid); // "JS" (João Silva)
getKidInitials(null); // "N/A"
```

**⚠️ Privacidade**: Sempre usar iniciais ao invés do nome completo nos logs para proteger a privacidade das crianças.

---

## Níveis de Log

O ChecklistLogger utiliza os níveis padrão do Laravel/Monolog:

| Nível | Uso | Exemplos |
|-------|-----|----------|
| `DEBUG` | Informações de depuração | `listed()` |
| `INFO` | Informações gerais | `viewed()`, `fillInterfaceAccessed()`, `chartViewed()`, `competenceNoteUpdated()` |
| `NOTICE` | Eventos significativos normais | `created()`, `updated()`, `deleted()`, `restored()`, `cloned()`, `autoClosed()`, `planeAutoGenerated()`, `kidDataUpdatedViaChecklist()`, `competenceNotesBulkUpdated()`, `retroactiveCreated()` |
| `WARNING` | Avisos (situações anormais mas não erros) | `accessDenied()` |
| `ERROR` | Erros de runtime | `operationFailed()` |
| `ALERT` | Ação imediata requerida | `forceDeleted()` |

---

## Exemplos de Uso Completo

### Exemplo 1: Criar Checklist no Controller

```php
public function store(ChecklistRequest $request)
{
    $this->authorize('create', Checklist::class);

    try {
        $data = $request->all();
        $data['created_by'] = Auth::id();

        // Validação da data retroativa
        if (isset($data['created_at']) && $data['created_at']) {
            $createdAt = \Carbon\Carbon::parse($data['created_at']);
            if ($createdAt->isFuture()) {
                return response()->json(['error' => 'A data não pode ser futura.'], 422);
            }
            $data['created_at'] = $createdAt;
            $data['situation'] = !$createdAt->isToday() ? 'f' : 'a';
        } else {
            unset($data['created_at']);
            $data['situation'] = 'a';
        }

        // Checklist
        $checklist = Checklist::create($data);

        // Plane
        $plane = Plane::create([
            'kid_id' => $request->kid_id,
            'checklist_id' => $checklist->id,
            'created_by' => Auth::id(),
        ]);

        // Log plane auto-generation
        $this->checklistLogger->planeAutoGenerated($checklist, $plane->id, [
            'source' => 'controller',
        ]);

        // ... código de competências ...

        // Observer will log at model level
        $this->checklistLogger->created($checklist, [
            'source' => 'controller',
            'retroactive' => isset($data['created_at']) && !$data['created_at']->isToday(),
            'cloned_from_active' => false,
        ]);

        flash('Checklist criado com sucesso')->success();
        return redirect()->route('checklists.index');
    } catch (\Exception $e) {
        $this->checklistLogger->operationFailed('store', $e, [
            'kid_id' => $request->kid_id ?? null,
        ]);

        flash('Erro ao criar checklist')->error();
        return redirect()->back();
    }
}
```

---

### Exemplo 2: Atualizar Checklist com Rastreamento de Mudanças

```php
public function update(ChecklistRequest $request, $id)
{
    $checklist = Checklist::findOrFail($id);
    $this->authorize('update', $checklist);

    DB::beginTransaction();
    try {
        // Get original data for change tracking
        $originalData = $checklist->only(['situation', 'level', 'kid_id']);

        // Atualizar dados da criança se fornecidos
        $kidChanges = [];
        if ($request->has('kid_name') || $request->has('kid_birth_date')) {
            $kid = $checklist->kid;
            $kidOriginalData = $kid->only(['name', 'birth_date']);

            if ($request->filled('kid_name')) {
                $kid->name = $request->kid_name;
            }

            if ($request->filled('kid_birth_date')) {
                $kid->birth_date = $request->kid_birth_date;
            }

            $kid->updated_by = Auth::id();
            $kid->save();

            // Track kid changes
            $kidNewData = $kid->only(['name', 'birth_date']);
            foreach ($kidNewData as $key => $value) {
                if ($kidOriginalData[$key] != $value) {
                    $kidChanges[$key] = ['old' => $kidOriginalData[$key], 'new' => $value];
                }
            }

            if (!empty($kidChanges)) {
                $this->checklistLogger->kidDataUpdatedViaChecklist($checklist, $kidChanges, [
                    'source' => 'controller',
                ]);
            }
        }

        $data = $request->all();
        $data['updated_by'] = Auth::id();
        $checklist->update($data);

        // Track what changed in checklist
        $changes = [];
        $newData = $checklist->only(['situation', 'level', 'kid_id']);
        foreach ($newData as $key => $value) {
            if ($originalData[$key] != $value) {
                $changes[$key] = ['old' => $originalData[$key], 'new' => $value];
            }
        }

        // Observer will log at model level
        if (!empty($changes)) {
            $this->checklistLogger->updated($checklist, $changes, [
                'source' => 'controller',
                'kid_data_updated' => !empty($kidChanges),
            ]);
        }

        DB::commit();

        flash('Checklist atualizado com sucesso')->success();
        return redirect()->route('checklists.index');
    } catch (\Exception $e) {
        DB::rollBack();

        $this->checklistLogger->operationFailed('update', $e, [
            'checklist_id' => $id,
        ]);

        flash('Erro ao atualizar checklist')->error();
        return redirect()->back()->withInput();
    }
}
```

---

### Exemplo 3: Observer Automático

```php
// app/Observers/ChecklistObserver.php

public function created(Checklist $checklist)
{
    // Observer logs at model level - controller logs business operations
    $additionalContext = [
        'source' => 'observer',
    ];

    // Check if it's a retroactive checklist
    if ($checklist->retroactive || ($checklist->created_at && !$checklist->created_at->isToday())) {
        $this->checklistLogger->retroactiveCreated($checklist, $additionalContext);
    } else {
        $this->checklistLogger->created($checklist, $additionalContext);
    }
}

public function updated(Checklist $checklist)
{
    // Get the changed attributes
    $changes = [];
    foreach ($checklist->getDirty() as $field => $newValue) {
        $changes[$field] = [
            'old' => $checklist->getOriginal($field),
            'new' => $newValue,
        ];
    }

    // Only log if there are actual changes
    if (!empty($changes)) {
        $this->checklistLogger->updated($checklist, $changes, [
            'source' => 'observer',
        ]);
    }
}

public function deleted(Checklist $checklist)
{
    $this->checklistLogger->deleted($checklist, [
        'source' => 'observer',
    ]);
}
```

---

## Integração com ChecklistController

O `ChecklistLogger` é injetado no construtor do `ChecklistController`:

```php
protected $checklistService;
protected $checklistLogger;

public function __construct(ChecklistService $checklistService, ChecklistLogger $checklistLogger)
{
    $this->checklistService = $checklistService;
    $this->checklistLogger = $checklistLogger;
}
```

---

## Comparação com KidLogger e UserLogger

### Semelhanças

1. **Estrutura de Duas Camadas**: Observer + Controller
2. **Métodos do Ciclo de Vida**: `created()`, `updated()`, `deleted()`, `restored()`, `forceDeleted()`
3. **Métodos de Visualização**: `viewed()`, `listed()`, `trashViewed()`
4. **Métodos de Erro**: `operationFailed()`, `accessDenied()`
5. **Helpers Privados**: `buildUserContext()`, `sanitizeChanges()`
6. **Conformidade LGPD**: Uso de iniciais (`getKidInitials()`)
7. **Rastreamento de Mudanças**: Array `$changes` com valores `old` e `new`

### Diferenças Exclusivas do ChecklistLogger

| Método | Descrição | Por que é exclusivo? |
|--------|-----------|----------------------|
| `cloned()` | Registra clonagem de checklist | Checklists podem ser clonados para criar novo a partir de existente |
| `fillInterfaceAccessed()` | Registra acesso à interface de preenchimento | Interface específica para avaliação de competências |
| `competenceNoteUpdated()` | Registra atualização de nota individual | Checklists têm relação many-to-many com competências via pivot |
| `competenceNotesBulkUpdated()` | Registra atualização em massa | Suporte para atualização de múltiplas competências de uma vez |
| `autoClosed()` | Registra fechamento automático | Checklist anterior é fechado quando novo é criado |
| `chartViewed()` | Registra visualização de gráfico/radar | Checklists têm visualização de gráfico radar de competências |
| `planeAutoGenerated()` | Registra geração automática de plano | Plane é criado automaticamente junto com checklist |
| `kidDataUpdatedViaChecklist()` | Registra atualização de Kid via checklist | Dados da criança podem ser editados via formulário de checklist |
| `retroactiveCreated()` | Registra criação retroativa | Checklists podem ser criados com datas no passado |

---

## Visualizando Logs

### Através do Log Viewer (Web)

1. Acesse: `https://maieuticavalia.com.br/log-viewer`
2. Filtre por nível: `NOTICE`, `INFO`, `ERROR`, etc.
3. Busque por: `"Checklist"`, `"checklist_id"`, `"kid_initials"`

### Através do Terminal

```bash
# Ver logs em tempo real
tail -f storage/logs/laravel.log

# Filtrar apenas logs de Checklist
tail -f storage/logs/laravel.log | grep "Checklist"

# Ver últimas 50 linhas
tail -n 50 storage/logs/laravel.log

# Buscar por ID específico
grep "checklist_id.*123" storage/logs/laravel.log
```

### Através do Artisan Tinker

```bash
php artisan tinker

# Simular criação de log
$checklist = App\Models\Checklist::find(1);
$logger = app(App\Services\Logging\ChecklistLogger::class);
$logger->viewed($checklist, 'test');
```

---

## Boas Práticas

### ✅ DO (Faça)

1. **Sempre use ChecklistLogger** ao invés de `Log::` direto no ChecklistController
2. **Rastreie mudanças** nos métodos `update()` usando array `$changes`
3. **Use `'source'` context** para distinguir Observer vs Controller
4. **Log operações críticas** como clonagem, fechamento automático, geração de plano
5. **Use níveis apropriados**: INFO para visualizações, NOTICE para modificações, ERROR para falhas
6. **Inclua context adicional** quando disponível (ex: `kid_data_updated`, `planes_also_deleted`)
7. **Capture exceções** e use `operationFailed()` para registrar erros
8. **Use iniciais (LGPD)** ao invés de nomes completos da criança

### ❌ DON'T (Não Faça)

1. **Não use `Log::` diretamente** no ChecklistController
2. **Não logue dados sensíveis** sem sanitização
3. **Não crie logs redundantes** (Observer + Controller já logam `created`/`updated`)
4. **Não use nível errado** (ex: ERROR para informações normais)
5. **Não esqueça try-catch** em operações críticas
6. **Não logue nome completo** da criança (use iniciais)
7. **Não misture fontes** (sempre especifique `'source' => 'observer'` ou `'controller'`)

---

## Troubleshooting

### Problema 1: Logs não aparecem

**Sintomas**: Nenhum log é gerado ao executar operações no checklist

**Soluções**:
```bash
# 1. Verificar se Observer está registrado
# Arquivo: app/Providers/EventServiceProvider.php
# Deve conter: Checklist::observe(ChecklistObserver::class);

# 2. Verificar permissões do arquivo de log
ls -la storage/logs/laravel.log
chmod 664 storage/logs/laravel.log

# 3. Verificar configuração de logging
php artisan config:clear
php artisan cache:clear

# 4. Testar manualmente via Tinker
php artisan tinker
$checklist = App\Models\Checklist::first();
$logger = app(App\Services\Logging\ChecklistLogger::class);
$logger->viewed($checklist, 'test');
exit
tail storage/logs/laravel.log
```

---

### Problema 2: Duplicação de logs

**Sintomas**: Mesma ação gera múltiplos logs idênticos

**Causa**: Observer registrado múltiplas vezes

**Solução**:
```bash
# Verificar EventServiceProvider
# Deve ter apenas UMA chamada:
Checklist::observe(ChecklistObserver::class);

# Limpar cache
php artisan optimize:clear
```

---

### Problema 3: Context está vazio

**Sintomas**: Logs não contêm `actor_user_id`, `actor_user_name`

**Causa**: Usuário não autenticado ou sessão expirada

**Solução**:
```php
// Adicionar middleware 'auth' na rota
Route::middleware(['auth'])->group(function () {
    Route::resource('checklists', ChecklistController::class);
});

// Ou verificar autenticação no controller
if (!Auth::check()) {
    return redirect()->route('login');
}
```

---

### Problema 4: Iniciais não aparecem corretamente

**Sintomas**: `kid_initials` retorna "N/A" mesmo tendo criança

**Causa**: Relacionamento `kid` não carregado (N+1 query)

**Solução**:
```php
// Sempre carregar relacionamento
$checklist = Checklist::with('kid')->findOrFail($id);

// Ou eager loading na query
$checklists = Checklist::with('kid')->get();
```

---

## Estrutura JSON dos Logs

### Log de Criação de Checklist

```json
{
    "message": "Checklist criado",
    "context": {
        "checklist_id": 123,
        "kid_id": 45,
        "kid_initials": "JS",
        "status": "a",
        "retroactive": 0,
        "retroactive_date": null,
        "source": "controller",
        "retroactive": false,
        "cloned_from_active": false,
        "actor_user_id": 5,
        "actor_user_name": "Dr. Maria Silva",
        "actor_user_email": "maria@exemplo.com",
        "ip": "192.168.1.10"
    },
    "level": 250,
    "level_name": "NOTICE",
    "channel": "local",
    "datetime": "2025-01-06T14:30:00.000000-03:00",
    "extra": {}
}
```

---

### Log de Atualização com Mudanças

```json
{
    "message": "Checklist atualizado",
    "context": {
        "checklist_id": 123,
        "kid_id": 45,
        "kid_initials": "JS",
        "changed_fields": ["situation", "level"],
        "changes": {
            "situation": {
                "old": "a",
                "new": "f"
            },
            "level": {
                "old": 1,
                "new": 2
            }
        },
        "source": "controller",
        "kid_data_updated": false,
        "actor_user_id": 5,
        "actor_user_name": "Dr. Maria Silva",
        "actor_user_email": "maria@exemplo.com",
        "ip": "192.168.1.10"
    },
    "level": 250,
    "level_name": "NOTICE",
    "channel": "local",
    "datetime": "2025-01-06T15:45:12.000000-03:00",
    "extra": {}
}
```

---

### Log de Clonagem

```json
{
    "message": "Checklist clonado",
    "context": {
        "original_checklist_id": 120,
        "new_checklist_id": 123,
        "kid_id": 45,
        "kid_initials": "JS",
        "competences_cloned": 48,
        "source": "controller",
        "plane_id": 89,
        "actor_user_id": 5,
        "actor_user_name": "Dr. Maria Silva",
        "actor_user_email": "maria@exemplo.com",
        "ip": "192.168.1.10"
    },
    "level": 250,
    "level_name": "NOTICE",
    "channel": "local",
    "datetime": "2025-01-06T10:15:00.000000-03:00",
    "extra": {}
}
```

---

### Log de Erro

```json
{
    "message": "Operação de checklist falhou: store",
    "context": {
        "operation": "store",
        "error": "SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row",
        "exception_class": "Illuminate\\Database\\QueryException",
        "file": "/var/www/app/Http/Controllers/ChecklistController.php",
        "line": 140,
        "kid_id": 45,
        "actor_user_id": 5,
        "actor_user_name": "Dr. Maria Silva",
        "actor_user_email": "maria@exemplo.com",
        "ip": "192.168.1.10"
    },
    "level": 400,
    "level_name": "ERROR",
    "channel": "local",
    "datetime": "2025-01-06T14:00:00.000000-03:00",
    "extra": {}
}
```

---

## Referências

- [Laravel Logging Documentation](https://laravel.com/docs/9.x/logging)
- [PSR-3 Logger Interface](https://www.php-fig.org/psr/psr-3/)
- [Monolog Levels](https://github.com/Seldaek/monolog/blob/main/doc/01-usage.md#log-levels)
- `docs/kidLogger.md` - Documentação do KidLogger (padrão base)
- `docs/userLogger.md` - Documentação do UserLogger (padrão base)

---

## Conclusão

O **ChecklistLogger** é o serviço de logging mais completo do sistema Maiêutica, com **18 métodos especializados** que cobrem todas as operações relacionadas a checklists de avaliação cognitiva. Ele segue rigorosamente o padrão estabelecido por KidLogger e UserLogger, garantindo:

- ✅ **Auditoria completa** de todas as operações
- ✅ **Conformidade com LGPD** (uso de iniciais)
- ✅ **Rastreamento de mudanças** granular
- ✅ **Contexto rico** com usuário, IP e metadata
- ✅ **Separação clara** entre Observer e Controller logs
- ✅ **Facilidade de debugging** e monitoramento

**Última atualização**: 06 de Janeiro de 2025
**Versão**: 1.0.0
**Autor**: Sistema Maiêutica - Avaliação Cognitiva Infantil
