# UserLogger - Sistema de Logging para Usuários

## Visão Geral

O **UserLogger** é um serviço centralizado de logging para operações relacionadas a usuários (User). Segue o mesmo padrão implementado pelo **KidLogger**, fornecendo um sistema consistente de auditoria e monitoramento para todas as ações relacionadas a usuários no sistema.

## Arquitetura

### Padrão de Logging em Duas Camadas

O sistema utiliza duas camadas complementares de logging:

#### **Camada 1: Observer (Logging no Nível do Modelo)**
- **Arquivo:** `app/Observers/UserObserver.php`
- **Trigger:** Automático via eventos Eloquent ORM
- **Eventos:** `created`, `updated`, `deleted`, `restored`, `forceDeleted`
- **Contexto:** Minimal - captura mudanças no modelo
- **Source:** `'observer'`

#### **Camada 2: Controller (Logging no Nível de Negócio)**
- **Arquivo:** `app/Http/Controllers/UserController.php`
- **Trigger:** Manual através de chamadas explícitas
- **Eventos:** Operações de negócio (view, role changes, professional linking, etc.)
- **Contexto:** Rico - inclui metadados adicionais de negócio
- **Source:** `'controller'`

### Destino dos Logs

**File-Based Logging (Laravel Padrão):**
- ✅ Logs escritos em: `storage/logs/laravel.log`
- ✅ Logs rotativos por data: `storage/logs/laravel-YYYY-MM-DD.log`
- ✅ Formato: JSON estruturado no contexto
- ✅ Níveis: DEBUG, INFO, NOTICE, WARNING, ALERT, ERROR

**NÃO usa tabela dedicada no banco de dados** (diferente do sistema `logs` table que existe para outro propósito).

---

## Estrutura de Arquivos

### Core Files

```
app/Services/Logging/
└── UserLogger.php              # Serviço principal de logging

app/Observers/
└── UserObserver.php            # Observer com injeção de UserLogger

app/Http/Controllers/
└── UserController.php          # Controller com UserLogger injetado

docs/
└── userLogger.md              # Esta documentação
```

---

## Métodos Disponíveis

### 1. Lifecycle do Modelo

#### `created(User $user, array $additionalContext = [])`
- **Level:** NOTICE
- **Uso:** Log quando um usuário é criado
- **Contexto Automático:** user_id, user_email, user_name, actor_user_id, actor_user_email, ip

```php
$this->userLogger->created($user, [
    'source' => 'observer',
]);
```

#### `updated(User $user, array $changes = [], array $additionalContext = [])`
- **Level:** NOTICE
- **Uso:** Log quando um usuário é atualizado
- **Contexto Automático:** user_id, changed_fields, changes (sanitized)

```php
$this->userLogger->updated($user, $changes, [
    'source' => 'controller',
    'roles_changed' => true,
]);
```

#### `deleted(User $user, array $additionalContext = [])`
- **Level:** NOTICE
- **Uso:** Log quando um usuário é movido para lixeira (soft delete)

```php
$this->userLogger->deleted($user, [
    'source' => 'observer',
]);
```

#### `restored(User $user, array $additionalContext = [])`
- **Level:** NOTICE
- **Uso:** Log quando um usuário é restaurado da lixeira

```php
$this->userLogger->restored($user, [
    'source' => 'observer',
]);
```

---

### 2. Autenticação

#### `login(User $user, array $additionalContext = [])`
- **Level:** INFO
- **Uso:** Log quando um usuário faz login com sucesso

```php
$this->userLogger->login($user, [
    'remember_me' => true,
]);
```

#### `logout(User $user, array $additionalContext = [])`
- **Level:** INFO
- **Uso:** Log quando um usuário faz logout

```php
$this->userLogger->logout($user);
```

#### `loginFailed(string $email, array $additionalContext = [])`
- **Level:** WARNING
- **Uso:** Log quando uma tentativa de login falha

```php
$this->userLogger->loginFailed('user@example.com', [
    'reason' => 'invalid_password',
]);
```

#### `passwordChanged(User $user, array $additionalContext = [])`
- **Level:** NOTICE
- **Uso:** Log quando um usuário troca a senha

```php
$this->userLogger->passwordChanged($user, [
    'initiated_by' => 'user', // ou 'admin'
]);
```

#### `passwordResetRequested(User $user, array $additionalContext = [])`
- **Level:** INFO
- **Uso:** Log quando um usuário solicita reset de senha

```php
$this->userLogger->passwordResetRequested($user);
```

---

### 3. Visualização e Acesso

