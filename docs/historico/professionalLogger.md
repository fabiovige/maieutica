# ProfessionalLogger - Sistema de Registro de Log para Professionals

## Visão Geral

O `ProfessionalLogger` é um serviço centralizado de logging para todas as operações relacionadas a **Profissionais** no sistema Maiêutica. Ele segue exatamente o mesmo padrão do `KidLogger`, `UserLogger` e `ChecklistLogger`, garantindo consistência e padronização em todo o sistema de auditoria.

## Arquitetura

### Padrão de Duas Camadas

O sistema de logging para Professionals utiliza uma arquitetura de **duas camadas**:

1. **Observer (Camada de Modelo)**
   - Localização: `app/Observers/ProfessionalObserver.php`
   - Responsabilidade: Logs automáticos de eventos do modelo Eloquent
   - Registra: `created`, `updated`, `deleted`, `restored`, `forceDeleted`
   - Context: `'source' => 'observer'`

2. **Controller (Camada de Negócio)**
   - Localização: `app/Http/Controllers/ProfessionalController.php`
   - Responsabilidade: Logs de operações de negócio e contexto adicional
   - Registra: Operações específicas como vinculação de users/kids, ativação/desativação
   - Context: `'source' => 'controller'`

### Diagrama de Fluxo

```
Operação CRUD no Professional
         │
         ├─→ Observer detecta evento do modelo
         │   └─→ ProfessionalLogger::created/updated/deleted/restored()
         │       └─→ Log com 'source' => 'observer'
         │
         └─→ Controller executa lógica de negócio
             └─→ ProfessionalLogger::userLinked/activated/deactivated()
                 └─→ Log com 'source' => 'controller'
```

## Estrutura do Arquivo

### Localização
- **Service**: `app/Services/Logging/ProfessionalLogger.php`
- **Observer**: `app/Observers/ProfessionalObserver.php`
- **Controller**: `app/Http/Controllers/ProfessionalController.php`

### Namespace
```php
namespace App\Services\Logging;
```

### Dependências
```php
use App\Models\Professional;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
```

## Métodos Disponíveis

### 1. Métodos do Ciclo de Vida

#### `created(Professional $professional, array $additionalContext = []): void`

Registra quando um professional é criado.

**Nível de Log**: `NOTICE`

**Uso**:
```php
// No Controller
$this->professionalLogger->created($professional, [
    'source' => 'controller',
    'user_id' => $user->id,
    'user_email' => $user->email,
]);

// No Observer
$this->professionalLogger->created($professional, [
    'source' => 'observer',
]);
```

**Exemplo de Log**:
```
[2025-01-06 14:30:00] local.NOTICE: Professional criado {
    "professional_id": 45,
    "specialty_id": 3,
    "specialty_name": "Psicologia Clínica",
    "registration_number": "CRP 12345",
    "source": "controller",
    "user_id": 78,
    "user_email": "dr.maria@exemplo.com",
    "actor_user_id": 1,
    "actor_user_name": "Admin Sistema",
    "actor_user_email": "admin@sistema.com",
    "ip": "192.168.1.10"
}
```

---

#### `updated(Professional $professional, array $changes = [], array $additionalContext = []): void`

Registra quando um professional é atualizado, incluindo quais campos foram alterados.

**Nível de Log**: `NOTICE`

**Uso**:
```php
// Capturar dados originais
$originalData = $professional->only(['specialty_id', 'registration_number', 'bio']);

// ... realizar atualizações ...

// Rastrear mudanças
$changes = [];
$newData = $professional->only(['specialty_id', 'registration_number', 'bio']);
foreach ($newData as $key => $value) {
    if ($originalData[$key] != $value) {
        $changes[$key] = ['old' => $originalData[$key], 'new' => $value];
    }
}

// Logar atualização
$this->professionalLogger->updated($professional, $changes, [
    'source' => 'controller',
    'user_also_updated' => true,
]);
```

