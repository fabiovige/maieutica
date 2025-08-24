# Sistema de Roles Flexível - Maiêutica

## Problemas Resolvidos

### 1. **Autorização Incorreta para Profissionais**
**Problema**: O usuário `user006@maildrop.cc` (profissional) conseguia listar todas as crianças do sistema, mesmo não tendo crianças associadas a ele.

**Solução**: Implementada lógica de autorização baseada em relacionamentos reais entre profissionais e crianças.

### 2. **Sistema de Roles Hardcoded**
**Problema**: Nomes de roles estavam espalhados por todo o código como strings literais, dificultando manutenção e extensibilidade.

**Solução**: Criado sistema flexível com Enum, Traits e Services para centralizar a lógica de autorização.

## Componentes do Novo Sistema

### 1. **UserRole Enum** (`app/Enums/UserRole.php`)
Centraliza todos os tipos de usuários do sistema:
- `SUPERADMIN` - Super Administrador (acesso total)
- `ADMIN` - Administrador (acesso amplo)
- `PROFESSIONAL` - Profissional (acesso às crianças associadas)
- `PARENT` - Pais/Responsável (acesso às próprias crianças)

### 2. **HasRoleAuthorization Trait** (`app/Traits/HasRoleAuthorization.php`)
Fornece métodos para verificação de permissões:
```php
// Verificar tipo de usuário
$user->isSuperAdmin();
$user->isAdmin();
$user->isProfessional();
$user->isParent();

// Verificar capacidades
$user->canManageAllKids();
$user->canViewKid($kid);
$user->canEditKid($kid);

// Obter query com crianças acessíveis
$accessibleKids = $user->getAccessibleKidsQuery()->get();
```

### 3. **RoleService** (`app/Services/RoleService.php`)
Gerencia roles de forma programática:
```php
// Criar nova role
$roleService->createRole('supervisor', 'Supervisor', ['list kids', 'view kids']);

// Atualizar permissões
$roleService->updateRolePermissions('supervisor', ['list kids', 'view kids', 'create kids']);

// Verificar se é role do sistema
$roleService->isSystemRole('admin'); // true
```

## Como Criar uma Nova Role

### 1. **Via Command Line**
```bash
# Criar role interativamente
php artisan role:create supervisor

# Criar role com permissões específicas
php artisan role:create supervisor --permissions="list kids" --permissions="view kids"
```

### 2. **Via Código**
```php
use App\Services\RoleService;

$roleService = new RoleService();
$role = $roleService->createRole('supervisor', 'Supervisor', [
    'list kids',
    'view kids', 
    'create kids',
    'edit kids'
]);
```

### 3. **Atribuir Role a Usuário**
```php
$user = User::find(1);
$user->assignRole('supervisor');
```

## Lógica de Autorização por Role

### **SuperAdmin & Admin**
- Acesso completo a todas as crianças
- Podem gerenciar usuários, roles e permissões
- Podem criar, editar e excluir qualquer registro

### **Professional**
- Acesso apenas às crianças **associadas** a eles na tabela pivot `kid_professional`
- Podem criar novas crianças (que ficam automaticamente associadas)
- Podem editar crianças criadas por eles ou associadas a eles

### **Parent (Pais)**
- Acesso apenas às crianças onde `responsible_id = user.id`
- Podem visualizar e editar apenas suas próprias crianças
- Não podem criar novas crianças (apenas profissionais podem)

### **Roles Customizadas**
- Podem ser criadas com qualquer combinação de permissões existentes
- Seguem as mesmas regras de autorização baseadas em relacionamentos
- Exemplo: `supervisor` - pode supervisionar crianças de determinados profissionais

## Exemplo de Uso Prático

```php
// Verificar se usuário pode acessar uma criança
public function show(Kid $kid)
{
    $user = auth()->user();
    
    if (!$user->canViewKid($kid)) {
        abort(403, 'Não autorizado');
    }
    
    return view('kids.show', compact('kid'));
}

// Listar apenas crianças acessíveis ao usuário
public function index()
{
    $user = auth()->user();
    $kids = $user->getAccessibleKidsQuery()
        ->with(['responsible', 'professionals'])
        ->paginate(15);
        
    return view('kids.index', compact('kids'));
}

// Criar nova role personalizada
$roleService = app(RoleService::class);
$role = $roleService->createRole('coordenador', 'Coordenador', [
    'list kids',
    'view kids', 
    'list checklists',
    'view checklists',
    'list professionals',
    'view professionals'
]);
```

## Benefícios do Novo Sistema

1. **Segurança**: Profissionais só veem crianças realmente associadas a eles
2. **Flexibilidade**: Fácil criação de novas roles sem alterar código
3. **Manutenibilidade**: Lógica centralizada em poucos arquivos
4. **Extensibilidade**: Sistema preparado para futuras necessidades
5. **Compatibilidade**: Mantém compatibilidade com código existente

## Migração de Código Legado

Para migrar código que ainda usa verificações hardcoded:

```php
// ❌ Código antigo (hardcoded)
if ($user->hasRole('professional')) {
    // lógica
}

// ✅ Código novo (flexível)
if ($user->isProfessional()) {
    // lógica
}

// ❌ Query antiga (insegura)
$kids = Kid::all();

// ✅ Query nova (segura)
$kids = auth()->user()->getAccessibleKidsQuery()->get();
```

## Próximos Passos

1. **Migrar controllers restantes** para usar o novo sistema
2. **Criar interface administrativa** para gerenciar roles via web
3. **Implementar auditoria** de mudanças de permissões
4. **Adicionar cache** para consultas de autorização frequentes