#### `viewed(User $user, string $viewType = 'details', array $additionalContext = [])`
- **Level:** INFO
- **Uso:** Log quando um usuário é visualizado
- **View Types:** `'details'`, `'edit'`, `'profile'`

```php
$this->userLogger->viewed($user, 'edit');
```

#### `listed(array $filters = [], array $additionalContext = [])`
- **Level:** DEBUG
- **Uso:** Log quando a lista de usuários é acessada

```php
$this->userLogger->listed([
    'search' => 'João',
    'total_results' => 15,
]);
```

#### `trashViewed(array $additionalContext = [])`
- **Level:** INFO
- **Uso:** Log quando a lixeira de usuários é acessada

```php
$this->userLogger->trashViewed();
```

---

### 4. Roles e Permissões

#### `roleAssigned(User $user, string $roleName, array $additionalContext = [])`
- **Level:** NOTICE
- **Uso:** Log quando uma role é atribuída a um usuário

```php
$this->userLogger->roleAssigned($user, 'admin', [
    'source' => 'controller',
]);
```

#### `roleRemoved(User $user, string $roleName, array $additionalContext = [])`
- **Level:** NOTICE
- **Uso:** Log quando uma role é removida de um usuário

```php
$this->userLogger->roleRemoved($user, 'profissional', [
    'source' => 'controller',
]);
```

---

### 5. Relacionamento com Professional

#### `professionalLinked(User $user, int $professionalId, array $additionalContext = [])`
- **Level:** NOTICE
- **Uso:** Log quando um professional é vinculado a um usuário

```php
$this->userLogger->professionalLinked($user, $professional->id);
```

#### `professionalUnlinked(User $user, int $professionalId, array $additionalContext = [])`
- **Level:** NOTICE
- **Uso:** Log quando um professional é desvinculado de um usuário

```php
$this->userLogger->professionalUnlinked($user, $professional->id);
```

---

### 6. Status da Conta

#### `activated(User $user, array $additionalContext = [])`
- **Level:** NOTICE
- **Uso:** Log quando uma conta de usuário é ativada

```php
$this->userLogger->activated($user);
```

#### `deactivated(User $user, array $additionalContext = [])`
- **Level:** ALERT
- **Uso:** Log quando uma conta de usuário é desativada

```php
$this->userLogger->deactivated($user, [
    'reason' => 'Solicitação do usuário',
]);
```

---

### 7. Tratamento de Erros

#### `operationFailed(string $operation, \Exception $exception, array $additionalContext = [])`
- **Level:** ERROR
- **Uso:** Log quando uma operação falha

```php
$this->userLogger->operationFailed('update', $e, [
    'user_id' => $user->id,
]);
```

#### `accessDenied(string $operation, ?User $user = null, array $additionalContext = [])`
- **Level:** WARNING
- **Uso:** Log quando acesso a uma operação é negado

```php
$this->userLogger->accessDenied('delete', $user);
```

---

## Estrutura dos Logs

### Contexto Automático (buildUserContext)

Todos os logs incluem automaticamente:

```json
{
  "actor_user_id": 1,
  "actor_user_name": "Fabio User 01",
  "actor_user_email": "user01@gmail.com",
  "ip": "127.0.0.1"
}
```

**Para usuários não autenticados:**
```json
{
  "actor_user_id": null,
  "actor_user_name": "Guest",
  "ip": "127.0.0.1"
}
```

### Exemplo de Log Completo

```json
{
  "message": "User updated",
  "context": {
    "user_id": 5,
    "user_email": "joao@example.com",
    "changed_fields": ["name", "email", "phone"],
    "changes": {
      "name": {"old": "João Silva", "new": "João S. Silva"},
      "email": {"old": "old@example.com", "new": "joao@example.com"},
      "password": "[HIDDEN]"
    },
    "actor_user_id": 1,
    "actor_user_name": "Admin User",
    "actor_user_email": "admin@example.com",
    "ip": "192.168.1.100",
    "source": "controller",
    "roles_changed": true
  },
  "level": "NOTICE",
  "level_name": "NOTICE",
  "channel": "stack",
  "datetime": "2025-02-06 14:30:45"
}
```

---

## Sanitização de Dados Sensíveis

### Campos Mascarados Automaticamente

O método `sanitizeChanges()` mascara os seguintes campos:

- **`password`** → `[HIDDEN]`
- **`remember_token`** → `[HIDDEN]`
- **`temporaryPassword`** → `[HIDDEN]`

### Exemplo de Sanitização

