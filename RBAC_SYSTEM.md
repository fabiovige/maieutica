# Sistema RBAC Agnóstico - Maiêutica

## Filosofia do Sistema

O novo sistema é **totalmente agnóstico** aos tipos específicos de roles. Em vez de verificar "se é admin", "se é profissional", etc., o sistema funciona assim:

1. **Qual a permissão necessária?** (ex: `view kids`)
2. **O usuário tem essa permissão?** 
3. **Se há um recurso específico, o usuário tem contexto para acessá-lo?**

## Como Funciona

### ✅ **Sistema Atual (RBAC Agnóstico)**
```php
// ✅ Verificação por permissão (agnóstico)
if ($user->can('view kids') && $user->canViewKid($kid)) {
    // permitir acesso
}

// ✅ Criar nova role facilmente
$role = Role::create(['name' => 'supervisor']);
$role->givePermissionTo(['list kids', 'view kids', 'create kids']);
$user->assignRole('supervisor');
// Pronto! Funciona automaticamente
```

### ❌ **Sistema Anterior (Hardcoded)**
```php
// ❌ Verificação hardcoded
if ($user->hasRole('admin') || $user->hasRole('professional')) {
    // permitir acesso
}

// ❌ Para criar nova role, precisava alterar código em vários lugares
// e adicionar verificações específicas
```

## Componentes do Sistema

### 1. **AuthorizationService** 
**Responsabilidade**: Lógica central de autorização
```php
// Verificar se pode acessar um recurso específico
$authService->canAccessResource('view kids', $kid);

// Verificar permissão geral
$authService->canList('kids');
$authService->canCreate('users');
```

### 2. **HasResourceAuthorization Trait**
**Responsabilidade**: Interface simples para o User model
```php
$user->canViewKid($kid);          // kids específico
$user->canListKids();             // permissão geral
$user->canCreateResource('users'); // qualquer recurso
```

### 3. **Policies Agnósticas**
**Responsabilidade**: Delegar para o AuthorizationService
```php
public function view(User $user, Kid $kid): bool
{
    return $this->authService->canView('kids', $kid);
}
```

## Contexto por Tipo de Recurso

### **Kids (Crianças)**
Usuário pode acessar uma criança se:
1. É o responsável (`responsible_id`)
2. Criou a criança (`created_by`) 
3. É profissional associado (tabela pivot `kid_professional`)
4. Tem permissão `manage all resources` (admins)

### **Outros Recursos**
Para recursos como `users`, `roles`, `checklists`, etc., se o usuário tem a permissão, pode acessar (sem contexto adicional por enquanto).

## Como Criar uma Nova Role

### **Método 1: Programaticamente**
```php
use Spatie\Permission\Models\Role;

// Criar role
$role = Role::create(['name' => 'coordenador']);

// Atribuir permissões
$role->givePermissionTo([
    'list kids',
    'view kids', 
    'create kids',
    'list checklists',
    'view checklists'
]);

// Atribuir ao usuário
$user->assignRole('coordenador');
```

### **Método 2: Via Seeder**
```php
class RoleSeeder extends Seeder
{
    public function run()
    {
        $coordinator = Role::create(['name' => 'coordenador']);
        
        $coordinator->givePermissionTo([
            'list kids', 'view kids', 'create kids',
            'list users', 'view users',
            'list checklists', 'view checklists'
        ]);
    }
}
```

### **Método 3: Via Interface (futuro)**
```php
// Interface administrativa para criar roles via web
// Selecionar permissões em checkboxes
// Atribuir a usuários dinamicamente
```

## Exemplo Prático: Role "Supervisor"

```php
// 1. Criar a role
$supervisor = Role::create(['name' => 'supervisor']);

// 2. Definir o que um supervisor pode fazer
$supervisor->givePermissionTo([
    'list kids',           // Pode listar crianças
    'view kids',           // Pode ver detalhes das crianças (com contexto)
    'create kids',         // Pode criar novas crianças
    'list checklists',     // Pode listar checklists
    'view checklists',     // Pode ver checklists específicos
    'list professionals',  // Pode listar profissionais
    'view professionals'   // Pode ver detalhes dos profissionais
]);

// 3. Atribuir a um usuário
$user->assignRole('supervisor');

// 4. PRONTO! O sistema funciona automaticamente:
$user->canListKids();              // ✅ SIM (tem a permissão)
$user->canViewKid($specificKid);   // ✅ Depende do contexto
$user->canCreateUsers();           // ❌ NÃO (não tem a permissão)
```

## Vantagens do Sistema

### 🔒 **Segurança por Contexto**
```php
// Um supervisor pode listar crianças, mas só pode ver/editar
// aquelas que tem contexto (criadas por ele, ou que é responsável, etc.)
$supervisor->canListKids();        // ✅ SIM  
$supervisor->canViewKid($kidA);    // ✅ SIM (tem contexto)
$supervisor->canViewKid($kidB);    // ❌ NÃO (sem contexto)
```

### 🚀 **Extensibilidade Total**
```php
// Criar qualquer role nova sem mexer no código
$customRole = Role::create(['name' => 'analista']);
$customRole->givePermissionTo(['list kids', 'view checklists']);
// Funciona imediatamente!
```

### 🧹 **Código Limpo**
```php
// Antes: verificações espalhadas pelo código
if ($user->hasRole('admin') || $user->hasRole('professional')) { ... }

// Agora: verificação semântica
if ($user->canEditKid($kid)) { ... }
```

### 🎯 **Foco nas Permissões**
- **Não importa** se é "admin", "professional", "supervisor"
- **Importa** se tem a permissão necessária
- **Importa** se tem contexto para o recurso específico

## Permissões Disponíveis

### **Users**
- `list users`, `view users`, `create users`, `edit users`, `remove users`

### **Kids** 
- `list kids`, `view kids`, `create kids`, `edit kids`, `remove kids`

### **Checklists**
- `list checklists`, `view checklists`, `create checklists`, `edit checklists`, `remove checklists`
- `fill checklists`, `clone checklists`, `avaliation checklist`

### **Especiais**
- `manage all resources` - Acesso total (admins)
- `plane automatic checklist`, `plane manual checklist`

## Migração do Código Legado

```php
// ❌ Código antigo (hardcoded)
if ($user->hasRole('professional')) {
    $kids = Kid::whereHas('professionals', function($q) use ($user) {
        $q->where('user_id', $user->id);
    })->get();
}

// ✅ Código novo (agnóstico)
if ($user->canListKids()) {
    $kids = $user->getAccessibleKidsQuery()->get();
}
```

## Conclusão

Este sistema resolve completamente os problemas:

1. ✅ **Fim dos hardcodes**: Não há mais verificações de roles específicas
2. ✅ **Segurança real**: Contexto baseado em relacionamentos reais
3. ✅ **Extensibilidade total**: Novas roles criadas sem alterar código
4. ✅ **Manutenção simples**: Lógica centralizada em poucos arquivos
5. ✅ **Semântica clara**: `canEditKid()` é mais claro que `hasRole('admin')`

**Agora você pode criar qualquer role nova e ela funciona automaticamente!** 🎉