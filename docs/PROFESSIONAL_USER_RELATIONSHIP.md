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

    // 2. Criar Professional
    $professional = Professional::create([
        'specialty_id' => $request->specialty_id,
        'registration_number' => $request->registration_number,
        'bio' => $request->bio,
        'created_by' => auth()->id(),
    ]);

    // 3. Vincular User ao Professional
    $professional->user()->attach($user->id);

    DB::commit();
}
```

**Resultado:**
- ✅ 1 registro em `users`
- ✅ 1 registro em `professionals`
- ✅ 1 registro em `user_professional` (pivot)

### 2. Exclusão de Professional

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

### 3. Verificação de Professional

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

**O sistema NÃO usa roles para lógica de negócio, apenas permissions!**

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

// 3. Vincular
$professional->user()->attach($user->id);

// 4. Atribuir permissions ao user
$user->givePermissionTo([
    'kid-list',
    'kid-create',
    'kid-edit',
    'checklist-list',
    'checklist-create',
]);

DB::commit();
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
- ✅ Nunca usar `hasRole()` na lógica de negócio, apenas `can()`
- ✅ Professional soft delete não deleta o User
- ✅ Não permitir delete se houver kids vinculados
- ✅ Professional não pode deletar a si mesmo
- ✅ Sempre usar transactions ao criar Professional+User
- ✅ Permissions devem ser atribuídas manualmente ao User
- ✅ A relação é 1:1, mas implementada como many-to-many com constraint

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
A: **APENAS PERMISSIONS!** Roles servem só para agrupar permissions internamente.

---

**Documento criado em:** 2025-10-30
**Versão:** 1.0
**Autor:** Claude Code
**Projeto:** Maiêutica - Sistema de Avaliação Cognitiva Infantil