**Exemplo de Log**:
```
[2025-01-06 15:45:12] local.NOTICE: Professional atualizado {
    "professional_id": 45,
    "specialty_id": 3,
    "specialty_name": "Psicologia Clínica",
    "changed_fields": ["registration_number", "bio"],
    "changes": {
        "registration_number": {
            "old": "CRP 12345",
            "new": "CRP 12345/01"
        },
        "bio": {
            "old": "Psicólogo clínico...",
            "new": "Psicólogo clínico com especialização..."
        }
    },
    "source": "controller",
    "user_also_updated": true,
    "actor_user_id": 1,
    "actor_user_name": "Admin Sistema",
    "actor_user_email": "admin@sistema.com",
    "ip": "192.168.1.10"
}
```

---

#### `deleted(Professional $professional, array $additionalContext = []): void`

Registra quando um professional é movido para a lixeira (soft delete).

**Nível de Log**: `NOTICE`

**Uso**:
```php
$this->professionalLogger->deleted($professional, [
    'source' => 'controller',
    'user_id' => $user->id,
]);
```

**Exemplo de Log**:
```
[2025-01-06 16:20:30] local.NOTICE: Professional movido para lixeira {
    "professional_id": 45,
    "specialty_id": 3,
    "registration_number": "CRP 12345/01",
    "source": "controller",
    "user_id": 78,
    "actor_user_id": 1,
    "actor_user_name": "Admin Sistema",
    "actor_user_email": "admin@sistema.com",
    "ip": "192.168.1.10"
}
```

---

#### `restored(Professional $professional, array $additionalContext = []): void`

Registra quando um professional é restaurado da lixeira.

**Nível de Log**: `NOTICE`

**Uso**:
```php
$this->professionalLogger->restored($professional, [
    'source' => 'controller',
    'user_id' => $user->id,
]);
```

---

#### `forceDeleted(Professional $professional, array $additionalContext = []): void`

Registra quando um professional é **permanentemente deletado** do banco de dados.

**Nível de Log**: `ALERT` (nível crítico)

**Uso**:
```php
// Geralmente chamado apenas pelo Observer
$this->professionalLogger->forceDeleted($professional, [
    'source' => 'observer',
]);
```

**⚠️ IMPORTANTE**: Force delete é uma operação **irreversível** e deve ser evitada em produção.

---

### 2. Métodos Específicos de Professional

#### `userLinked(Professional $professional, int $userId, array $additionalContext = []): void`

Registra quando um usuário é vinculado ao professional.

**Nível de Log**: `NOTICE`

**Uso**:
```php
$this->professionalLogger->userLinked($professional, $user->id, [
    'source' => 'controller',
    'on_creation' => true,
]);
```

**Exemplo de Log**:
```
[2025-01-06 10:15:00] local.NOTICE: User vinculado ao professional {
    "professional_id": 45,
    "user_id": 78,
    "source": "controller",
    "on_creation": true,
    "actor_user_id": 1,
    "actor_user_name": "Admin Sistema",
    "actor_user_email": "admin@sistema.com",
    "ip": "192.168.1.10"
}
```

---

#### `userUnlinked(Professional $professional, int $userId, array $additionalContext = []): void`

Registra quando um usuário é desvinculado do professional.

**Nível de Log**: `NOTICE`

**Uso**:
```php
$this->professionalLogger->userUnlinked($professional, $user->id, [
    'reason' => 'Troca de vinculação',
]);
```

---

#### `kidLinked(Professional $professional, int $kidId, array $additionalContext = []): void`

Registra quando uma criança é vinculada ao professional.

**Nível de Log**: `NOTICE`

**Uso**:
```php
$this->professionalLogger->kidLinked($professional, $kid->id, [
    'source' => 'kid_controller',
]);
```

**Exemplo de Log**:
```
[2025-01-06 11:30:45] local.NOTICE: Criança vinculada ao professional {
    "professional_id": 45,
    "kid_id": 123,
    "source": "kid_controller",
    "actor_user_id": 5,
    "actor_user_name": "Dr. Maria Silva",
    "actor_user_email": "maria@exemplo.com",
    "ip": "192.168.1.10"
}
```

---

#### `kidUnlinked(Professional $professional, int $kidId, array $additionalContext = []): void`

Registra quando uma criança é desvinculada do professional.

**Nível de Log**: `NOTICE`

**Uso**:
```php
$this->professionalLogger->kidUnlinked($professional, $kid->id, [
    'reason' => 'Fim do acompanhamento',
]);
```

---