**Antes:**
```php
$changes = [
    'name' => ['old' => 'João', 'new' => 'João Silva'],
    'password' => ['old' => 'abc123', 'new' => 'xyz789'],
    'email' => ['old' => 'old@mail.com', 'new' => 'new@mail.com'],
];
```

**Depois (no log):**
```json
{
  "name": {"old": "João", "new": "João Silva"},
  "password": "[HIDDEN]",
  "email": {"old": "old@mail.com", "new": "new@mail.com"}
}
```

---

## Níveis de Log (Log Levels)

O UserLogger utiliza níveis apropriados para cada tipo de operação:

| Nível | Uso | Exemplos |
|-------|-----|----------|
| **DEBUG** | Eventos de baixa prioridade | `listed()` |
| **INFO** | Eventos informacionais | `viewed()`, `login()`, `logout()`, `passwordResetRequested()` |
| **NOTICE** | Eventos normais mas significativos | `created()`, `updated()`, `deleted()`, `restored()`, `roleAssigned()`, `passwordChanged()` |
| **WARNING** | Alertas, falhas de acesso | `loginFailed()`, `accessDenied()` |
| **ALERT** | Ações críticas | `deactivated()` |
| **ERROR** | Falhas em operações | `operationFailed()` |

---

## Integração nos Controllers

### Injeção de Dependência

```php
use App\Services\Logging\UserLogger;

class UserController extends Controller
{
    protected $userLogger;

    public function __construct(UserLogger $userLogger)
    {
        $this->userLogger = $userLogger;
    }
}
```

### Exemplo de Uso: Update com Tracking de Roles

```php
public function update(UserRequest $request, User $user)
{
    DB::beginTransaction();
    try {
        // Track old roles
        $oldRoles = $user->roles->pluck('name')->toArray();

        // Update user
        $user->fill($request->validated());
        $user->save();

        // Sync roles
        $user->syncRoles($request->roles);

        // Track new roles
        $newRoles = $user->roles->pluck('name')->toArray();

        // Log role changes
        if ($oldRoles != $newRoles) {
            $removedRoles = array_diff($oldRoles, $newRoles);
            $addedRoles = array_diff($newRoles, $oldRoles);

            foreach ($removedRoles as $roleName) {
                $this->userLogger->roleRemoved($user, $roleName, ['source' => 'controller']);
            }

            foreach ($addedRoles as $roleName) {
                $this->userLogger->roleAssigned($user, $roleName, ['source' => 'controller']);
            }
        }

        // Log user update
        $this->userLogger->updated($user, [], [
            'source' => 'controller',
            'roles_changed' => $oldRoles != $newRoles,
        ]);

        DB::commit();
        return redirect()->route('users.edit', $user->id);

    } catch (Exception $e) {
        DB::rollBack();
        $this->userLogger->operationFailed('update', $e, ['user_id' => $user->id]);
        return redirect()->back();
    }
}
```

---

## Integração no Observer

O **UserObserver** foi atualizado para injetar e usar o UserLogger:

```php
use App\Services\Logging\UserLogger;

class UserObserver
{
    protected $userLogger;

    public function __construct(UserLogger $userLogger)
    {
        $this->userLogger = $userLogger;
    }

    public function created(User $user)
    {
        $this->userLogger->created($user, ['source' => 'observer']);

        // ... código existente de envio de email ...
    }

    public function updated(User $user)
    {
        // Get changes
        $changes = [];
        foreach ($user->getDirty() as $field => $newValue) {
            $changes[$field] = [
                'old' => $user->getOriginal($field),
                'new' => $newValue,
            ];
        }

        if (!empty($changes)) {
            $this->userLogger->updated($user, $changes, ['source' => 'observer']);
        }

        // ... código existente de envio de email ...
    }
}
```

---

## Diferenças: UserLogger vs KidLogger

| Aspecto | KidLogger | UserLogger |
|---------|-----------|------------|
| **Identificação** | Iniciais (LGPD) | Email/ID |
| **Eventos Únicos** | Photo upload, PDF generation | Login, logout, password, roles |
| **Privacidade** | Alta (crianças) | Moderada (adultos) |
| **Campos Sensíveis** | `name`, `photo` | `password`, `remember_token`, `temporaryPassword` |
| **Eventos de Autenticação** | ❌ Não | ✅ Sim |
| **Tracking de Roles** | ❌ Não | ✅ Sim |
| **Tracking de Professional** | Kids vinculados a professionals | Users vinculados a professionals |

---

## Compliance e Segurança

### LGPD / Privacy

