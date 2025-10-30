# Relação Professional ↔ User

Este documento explica como funciona a relação entre `Professional` (Profissional) e `User` (Usuário) no sistema Maiêutica.

## 📋 Índice

- [Visão Geral](#visão-geral)
- [Estrutura do Banco de Dados](#estrutura-do-banco-de-dados)
- [Modelos Eloquent](#modelos-eloquent)
- [Regras de Negócio](#regras-de-negócio)
- [Como Funciona na Prática](#como-funciona-na-prática)
- [Autorização e Permissions](#autorização-e-permissions)
- [Exemplos de Código](#exemplos-de-código)
- [Migrations](#migrations)

---

## Visão Geral

### Conceitos Importantes

**User (Usuário):**
- Representa uma conta de acesso ao sistema
- Possui credenciais de login (email/senha)
- Tem permissions atribuídas (através de roles do Spatie)
- Pode ou não estar vinculado a um Professional

**Professional (Profissional):**
- Representa as credenciais profissionais (CRM, CRP, etc)
- Armazena dados da especialidade (Psicologia, Neurologia, etc)
- Possui biografia e informações profissionais
- SEMPRE está vinculado a um User

### Relação de Cardinalidade

```
User (1) ←→ (1) Professional
```

**IMPORTANTE:** Apesar de usar uma tabela pivot (many-to-many), a regra de negócio é **1:1**:
- Um User pode ter **no máximo 1** Professional vinculado
- Um Professional está vinculado a **exatamente 1** User

A estrutura many-to-many foi mantida por razões arquiteturais, mas com constraint UNIQUE que garante o 1:1.

---

## Estrutura do Banco de Dados

### Tabela `users`

```sql
users
├── id (PK)
├── name
├── email (unique)
├── password
├── phone
├── avatar
├── allow (boolean - ativo/inativo)
├── postal_code, street, number, complement, neighborhood, city, state
├── created_by, updated_by, deleted_by
├── timestamps
└── soft_deletes (deleted_at)
```

### Tabela `professionals`

```sql
professionals
├── id (PK)
├── specialty_id (FK → specialties.id) RESTRICT
├── registration_number (nullable - CRM, CRP, etc)
├── bio (nullable - texto)
├── created_by, updated_by, deleted_by (FK → users.id)
├── timestamps
└── soft_deletes (deleted_at)
```

### Tabela Pivot `user_professional`

```sql
user_professional
├── id (PK)
├── user_id (FK → users.id) CASCADE
├── professional_id (FK → professionals.id) CASCADE
├── timestamps
└── UNIQUE(user_id) -- ⚠️ GARANTE RELAÇÃO 1:1
```

**⚠️ CONSTRAINT ÚNICO:** A constraint `UNIQUE(user_id)` garante que um User só pode ter um Professional.

### Tabela `specialties`

```sql
specialties
├── id (PK)
├── name (e.g., "Psicologia Clínica", "Neurologia Pediátrica")
├── description (nullable)
├── created_by, updated_by, deleted_by
├── timestamps
└── soft_deletes (deleted_at)
```

---

## Modelos Eloquent

### App\Models\User

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles, SoftDeletes;

    /**
     * Relação many-to-many com Professional.
     * Regra de negócio: 1 User = 1 Professional (relação 1:1 apesar do pivot).
     */
    public function professional()
    {
        return $this->belongsToMany(Professional::class, 'user_professional');
    }

    /**
     * Accessor para obter a especialidade do profissional vinculado.
     */
    public function getSpecialtyAttribute()
    {
        return $this->professional->first()?->specialty;
    }
}
```

**Uso:**
```php
// Obter o professional vinculado ao user
$professional = $user->professional->first();

// Obter a especialidade
$specialty = $user->specialty; // Accessor
```

### App\Models\Professional

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Professional extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'registration_number',
        'bio',
        'specialty_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Relação many-to-many com User.
     * Regra de negócio: 1 Professional = 1 User.
     */
    public function user()
    {
        return $this->belongsToMany(User::class, 'user_professional');
    }

    /**
     * Relação com Specialty.
     */
    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    /**
     * Relação many-to-many com Kids.
     */
    public function kids()
    {
        return $this->belongsToMany(Kid::class, 'kid_professional');
    }
}
```

**Uso:**
```php
// Obter o user vinculado ao professional
$user = $professional->user->first();

// Obter a especialidade
$specialty = $professional->specialty;

// Obter kids atendidos por este professional
$kids = $professional->kids;
```

---

## Regras de Negócio

### 1. Criação de Professional

**Fluxo no ProfessionalController:**

```php
public function store(ProfessionalRequest $request)
{
    DB::beginTransaction();

    // 1. Criar User
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'password' => bcrypt($temporaryPassword),
        'allow' => $request->has('allow'),
        'created_by' => auth()->id(),
    ]);

    // 2. Atribuir role 'profissional' (agrupa permissions automaticamente)
    // IMPORTANTE: O código usa APENAS can() para verificações, nunca hasRole()
    if (\App\Models\Role::where('name', 'profissional')->exists()) {
        $user->assignRole('profissional');
    }

    // 3. Criar Professional
    $professional = Professional::create([
        'specialty_id' => $request->specialty_id,
        'registration_number' => $request->registration_number,
        'bio' => $request->bio,
        'created_by' => auth()->id(),
    ]);

    // 4. Vincular User ao Professional
    $professional->user()->attach($user->id);

    DB::commit();
}
```

**Resultado:**
- ✅ 1 registro em `users`
- ✅ 1 registro em `professionals`
- ✅ 1 registro em `user_professional` (pivot)
- ✅ Role 'profissional' atribuído ao User (com todas as permissions do role)

### 2. Exclusão de Professional e User

**Quando um Professional é movido para lixeira:**

```php
public function destroy(Professional $professional)
{
    // Verifica se tem kids vinculados
    if ($professional->kids()->count() > 0) {
        throw new \Exception('Não é possível mover para lixeira, pois existem crianças vinculadas.');
    }

    // Soft delete apenas do Professional
    $professional->delete();
}
```

**O que acontece:**
- ✅ Professional vai para lixeira (`deleted_at` preenchido)
- ✅ User permanece ativo (não é deletado)
- ✅ Vínculo na pivot permanece
- ✅ Professional pode ser restaurado

**⚠️ IMPORTANTE:** O User NÃO é deletado quando o Professional é movido para lixeira.

---

**Quando um User vinculado a Professional é movido para lixeira:**

```php
public function destroy(User $user)
{
    // Verifica se o user está vinculado a um professional
    $professional = $user->professional->first();
    if ($professional) {
        // Verifica se o professional tem kids vinculados
        if ($professional->kids()->count() > 0) {
            throw new \Exception('Não é possível mover para lixeira. Este usuário está vinculado a um profissional que possui crianças atendidas.');
        }

        // Move o professional para lixeira primeiro
        $professional->delete();
    }

    // Move o user para lixeira
    $user->delete();
}
```

**O que acontece:**
- ✅ User vai para lixeira (`deleted_at` preenchido)
- ✅ Professional vinculado TAMBÉM vai para lixeira automaticamente
- ✅ Vínculo na pivot permanece
- ✅ Ambos podem ser restaurados
- ❌ Não permite exclusão se o professional tiver kids vinculados

**⚠️ IMPORTANTE:** Quando um User vinculado a Professional é deletado, o Professional TAMBÉM é movido para lixeira automaticamente, pois ambos estão intimamente ligados.

---

**Restauração de User vinculado a Professional:**

```php
public function restore($id)
{
    $user = User::onlyTrashed()->findOrFail($id);

    // Verifica se o user tem professional vinculado na lixeira
    $professional = $user->professional()->onlyTrashed()->first();

    // Restaura o usuário da lixeira
    $user->restore();

    // Restaura o professional vinculado se existir
    if ($professional) {
        $professional->restore();
    }
}
```

**O que acontece:**
- ✅ User é restaurado
- ✅ Professional vinculado TAMBÉM é restaurado automaticamente
- ✅ Ambos voltam ao estado ativo
- ✅ Vínculo permanece intacto

### 3. Proteção de Roles em Usuários de Profissionais

**Regra de Negócio:** Usuários criados através da criação de um profissional devem permanecer com o papel de 'profissional' para sempre e não podem ter seu role alterado.

**Implementação:**

**Backend (UserController):**
```php
public function update(UserRequest $request, User $user)
{
    $this->authorize('update', $user);

    DB::beginTransaction();
    try {
        // ... atualização dos dados do user ...

        $user->save();

        // Proteção: Não permite mudar roles se o user está vinculado a um Professional
        if ($user->professional->count() > 0) {
            // User vinculado a professional - mantém role 'profissional' fixo
            if (!$user->hasRole('profissional')) {
                $user->assignRole('profissional');
            }
            Log::info('Tentativa de alterar role de user profissional bloqueada.', [
                'user_id' => $user->id,
                'attempted_roles' => $request->roles,
            ]);
        } else {
            // User normal - pode mudar roles livremente
            $user->syncRoles($request->roles);
        }

        DB::commit();
        return redirect()->route('users.edit', $user->id);
    } catch (Exception $e) {
        DB::rollBack();
        return redirect()->back();
    }
}
```

**Frontend (users/edit.blade.php):**
```blade
<div class="col-md-6">
    <label for="role_id" class="form-label">Perfil</label>

    @if($user->professional->count() > 0)
        <div class="alert alert-info mb-2 d-flex align-items-center" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            <small>
                <strong>Perfil bloqueado:</strong> Este usuário está vinculado a um profissional
                e seu perfil não pode ser alterado.
            </small>
        </div>
    @endif

    <select class="form-select"
        id="role_id"
        name="roles[]"
        multiple
        {{ $user->professional->count() > 0 ? 'disabled' : '' }}>
        <option>...Selecione...</option>
        @foreach ($roles as $role)
            <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : "" }}>
                {{ $role->name }}
            </option>
        @endforeach
    </select>
</div>
```

**Comportamento:**
- ✅ Usuários criados via `users.create` podem ter seus roles alterados livremente
- ✅ Usuários criados via `professionals.create` têm o select de roles desabilitado na view
- ✅ Tentativas de alterar roles via backend são bloqueadas e logadas
- ✅ Role 'profissional' é forçado mesmo se alterado manualmente
- ✅ Mensagem explicativa é exibida na interface informando a restrição

**Validação:**
```php
// Verificar se user é vinculado a professional
if ($user->professional->count() > 0) {
    // User vinculado - role protegido
    // Não pode alterar via interface nem programaticamente
}
```

### 4. Verificação de Professional

**Como saber se um User é um Professional:**

```php
// Opção 1: Verificar se tem professional vinculado
if ($user->professional->count() > 0) {
    // É um professional
    $professional = $user->professional->first();
}

// Opção 2: Verificar permissions
if ($user->can('kid-list')) {
    // Tem permissões de professional
}
```

**⚠️ NUNCA USE:** `$user->hasRole('profissional')` - O sistema usa APENAS permissions!

### 5. Ativação e Desativação

**Quando um Professional é desativado:**

```php
public function deactivate(Professional $professional)
{
    $user = $professional->user->first();

    // Desativa o user vinculado
    $user->update([
        'allow' => false,
        'updated_by' => auth()->id(),
    ]);
}
```

**O que acontece:**
- ✅ User vinculado é desativado (`allow = false`)
- ✅ Professional e User ficam inativos juntos
- ✅ Ambos não conseguem mais fazer login
- ✅ Pode ser revertido via ativação

**Quando um Professional é ativado:**

```php
public function activate(Professional $professional)
{
    $user = $professional->user->first();

    // Ativa o user vinculado
    $user->update([
        'allow' => true,
        'updated_by' => auth()->id(),
    ]);
}
```

**O que acontece:**
- ✅ User vinculado é ativado (`allow = true`)
- ✅ Professional e User ficam ativos juntos
- ✅ Ambos podem fazer login normalmente

**⚠️ IMPORTANTE:** Ativação/desativação de Professional SEMPRE afeta o User vinculado.

---

**Visualização na Interface:**

Na lista de usuários (users.index), uma coluna "Status" mostra badges diferenciadas:

- **Badge Verde** `Ativo` - Usuário ativo (`allow = true`)
- **Badge Cinza** `Desativado` - Usuário desativado manualmente (`allow = false`, sem professional)
- **Badge Amarela** `Desativado (Profissional)` - Usuário desativado porque está vinculado a um profissional desativado (`allow = false`, com professional vinculado)

O badge amarelo possui um tooltip explicativo: "Desativado porque está vinculado a um profissional desativado"

Isso permite identificar visualmente quando um usuário está desativado por estar vinculado a um profissional.

### 6. Resumo: Exclusão, Restauração, Ativação e Desativação

**Matriz de Comportamentos:**

| Ação | O que é afetado | Consequência |
|------|-----------------|--------------|
| Deletar Professional | Professional apenas | User permanece ativo |
| Deletar User (sem professional) | User apenas | Comportamento normal |
| Deletar User (COM professional) | User + Professional | Ambos vão para lixeira juntos |
| Restaurar Professional | Professional apenas | User permanece ativo (já estava ativo) |
| Restaurar User (sem professional) | User apenas | Comportamento normal |
| Restaurar User (COM professional na lixeira) | User + Professional | Ambos são restaurados juntos |
| **Desativar Professional** | **User vinculado** | **Ambos ficam inativos (allow=false)** |
| **Ativar Professional** | **User vinculado** | **Ambos ficam ativos (allow=true)** |

**Regras Importantes:**

**Exclusão:**
- ⬆️ Professional deletado → User permanece ativo (exclusão "para cima")
- ⬇️ User deletado → Professional também é deletado (exclusão "para baixo")
- Esta assimetria existe porque o Professional é dependente do User, mas o User pode existir sem Professional

**Ativação/Desativação:**
- 🔄 Professional desativado → User também é desativado (sincronização)
- 🔄 Professional ativado → User também é ativado (sincronização)
- Professional e User vinculado sempre mantêm o mesmo estado (ativo/inativo)

---

## Como Funciona na Prática

### Cenário 1: Usuário comum vs Professional

**Usuário Comum (ex: Responsável/Pais):**
```php
$user->professional->count(); // 0 (sem professional vinculado)
$user->can('kid-list'); // false (sem permissions)
```

**Usuário Professional:**
```php
$user->professional->count(); // 1 (tem professional vinculado)
$user->can('kid-list'); // true (tem permissions)
$professional = $user->professional->first();
$specialty = $professional->specialty->name; // "Psicologia Clínica"
```

### Cenário 2: Listagem de Kids por Professional

**KidsController:**
```php
// Profissionais veem apenas seus kids
if (auth()->user()->professional->count() > 0) {
    $professionalId = auth()->user()->professional->first()->id;

    $kids = Kid::whereHas('professionals', function ($q) use ($professionalId) {
        $q->where('professional_id', $professionalId);
    })->get();
}
```

### Cenário 3: Vinculando Kid a Professional

**Ao criar um Kid:**
```php
$professional = auth()->user()->professional->first();

if ($professional) {
    $kid->professionals()->attach($professional->id, [
        'created_at' => now(),
        'updated_at' => now()
    ]);
}
```

---

## Autorização e Permissions

### Sistema de Permissions (Spatie)

**⚠️ REGRA IMPORTANTE:**
- ✅ **Roles SÃO atribuídos** aos users (para agrupar permissions automaticamente)
- ❌ **Roles NÃO SÃO usados** no código para lógica de negócio (nunca usar `hasRole()`)
- ✅ **Permissions SÃO usados** no código para autorização (sempre usar `can()`)

**Resumo:**
- `$user->assignRole('profissional')` → ✅ OK (atribui o role com suas permissions)
- `if ($user->hasRole('profissional'))` → ❌ NUNCA USAR (use can() ao invés)
- `if ($user->can('kid-list'))` → ✅ SEMPRE USAR (verifica permission)

### Diferença: Atribuir vs Verificar Roles

**Atribuir Role (✅ FAZER):**
```php
// Ao criar professional, atribui o role para agrupar permissions
$user->assignRole('profissional');
```

**Verificar Role (❌ NÃO FAZER):**
```php
// ❌ ERRADO - Nunca usar hasRole() para lógica de negócio
if ($user->hasRole('profissional')) {
    // ...
}

// ✅ CORRETO - Sempre usar can() para verificar autorização
if ($user->can('kid-list')) {
    // ...
}
```

**Por que essa distinção?**
- Roles existem para **agrupar permissions** e facilitar a atribuição
- Permissions são mais **granulares** e flexíveis
- Se mudar o nome do role, o código com `hasRole()` quebra
- Se usar `can()`, o código continua funcionando independente dos roles

**Permissions de Professional:**
- `professional-list` - Listar profissionais
- `professional-show` - Visualizar profissional
- `professional-create` - Criar profissional
- `professional-edit` - Editar profissional
- `professional-delete` - Deletar profissional
- `professional-list-all` - Listar TODOS (admin)
- `professional-show-all` - Ver TODOS (admin)
- `professional-edit-all` - Editar TODOS (admin)
- `professional-delete-all` - Deletar TODOS (admin)
- `professional-activate` - Ativar/desativar
- `professional-deactivate` - Ativar/desativar
- `professional-restore` - Restaurar da lixeira

### ProfessionalPolicy

```php
public function viewAny(User $user): bool
{
    return $user->can('professional-list');
}

public function create(User $user): bool
{
    return $user->can('professional-create');
}

public function update(User $user, Professional $professional): bool
{
    return $user->can('professional-edit');
}

public function delete(User $user, Professional $professional): bool
{
    // Profissional não pode deletar a si mesmo
    $professionalUser = $professional->user->first();
    if ($professionalUser && $user->id === $professionalUser->id) {
        return false;
    }

    return $user->can('professional-delete');
}
```

### Uso em Controllers

```php
// ✅ CORRETO: Usar authorize() com Policy
$this->authorize('create', Professional::class);
$this->authorize('update', $professional);
$this->authorize('delete', $professional);
```

### Uso em Views

```php
<!-- ✅ CORRETO: Usar @can com permission name -->
@can('professional-create')
    <a href="{{ route('professionals.create') }}">Criar</a>
@endcan

@can('professional-edit')
    <a href="{{ route('professionals.edit', $professional) }}">Editar</a>
@endcan

@can('professional-delete')
    <button>Mover para Lixeira</button>
@endcan
```

---

## Exemplos de Código

### Criar Professional com User

```php
use App\Models\User;
use App\Models\Professional;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();

// 1. Criar User
$user = User::create([
    'name' => 'Dr. João Silva',
    'email' => 'joao.silva@example.com',
    'phone' => '(11) 98765-4321',
    'password' => bcrypt('senha-temporaria'),
    'allow' => true,
    'created_by' => auth()->id(),
]);

// 2. Criar Professional
$professional = Professional::create([
    'specialty_id' => 1, // Psicologia Clínica
    'registration_number' => 'CRP 06/123456',
    'bio' => 'Especialista em psicologia infantil...',
    'created_by' => auth()->id(),
]);

// 3. Atribuir role 'profissional' (agrupa permissions automaticamente)
if (\App\Models\Role::where('name', 'profissional')->exists()) {
    $user->assignRole('profissional');
}

// 4. Vincular
$professional->user()->attach($user->id);

DB::commit();

// Nota: O role 'profissional' já vem com as permissions necessárias.
// Se precisar de permissions adicionais específicas:
// $user->givePermissionTo(['alguma-permission-extra']);
```

### Obter dados do Professional a partir do User

```php
$user = auth()->user();

// Verificar se é professional
if ($user->professional->count() > 0) {
    $professional = $user->professional->first();

    echo "Nome: " . $user->name;
    echo "Email: " . $user->email;
    echo "Especialidade: " . $professional->specialty->name;
    echo "Registro: " . $professional->registration_number;
    echo "Kids atendidos: " . $professional->kids->count();
}
```

### Filtrar Kids por Professional autenticado

```php
use App\Models\Kid;

$user = auth()->user();
$professional = $user->professional->first();

if ($professional) {
    // Opção 1: Via relationship
    $kids = $professional->kids;

    // Opção 2: Via query builder
    $kids = Kid::whereHas('professionals', function ($q) use ($professional) {
        $q->where('professional_id', $professional->id);
    })->get();
}
```

---

## Migrations

### 1. Create Specialties Table

```php
Schema::create('specialties', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->unsignedBigInteger('created_by')->nullable();
    $table->unsignedBigInteger('updated_by')->nullable();
    $table->unsignedBigInteger('deleted_by')->nullable();
    $table->timestamps();
    $table->softDeletes();
});
```

### 2. Create Professionals Table

```php
Schema::create('professionals', function (Blueprint $table) {
    $table->id();
    $table->string('registration_number')->nullable();
    $table->text('bio')->nullable();
    $table->foreignId('specialty_id')
        ->constrained('specialties')
        ->restrictOnDelete();
    $table->unsignedBigInteger('created_by')->nullable();
    $table->unsignedBigInteger('updated_by')->nullable();
    $table->unsignedBigInteger('deleted_by')->nullable();
    $table->timestamps();
    $table->softDeletes();
});
```

### 3. Create User_Professional Pivot Table

```php
Schema::create('user_professional', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')
        ->constrained('users')
        ->cascadeOnDelete();
    $table->foreignId('professional_id')
        ->constrained('professionals')
        ->cascadeOnDelete();
    $table->timestamps();

    // ⚠️ GARANTE RELAÇÃO 1:1
    $table->unique('user_id');
});
```

---

## Diagrama de Relacionamentos

```
┌─────────────────┐
│     Users       │
│─────────────────│
│ id              │◄────┐
│ name            │     │
│ email           │     │
│ password        │     │
│ phone           │     │
│ allow           │     │
│ deleted_at      │     │
└─────────────────┘     │
                        │ 1:1
                        │ (via pivot)
                        │
              ┌─────────┴──────────┐
              │  user_professional │
              │────────────────────│
              │ user_id (UNIQUE)   │
              │ professional_id    │
              └─────────┬──────────┘
                        │
                        │
┌─────────────────┐     │ 1:1
│  Professionals  │◄────┘
│─────────────────│
│ id              │
│ registration_no │
│ bio             │
│ specialty_id    │────┐
│ deleted_at      │    │
└─────────────────┘    │
                       │ N:1
                       │
              ┌────────▼────────┐
              │   Specialties   │
              │─────────────────│
              │ id              │
              │ name            │
              │ description     │
              └─────────────────┘
```

---

## Checklist de Implementação

Ao trabalhar com Professional ↔ User, lembre-se:

- ✅ Sempre usar `->first()` ao acessar `$user->professional` ou `$professional->user`
- ✅ Verificar `$user->professional->count() > 0` antes de acessar
- ✅ Role 'profissional' é atribuído automaticamente ao criar professional
- ✅ Nunca usar `hasRole()` na lógica de negócio, sempre usar `can()`
- ✅ Professional soft delete não deleta o User
- ✅ **User soft delete DELETA o Professional vinculado automaticamente**
- ✅ **User restore RESTAURA o Professional vinculado automaticamente**
- ✅ **Professional desativar DESATIVA o User vinculado automaticamente**
- ✅ **Professional ativar ATIVA o User vinculado automaticamente**
- ✅ Não permitir delete se houver kids vinculados (tanto user quanto professional)
- ✅ Professional não pode deletar a si mesmo
- ✅ Sempre usar transactions ao criar Professional+User
- ✅ Permissions devem ser atribuídas manualmente ao User
- ✅ A relação é 1:1, mas implementada como many-to-many com constraint
- ✅ Usuários vinculados a profissionais não podem ter seus roles alterados
- ✅ Role 'profissional' é protegido e forçado para users com professional vinculado
- ✅ Professional e User vinculado sempre mantêm o mesmo estado (ativo/inativo)

---

## Perguntas Frequentes

**Q: Por que usar many-to-many se é 1:1?**
A: Flexibilidade arquitetural. Se no futuro precisar de 1:N, basta remover o constraint UNIQUE.

**Q: O que acontece se deletar o User?**
A: CASCADE: O vínculo na pivot é deletado automaticamente. O Professional fica órfão.

**Q: Posso ter um User sem Professional?**
A: Sim! Usuários comuns (pais, admin) não têm Professional vinculado.

**Q: Como sei se um usuário é professional?**
A: Verifique `$user->professional->count() > 0` ou `$user->can('kid-list')`.

**Q: Devo usar roles ou permissions?**
A: **Depende do contexto:**
   - **Atribuição:** Use `assignRole('profissional')` para atribuir o role ao user (agrupa permissions)
   - **Verificação:** Use `can('permission')` para verificar autorização (NUNCA use `hasRole()`)
   - **Resumo:** Roles são atribuídos, mas permissions são verificadas no código

**Q: Posso alterar o role de um usuário que é profissional?**
A: **Não!** Usuários criados através da criação de um profissional têm o role 'profissional' protegido e não pode ser alterado. A interface mostra um alerta e desabilita o select. Tentativas de alteração via backend são bloqueadas e logadas. Apenas usuários criados diretamente pela tela de usuários podem ter seus roles alterados livremente.

**Q: O que acontece quando deleto um User que é Professional?**
A: **Exclusão em cascata!** Quando você deleta um User que possui Professional vinculado, AMBOS são movidos para a lixeira automaticamente. Isso acontece porque User e Professional vinculados são considerados uma unidade. O sistema verifica se o Professional tem kids vinculados e bloqueia a exclusão caso tenha. Da mesma forma, ao restaurar o User, o Professional também é restaurado automaticamente.

**Q: Por que deletar Professional não deleta o User, mas deletar User deleta o Professional?**
A: **Assimetria intencional!** O Professional é dependente do User (precisa de uma conta de acesso), mas o User pode existir independentemente (pode ser um responsável, admin, etc). Por isso:
- Deletar Professional → User continua ativo (pode virar outro tipo de usuário)
- Deletar User com Professional → Ambos vão para lixeira (não faz sentido ter Professional sem User)

**Q: O que acontece quando desativo um Professional?**
A: **Sincronização automática!** Quando você desativa um Professional (via botão "Desativar" na lista), o User vinculado também é desativado automaticamente (`allow = false`). Ambos ficam inativos e não conseguem mais fazer login. A mesma sincronização acontece ao ativar: Professional ativado → User ativado. Isso garante que Professional e User vinculado sempre mantêm o mesmo estado (ativo/inativo).

**Q: Como identificar visualmente na lista de usuários se um user está desativado por causa do profissional?**
A: **Badges diferenciadas na coluna Status!** Na lista de usuários existe uma coluna "Status" que mostra:
- **Badge Verde "Ativo"** → Usuário ativo e funcionando normalmente
- **Badge Cinza "Desativado"** → Usuário desativado manualmente (não tem professional vinculado)
- **Badge Amarela "Desativado (Profissional)"** → Usuário desativado porque está vinculado a um profissional que foi desativado

A badge amarela possui um tooltip que explica: "Desativado porque está vinculado a um profissional desativado". Isso facilita identificar rapidamente o motivo da desativação.

---

**Documento criado em:** 2025-10-30
**Versão:** 1.4
**Última atualização:** 2025-10-30
- v1.1: Adicionado proteção de roles para usuários profissionais
- v1.2: Adicionado exclusão/restauração em cascata de User → Professional
- v1.3: Adicionado sincronização de ativação/desativação Professional ↔ User
- v1.4: Adicionado coluna Status na lista de usuários com badges diferenciadas (visual para identificar users desativados por profissional)
**Autor:** Claude Code
**Projeto:** Maiêutica - Sistema de Avaliação Cognitiva Infantil