#### `activated(Professional $professional, array $additionalContext = []): void`

Registra quando um professional é ativado (usuário vinculado também é ativado).

**Nível de Log**: `NOTICE`

**Uso**:
```php
$this->professionalLogger->activated($professional, [
    'source' => 'controller',
    'user_id' => $user->id,
    'user_name' => $user->name,
]);
```

**Exemplo de Log**:
```
[2025-01-06 12:00:00] local.NOTICE: Professional ativado {
    "professional_id": 45,
    "specialty_id": 3,
    "source": "controller",
    "user_id": 78,
    "user_name": "Dr. Maria Silva",
    "actor_user_id": 1,
    "actor_user_name": "Admin Sistema",
    "actor_user_email": "admin@sistema.com",
    "ip": "192.168.1.10"
}
```

---

#### `deactivated(Professional $professional, array $additionalContext = []): void`

Registra quando um professional é desativado (usuário vinculado também é desativado).

**Nível de Log**: `ALERT` (nível crítico)

**Uso**:
```php
$this->professionalLogger->deactivated($professional, [
    'source' => 'controller',
    'user_id' => $user->id,
    'user_name' => $user->name,
]);
```

**⚠️ IMPORTANTE**: Desativação é um evento crítico pois impede o professional de acessar o sistema.

---

#### `specialtyChanged(Professional $professional, int $oldSpecialtyId, int $newSpecialtyId, array $additionalContext = []): void`

Registra quando a especialidade do professional é alterada.

**Nível de Log**: `NOTICE`

**Uso**:
```php
$this->professionalLogger->specialtyChanged($professional, 3, 5, [
    'source' => 'observer',
]);
```

**Exemplo de Log**:
```
[2025-01-06 13:15:20] local.NOTICE: Especialidade do professional alterada {
    "professional_id": 45,
    "old_specialty_id": 3,
    "new_specialty_id": 5,
    "source": "observer",
    "actor_user_id": 1,
    "actor_user_name": "Admin Sistema",
    "actor_user_email": "admin@sistema.com",
    "ip": "192.168.1.10"
}
```

---

### 3. Métodos de Visualização

#### `viewed(Professional $professional, string $viewType = 'details', array $additionalContext = []): void`

Registra quando um professional é visualizado.

**Nível de Log**: `INFO`

**Tipos de visualização**:
- `'details'` - Página de detalhes
- `'edit'` - Formulário de edição
- `'profile'` - Perfil do profissional

**Uso**:
```php
$this->professionalLogger->viewed($professional, 'details');
$this->professionalLogger->viewed($professional, 'edit');
```

---

#### `listed(array $filters = [], array $additionalContext = []): void`

Registra quando a lista de professionals é acessada.

**Nível de Log**: `DEBUG`

**Uso**:
```php
$this->professionalLogger->listed([
    'search' => 'Maria',
    'total_results' => 15,
]);
```

---

#### `trashViewed(array $additionalContext = []): void`

Registra quando a lixeira de professionals é visualizada.

**Nível de Log**: `INFO`

**Uso**:
```php
$this->professionalLogger->trashViewed([
    'total_trashed' => 3,
]);
```

---

### 4. Métodos de Erro e Acesso

#### `operationFailed(string $operation, \Exception $exception, array $additionalContext = []): void`

Registra quando uma operação falha.

**Nível de Log**: `ERROR`

**Operações comuns**:
- `'create'`, `'store'`, `'edit'`, `'update'`, `'destroy'`
- `'restore'`, `'activate'`, `'deactivate'`

**Uso**:
```php
try {
    // ... operação ...
} catch (\Exception $e) {
    $this->professionalLogger->operationFailed('store', $e, [
        'specialty_id' => $request->specialty_id,
    ]);

    flash('Erro ao criar profissional')->error();
    return redirect()->back();
}
```

**Exemplo de Log**:
```
[2025-01-06 14:00:00] local.ERROR: Operação de professional falhou: store {
    "operation": "store",
    "error": "SQLSTATE[23000]: Integrity constraint violation",
    "exception_class": "Illuminate\\Database\\QueryException",
    "file": "/var/www/app/Http/Controllers/ProfessionalController.php",
    "line": 135,
    "specialty_id": 3,
    "actor_user_id": 1,
    "actor_user_name": "Admin Sistema",
    "actor_user_email": "admin@sistema.com",
    "ip": "192.168.1.10"
}
```