- ✅ **Não loga senhas** - Sempre mascaradas como `[HIDDEN]`
- ✅ **Não loga tokens** - remember_token e temporaryPassword mascarados
- ✅ **Rastreabilidade** - Todo log inclui quem (actor), quando, de onde (IP)
- ✅ **Auditoria** - Histórico completo de ações críticas (criação, edição, roles)

### Security Monitoring

O UserLogger é especialmente útil para:
- 🔍 **Detecção de tentativas de login falhas** (possível brute force)
- 🔍 **Monitoramento de mudanças de roles** (escalação de privilégios)
- 🔍 **Tracking de ações administrativas** (criação/exclusão de usuários)
- 🔍 **Auditoria de acessos** (quem visualizou quais usuários)

---

## Visualização dos Logs

### Via Laravel Log Viewer

O sistema possui o **arcanedev/log-viewer** instalado:

**Acesso:** `/log-viewer` (requer autenticação e permissões adequadas)

**Filtros disponíveis:**
- Por data
- Por nível (DEBUG, INFO, NOTICE, WARNING, ERROR)
- Por mensagem/contexto

### Via Comando Artisan

```bash
# Ver últimas 50 linhas do log
tail -n 50 storage/logs/laravel.log

# Monitorar log em tempo real
tail -f storage/logs/laravel.log

# Filtrar logs de usuários
grep "User" storage/logs/laravel.log
```

### Via Código (Programaticamente)

```php
use Illuminate\Support\Facades\File;

$logContent = File::get(storage_path('logs/laravel.log'));
$lines = explode("\n", $logContent);

// Filtrar linhas com 'User'
$userLogs = array_filter($lines, function($line) {
    return strpos($line, 'User') !== false;
});
```

---

## Boas Práticas

### ✅ DO:

- **Sempre use UserLogger** para operações de usuário (não use Log::info diretamente)
- **Adicione contexto adicional** relevante para debugging futuro
- **Log operações críticas** (mudanças de role, deactivation, delete)
- **Log falhas de autenticação** para monitoramento de segurança
- **Use try-catch** e log erros com `operationFailed()`

### ❌ DON'T:

- **Não logue senhas** ou tokens não mascarados
- **Não logue dados sensíveis** desnecessários (CPF, endereço completo)
- **Não use hasRole()** em verificações - use `can()` com permissions (padrão do sistema)
- **Não ignore exceções** - sempre log com `operationFailed()`

---

## Troubleshooting

### Problema: Logs não aparecem

**Possíveis causas:**
1. ✅ Verifique permissões do diretório `storage/logs`
2. ✅ Verifique configuração em `config/logging.php`
3. ✅ Verifique se UserLogger está injetado no controller/observer
4. ✅ Verifique se o Observer está registrado em `EventServiceProvider`

### Problema: Campos sensíveis aparecendo no log

**Solução:**
- Adicione o campo ao array `$sensitiveFields` em `UserLogger::sanitizeChanges()`

### Problema: Duplicação de logs

**Causa:**
- Observer e Controller logam o mesmo evento
- **Isso é esperado!** Observer log automático (source='observer') e Controller log manual (source='controller') são complementares

---

## Checklist de Implementação

- ✅ **UserLogger Service criado** (`app/Services/Logging/UserLogger.php`)
- ✅ **UserObserver atualizado** com injeção de UserLogger
- ✅ **UserController atualizado** com injeção de UserLogger
- ✅ **Logging adicionado** em todos os métodos relevantes
- ✅ **Tracking de roles** implementado
- ✅ **Sanitização** de campos sensíveis configurada
- ✅ **Documentação** completa (este arquivo)

---

## Referências

- **KidLogger:** `app/Services/Logging/KidLogger.php` (padrão de referência)
- **Laravel Logging:** https://laravel.com/docs/9.x/logging
- **Monolog Levels:** https://github.com/Seldaek/monolog/blob/main/doc/01-usage.md#log-levels

---

## Autores e Manutenção

**Implementado por:** Claude Code
**Data:** 2025-02-06
**Baseado em:** KidLogger pattern
**Versão Laravel:** 9.x
**Status:** ✅ Production Ready

---

## Changelog

### v1.0.0 (2025-02-06)
- ✅ Implementação inicial do UserLogger
- ✅ Integração com UserObserver
- ✅ Integração com UserController
- ✅ Documentação completa
- ✅ Sanitização de campos sensíveis
- ✅ Tracking de roles e professional linking
- ✅ Logging de autenticação (preparado para integração futura)