---

#### `accessDenied(string $operation, ?Professional $professional = null, array $additionalContext = []): void`

Registra quando o acesso a uma operação é negado.

**Nível de Log**: `WARNING`

**Uso**:
```php
$this->professionalLogger->accessDenied('delete', $professional, [
    'reason' => 'Profissional tem crianças vinculadas',
    'kids_count' => 5,
]);
```

**Exemplo de Log**:
```
[2025-01-06 15:00:00] local.WARNING: Acesso negado à operação de professional {
    "operation": "delete",
    "target_professional_id": 45,
    "specialty_id": 3,
    "reason": "Profissional tem crianças vinculadas",
    "kids_count": 5,
    "actor_user_id": 1,
    "actor_user_name": "Admin Sistema",
    "actor_user_email": "admin@sistema.com",
    "ip": "192.168.1.10"
}
```

---

#### `roleMissing(int $userId, string $roleName, array $additionalContext = []): void`

Registra quando uma role não é encontrada durante a criação de professional.

**Nível de Log**: `WARNING`

**Uso**:
```php
if (\App\Models\Role::where('name', 'profissional')->exists()) {
    $user->assignRole('profissional');
} else {
    $this->professionalLogger->roleMissing($user->id, 'profissional', [
        'email' => $user->email,
    ]);
}
```

**Exemplo de Log**:
```
[2025-01-06 10:00:00] local.WARNING: Role não encontrada durante criação de professional {
    "user_id": 78,
    "role_name": "profissional",
    "email": "dr.maria@exemplo.com",
    "actor_user_id": 1,
    "actor_user_name": "Admin Sistema",
    "actor_user_email": "admin@sistema.com",
    "ip": "192.168.1.10"
}
```

---

### 5. Métodos Auxiliares Privados

#### `buildUserContext(): array`

Constrói o contexto do usuário autenticado para todos os logs.

**Retorno**:
```php
[
    'actor_user_id' => 1,
    'actor_user_name' => 'Admin Sistema',
    'actor_user_email' => 'admin@sistema.com',
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

**Campos sensíveis mascarados**: `password`, `remember_token`, `api_token`

**Exemplo**:
```php
// Input
$changes = [
    'bio' => ['old' => 'Psicólogo...', 'new' => 'Psicólogo clínico...'],
    'password' => ['old' => 'hash1', 'new' => 'hash2']
];

// Output
[
    'bio' => ['old' => 'Psicólogo...', 'new' => 'Psicólogo clínico...'],
    'password' => '[HIDDEN]'
]
```

---

## Níveis de Log

O ProfessionalLogger utiliza os níveis padrão do Laravel/Monolog:

| Nível | Uso | Exemplos |
|-------|-----|----------|
| `DEBUG` | Informações de depuração | `listed()` |
| `INFO` | Informações gerais | `viewed()`, `trashViewed()` |
| `NOTICE` | Eventos significativos normais | `created()`, `updated()`, `deleted()`, `restored()`, `userLinked()`, `kidLinked()`, `activated()`, `specialtyChanged()` |
| `WARNING` | Avisos (situações anormais mas não erros) | `accessDenied()`, `roleMissing()` |
| `ALERT` | Ação imediata requerida | `deactivated()`, `forceDeleted()` |
| `ERROR` | Erros de runtime | `operationFailed()` |

---

## Exemplos de Uso Completo

### Exemplo 1: Criar Professional no Controller

```php
public function store(ProfessionalRequest $request)
{
    $this->authorize('create', Professional::class);

    DB::beginTransaction();
    try {
        $validated = $request->validated();

        // Gerar senha temporária
        $temporaryPassword = Str::random(10);

        // Criar o usuário
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => bcrypt($temporaryPassword),
            'allow' => $request->has('allow'),
            'created_by' => auth()->id(),
        ]);

        // Atribuir role 'profissional'
        if (\App\Models\Role::where('name', 'profissional')->exists()) {
            $user->assignRole('profissional');
        } else {
            $this->professionalLogger->roleMissing($user->id, 'profissional', [
                'email' => $user->email,
            ]);
        }

        // Criar o profissional
        $professional = Professional::create([
            'specialty_id' => $validated['specialty_id'],
            'registration_number' => $validated['registration_number'],
            'bio' => $validated['bio'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Vincular usuário ao profissional
        $professional->user()->attach($user->id);

        // Log user linking
        $this->professionalLogger->userLinked($professional, $user->id, [
            'source' => 'controller',
            'on_creation' => true,
        ]);

        // Observer will log at model level
        $this->professionalLogger->created($professional, [
            'source' => 'controller',
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);

        DB::commit();

        flash('Profissional criado com sucesso.')->success();

        return redirect()->route('professionals.index');
    } catch (\Exception $e) {
        DB::rollBack();

        $this->professionalLogger->operationFailed('store', $e);

        flash('Erro ao criar profissional: ' . $e->getMessage())->error();

        return redirect()->back()->withInput();
    }
}
```

---

### Exemplo 2: Atualizar Professional com Rastreamento de Mudanças

```php
public function update(Request $request, $id)
{
    $professional = Professional::with('user')->findOrFail($id);
    $this->authorize('update', $professional);

    try {
        $user = $professional->user->first();

        if (!$user) {
            throw new \Exception('Usuário não encontrado');
        }

        DB::beginTransaction();

        // Get original data for change tracking
        $originalProfessionalData = $professional->only(['specialty_id', 'registration_number', 'bio']);
        $originalUserData = $user->only(['name', 'email', 'phone', 'allow']);

        // Atualizar dados do usuário
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'allow' => $request->has('allow'),
            'updated_by' => auth()->id()
        ]);

        // Atualizar dados do profissional
        $professional->update([
            'specialty_id' => $request->specialty_id,
            'registration_number' => $request->registration_number,
            'bio' => $request->bio,
            'updated_by' => auth()->id()
        ]);

        // Track what changed in professional
        $changes = [];
        $newProfessionalData = $professional->only(['specialty_id', 'registration_number', 'bio']);
        foreach ($newProfessionalData as $key => $value) {
            if ($originalProfessionalData[$key] != $value) {
                $changes[$key] = ['old' => $originalProfessionalData[$key], 'new' => $value];
            }
        }

        // Track what changed in user
        $userChanges = [];
        $newUserData = $user->only(['name', 'email', 'phone', 'allow']);
        foreach ($newUserData as $key => $value) {
            if ($originalUserData[$key] != $value) {
                $userChanges[$key] = ['old' => $originalUserData[$key], 'new' => $value];
            }
        }

        // Observer will log at model level
        if (!empty($changes)) {
            $this->professionalLogger->updated($professional, $changes, [
                'source' => 'controller',
                'user_also_updated' => !empty($userChanges),
                'user_changes' => $userChanges,
            ]);
        }

        DB::commit();

        flash('Profissional atualizado com sucesso!')->success();
        return redirect()->route('professionals.index');
    } catch (\Exception $e) {
        DB::rollBack();

        $this->professionalLogger->operationFailed('update', $e, [
            'professional_id' => $id,
        ]);

        flash('Erro ao atualizar o profissional')->warning();
        return redirect()->back()->withInput();
    }
}
```

---

### Exemplo 3: Observer Automático

```php
// app/Observers/ProfessionalObserver.php

public function created(Professional $professional)
{
    // Observer logs at model level - controller logs business operations
    $this->professionalLogger->created($professional, [
        'source' => 'observer',
    ]);
}

public function updated(Professional $professional)
{
    // Get the changed attributes
    $changes = [];
    foreach ($professional->getDirty() as $field => $newValue) {
        $changes[$field] = [
            'old' => $professional->getOriginal($field),
            'new' => $newValue,
        ];
    }

    // Only log if there are actual changes
    if (!empty($changes)) {
        // Check if specialty changed
        if (isset($changes['specialty_id'])) {
            $this->professionalLogger->specialtyChanged(
                $professional,
                $changes['specialty_id']['old'],
                $changes['specialty_id']['new'],
                ['source' => 'observer']
            );
        }

        $this->professionalLogger->updated($professional, $changes, [
            'source' => 'observer',
        ]);
    }
}

public function deleted(Professional $professional)
{
    $this->professionalLogger->deleted($professional, [
        'source' => 'observer',
    ]);
}
```

---

## Integração com ProfessionalController

O `ProfessionalLogger` é injetado no construtor do `ProfessionalController`:

```php
protected $professionalLogger;

public function __construct(ProfessionalLogger $professionalLogger)
{
    $this->professionalLogger = $professionalLogger;
}
```

---

## Comparação com Outros Loggers

### Semelhanças

1. **Estrutura de Duas Camadas**: Observer + Controller
2. **Métodos do Ciclo de Vida**: `created()`, `updated()`, `deleted()`, `restored()`, `forceDeleted()`
3. **Métodos de Visualização**: `viewed()`, `listed()`, `trashViewed()`
4. **Métodos de Erro**: `operationFailed()`, `accessDenied()`
5. **Helpers Privados**: `buildUserContext()`, `sanitizeChanges()`
6. **Rastreamento de Mudanças**: Array `$changes` com valores `old` e `new`

### Diferenças Exclusivas do ProfessionalLogger

| Método | Descrição | Por que é exclusivo? |
|--------|-----------|----------------------|
| `userLinked()` | Registra vinculação de user | Professional tem relação belongsToMany com User |
| `userUnlinked()` | Registra desvinculação de user | Professional pode ter user desvinculado |
| `kidLinked()` | Registra vinculação de criança | Professional atende múltiplas crianças |
| `kidUnlinked()` | Registra desvinculação de criança | Crianças podem mudar de profissional |
| `activated()` | Registra ativação | Professional e user vinculado são ativados juntos |
| `deactivated()` | Registra desativação | Professional e user vinculado são desativados juntos |
| `specialtyChanged()` | Registra mudança de especialidade | Specialty é um atributo chave do Professional |
| `roleMissing()` | Registra role faltante | Specific para criação de professional com role automática |

---

## Visualizando Logs

### Através do Log Viewer (Web)

1. Acesse: `https://maieuticavalia.com.br/log-viewer`
2. Filtre por nível: `NOTICE`, `INFO`, `ERROR`, etc.
3. Busque por: `"Professional"`, `"professional_id"`, `"specialty_id"`

### Através do Terminal

```bash
# Ver logs em tempo real
tail -f storage/logs/laravel.log

# Filtrar apenas logs de Professional
tail -f storage/logs/laravel.log | grep "Professional"

# Ver últimas 50 linhas
tail -n 50 storage/logs/laravel.log

# Buscar por ID específico
grep "professional_id.*45" storage/logs/laravel.log
```

### Através do Artisan Tinker

```bash
php artisan tinker

# Simular criação de log
$professional = App\Models\Professional::find(1);
$logger = app(App\Services\Logging\ProfessionalLogger::class);
$logger->viewed($professional, 'test');
```

---

## Boas Práticas

### ✅ DO (Faça)

1. **Sempre use ProfessionalLogger** ao invés de `Log::` direto no ProfessionalController
2. **Rastreie mudanças** nos métodos `update()` usando array `$changes`
3. **Use `'source'` context** para distinguir Observer vs Controller
4. **Log vinculações** quando user ou kid é vinculado/desvinculado
5. **Use níveis apropriados**: INFO para visualizações, NOTICE para modificações, ALERT para desativação
6. **Inclua context adicional** quando disponível (ex: `user_also_updated`, `kids_count`)
7. **Capture exceções** e use `operationFailed()` para registrar erros
8. **Log role missing** quando role 'profissional' não existe

### ❌ DON'T (Não Faça)

1. **Não use `Log::` diretamente** no ProfessionalController
2. **Não logue dados sensíveis** sem sanitização
3. **Não crie logs redundantes** (Observer + Controller já logam `created`/`updated`)
4. **Não use nível errado** (ex: ERROR para informações normais)
5. **Não esqueça try-catch** em operações críticas
6. **Não delete professional** com kids vinculados sem logar accessDenied
7. **Não misture fontes** (sempre especifique `'source' => 'observer'` ou `'controller'`)

---

## Troubleshooting

### Problema 1: Logs não aparecem

**Sintomas**: Nenhum log é gerado ao executar operações no professional

**Soluções**:
```bash
# 1. Verificar se Observer está registrado
# Arquivo: app/Providers/EventServiceProvider.php
# Deve conter: Professional::observe(ProfessionalObserver::class);

# 2. Verificar permissões do arquivo de log
ls -la storage/logs/laravel.log
chmod 664 storage/logs/laravel.log

# 3. Verificar configuração de logging
php artisan config:clear
php artisan cache:clear

# 4. Testar manualmente via Tinker
php artisan tinker
$professional = App\Models\Professional::first();
$logger = app(App\Services\Logging\ProfessionalLogger::class);
$logger->viewed($professional, 'test');
exit
tail storage/logs/laravel.log
```

---

### Problema 2: Specialty name não aparece

**Sintomas**: `specialty_name` retorna "N/A" nos logs

**Causa**: Relacionamento `specialty` não carregado (N+1 query)

**Solução**:
```php
// Sempre carregar relacionamento
$professional = Professional::with('specialty')->findOrFail($id);

// Ou eager loading na query
$professionals = Professional::with('specialty')->get();
```

---

### Problema 3: User vinculado não encontrado

**Sintomas**: Erro "Usuário não encontrado" em ativação/desativação

**Causa**: Professional sem user vinculado ou relacionamento não carregado

**Solução**:
```php
// Carregar relacionamento antes de acessar
$professional = Professional::with('user')->findOrFail($id);

$user = $professional->user->first();
if (!$user) {
    $this->professionalLogger->operationFailed('activate', new \Exception('User não encontrado'), [
        'professional_id' => $professional->id,
    ]);
    throw new \Exception('Usuário não encontrado');
}
```

---

## Estrutura JSON dos Logs

### Log de Criação de Professional

```json
{
    "message": "Professional criado",
    "context": {
        "professional_id": 45,
        "specialty_id": 3,
        "specialty_name": "Psicologia Clínica",
        "registration_number": "CRP 12345",
        "source": "controller",
        "user_id": 78,
        "user_email": "dr.maria@exemplo.com",
        "actor_user_id": 1,
        "actor_user_name": "Admin Sistema",
        "actor_user_email": "admin@sistema.com",
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

### Log de Ativação

```json
{
    "message": "Professional ativado",
    "context": {
        "professional_id": 45,
        "specialty_id": 3,
        "source": "controller",
        "user_id": 78,
        "user_name": "Dr. Maria Silva",
        "actor_user_id": 1,
        "actor_user_name": "Admin Sistema",
        "actor_user_email": "admin@sistema.com",
        "ip": "192.168.1.10"
    },
    "level": 250,
    "level_name": "NOTICE",
    "channel": "local",
    "datetime": "2025-01-06T12:00:00.000000-03:00",
    "extra": {}
}
```

---

### Log de Acesso Negado

```json
{
    "message": "Acesso negado à operação de professional",
    "context": {
        "operation": "delete",
        "target_professional_id": 45,
        "specialty_id": 3,
        "reason": "Profissional tem crianças vinculadas",
        "kids_count": 5,
        "actor_user_id": 1,
        "actor_user_name": "Admin Sistema",
        "actor_user_email": "admin@sistema.com",
        "ip": "192.168.1.10"
    },
    "level": 300,
    "level_name": "WARNING",
    "channel": "local",
    "datetime": "2025-01-06T15:00:00.000000-03:00",
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
- `docs/checklistLogger.md` - Documentação do ChecklistLogger (padrão base)

---

## Conclusão

O **ProfessionalLogger** completa o sistema de logging padronizado do Maiêutica com **16 métodos especializados** que cobrem todas as operações relacionadas a profissionais. Ele segue rigorosamente o padrão estabelecido pelos outros loggers, garantindo:

- ✅ **Auditoria completa** de todas as operações
- ✅ **Rastreamento de mudanças** granular
- ✅ **Contexto rico** com usuário, IP e metadata
- ✅ **Separação clara** entre Observer e Controller logs
- ✅ **Facilidade de debugging** e monitoramento
- ✅ **Registro de vinculações** User/Kid ao Professional
- ✅ **Logs de ativação/desativação** críticos

**Última atualização**: 06 de Janeiro de 2025
**Versão**: 1.0.0
**Autor**: Sistema Maiêutica - Avaliação Cognitiva Infantil
