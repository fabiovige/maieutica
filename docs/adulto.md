# Análise: Sistema de Pacientes Adultos no Maiêutica

**Data:** 23/12/2025
**Contexto:** Investigação sobre por que pacientes adultos não aparecem no filtro de prontuários para profissionais

---

## 📋 Resumo Executivo

**Problema identificado:** O filtro de prontuários não mostra pacientes adultos (Users) quando um profissional está logado, mas funciona para admin.

**Causa raiz:** O método `getUserPatientsForUser()` retorna uma coleção vazia para profissionais porque o sistema de atribuição de pacientes adultos a profissionais ainda não foi implementado (há um TODO explícito no código).

**Status:** ⚠️ Implementação parcial - funcional para admin, incompleto para profissionais

---

## 1. 🔍 PROBLEMA: Filtro não mostra pacientes adultos para profissionais

**Arquivo:** `app/Http/Controllers/MedicalRecordsController.php` (linhas 602-612)

```php
private function getUserPatientsForUser()
{
    if (auth()->user()->can('medical-record-list-all')) {
        // Admin sees all active users
        return User::where('allow', 1)->orderBy('name')->get();
    }

    // Professional sees only their assigned user patients
    // Temporarily returns empty until assignment system is implemented
    return collect([]);  // ← RETORNA VAZIO PARA PROFISSIONAIS
}
```

### Comportamento Atual

| Tipo de Usuário | Resultado |
|-----------------|-----------|
| **Admin** | ✅ Funciona - retorna todos Users ativos (`where('allow', 1)`) |
| **Profissional** | ❌ Retorna coleção vazia - nada aparece no filtro |

### Impacto na UI

**View:** `resources/views/medical-records/index.blade.php` (linhas 71-76)

```blade
<select name="patient_id" id="patient_id">
    <option value="">Todos</option>
    @if(request('patient_type') === 'App\\Models\\User')
        @foreach($users as $user)  {{-- $users está vazio para profissionais --}}
            <option value="{{ $user->id }}">{{ $user->name }}</option>
        @endforeach
    @endif
</select>
```

**JavaScript:** (linhas 240-247)
```javascript
} else if (patientType === 'App\\Models\\User') {
    // Adicionar Users
    users.forEach(function(user) {  // Array vazio = nenhuma opção
        $patientSelect.append(
            $('<option></option>').val(user.id).text(user.name)
        );
    });
}
```

---

## 2. 👤 COMO FUNCIONA O CADASTRO DE PACIENTES ADULTOS

### Fluxo de Cadastro

**Controller:** `app/Http/Controllers/UserController.php` (método `store`)

1. Admin acessa: **Cadastro > Usuários > Novo Usuário**
2. Preenche formulário (`resources/views/users/create.blade.php`):
   - Nome, Email, Telefone
   - Endereço completo (CEP, logradouro, número, complemento, bairro, cidade, estado)
   - **Perfil (Roles):** deixa em branco para paciente
   - **Status:** "Liberado para acessar o sistema" (`allow=true`)
3. Sistema cria User e envia senha temporária por email (`WelcomeNotification`)

### Diferenciação: User Profissional vs User Paciente

**Não há campo explícito!** A diferenciação é por **ausência de relacionamento Professional**:

```php
// Verificação no código:
if ($user->professional->count() > 0) {
    // É PROFISSIONAL (tem registro em user_professional pivot)
    // Tem permissões: professional-list, medical-record-create, etc.
} else if ($user->allow) {
    // É PACIENTE ADULTO
    // Apenas allow=true, sem roles ou professional vinculado
}
```

**Model User.php (linha 71-73):**
```php
public function professional()
{
    return $this->belongsToMany(Professional::class, 'user_professional');
}
```

**Tabela pivot existente:** `user_professional`
- Relaciona User **como profissional** (não como paciente)
- Estrutura: `user_id` → `professional_id`
- **NÃO é usada para pacientes adultos**

### Características do User Paciente

- `allow = true` (ativo no sistema)
- `professional()->count() === 0` (não é profissional)
- Pode receber prontuários via `morphMany(MedicalRecord)`
- Pode fazer login no sistema (se desejar acessar prontuários)

---

## 3. 🏥 SISTEMA DE PRONTUÁRIOS (POLIMÓRFICO)

### Estrutura do Banco de Dados

**Migration:** `database/migrations/2025_12_22_145946_create_medical_records_table.php`

```php
$table->morphs('patient'); // Cria: patient_id + patient_type
// patient_type armazena: 'App\Models\Kid' OU 'App\Models\User'
```

### Tipos de Paciente Suportados

| Tipo | Valor no Banco | Badge na UI | Model |
|------|----------------|-------------|-------|
| **Criança** | `App\Models\Kid` | `<span class="badge bg-info">Criança</span>` | `Kid` |
| **Adulto** | `App\Models\User` | `<span class="badge bg-secondary">Adulto</span>` | `User` |

### View de Criação

**Arquivo:** `resources/views/medical-records/create.blade.php` (linhas 50-51)

```blade
<select name="patient_type" id="patient_type" class="form-control" required>
    <option value="">Selecione o tipo de paciente</option>
    <option value="App\Models\Kid">Criança</option>
    <option value="App\Models\User">Adulto</option>
</select>
```

### Model MedicalRecord.php

**Relacionamento polimórfico (linha 42-45):**
```php
public function patient()
{
    return $this->morphTo();
}
```

**Accessor para nome do tipo (linhas 123-130):**
```php
public function getPatientTypeNameAttribute()
{
    return match($this->patient_type) {
        'App\\Models\\Kid' => 'Criança',
        'App\\Models\\User' => 'Adulto',
        default => 'Desconhecido',
    };
}
```

---

## 4. ⚖️ COMPARAÇÃO: Kids vs Users (Atribuição a Profissionais)

| Aspecto | Kids (Crianças) | Users (Adultos) |
|---------|-----------------|-----------------|
| **Tabela Pivot** | `kid_professional` ✅ | **NÃO EXISTE** ❌ |
| **Relacionamento** | `Kid->professionals()` ✅ | **NÃO DEFINIDO** ❌ |
| **Filtro para Profissional** | Funciona (filtra pela pivot) ✅ | Retorna vazio ❌ |
| **Método Controller** | `getKidsForUser()` funcional ✅ | `getUserPatientsForUser()` retorna `collect([])` ❌ |

### Código Funcional para Kids

**Arquivo:** `app/Http/Controllers/MedicalRecordsController.php` (linhas 580-598)

```php
private function getKidsForUser()
{
    if (auth()->user()->can('medical-record-list-all')) {
        // Admin sees all kids
        return Kid::orderBy('name')->get();
    }

    // Professional sees only assigned kids
    $professional = auth()->user()->professional->first();

    if (!$professional) {
        return collect([]);
    }

    return Kid::whereHas('professionals', function ($q) use ($professional) {
        $q->where('professional_id', $professional->id);
    })->orderBy('name')->get();
}
```

**Model Kid.php (linha 78-82):**
```php
public function professionals()
{
    return $this->belongsToMany(Professional::class, 'kid_professional')
        ->whereNull('professionals.deleted_at');
}
```

### Código Incompleto para Users

**Arquivo:** `app/Http/Controllers/MedicalRecordsController.php` (linhas 602-612)

```php
private function getUserPatientsForUser()
{
    if (auth()->user()->can('medical-record-list-all')) {
        // Admin sees all active users
        return User::where('allow', 1)->orderBy('name')->get();
    }

    // Professional sees only their assigned user patients
    // TODO: Filtrar apenas Users atribuídos ao profissional
    // Temporarily returns empty until assignment system is implemented
    return collect([]);  // ← PROBLEMA
}
```

**Falta em User.php:**
```php
// NÃO EXISTE:
public function assignedProfessionals()
{
    return $this->belongsToMany(Professional::class, 'professional_user_patient');
}
```

**Falta em Professional.php:**
```php
// NÃO EXISTE:
public function patients()
{
    return $this->belongsToMany(User::class, 'professional_user_patient');
}
```

---

## 5. 📝 EVIDÊNCIAS DE TODO NO CÓDIGO

### Documentação (medical-records.md)

**Linha 32:**
> "Important: Kids maintain relationship with Professionals via `kid_professional` pivot. Users (adult patients) will have similar or direct relationship with Professional **(to be defined in implementation).**"

**Linhas 477-490:** Método com TODO comment explícito

**Linhas 901-917:** Seção "CRITICAL POINTS" menciona implementação incompleta

### Model MedicalRecord.php

**Linha 158:**
```php
// OR medical records of Users (adult patients) assigned
// TODO: implement User->Professional assignment logic when defined
->orWhere(function ($subQ) use ($professional) {
    $subQ->where('patient_type', 'App\\Models\\User');
    // Temporarily allow viewing for any professional
});
```

### Policy MedicalRecordPolicy.php

**Linhas 49-54:**
```php
// If User (adult patient), check assignment
// TODO: implement when User->Professional relationship is defined
if ($medicalRecord->patient_type === 'App\\Models\\User') {
    // Temporarily allow viewing for any professional
    return true;
}
```

---

## 6. 📊 IMPACTO ATUAL NO SISTEMA

### Tabela de Funcionalidades

| Situação | Admin | Profissional |
|----------|-------|--------------|
| **Filtrar por "Adulto"** | ✅ Vê todos Users ativos | ❌ Não vê nenhum User |
| **Criar prontuário para adulto** | ✅ Pode selecionar qualquer User | ❌ Dropdown vazio |
| **Ver prontuários existentes** | ✅ Vê todos | ✅ Vê apenas os que criou |
| **Editar prontuário de adulto** | ✅ Pode editar qualquer | ✅ Apenas os que criou |
| **Deletar prontuário de adulto** | ✅ Pode deletar qualquer | ✅ Apenas os que criou |

### Workaround Atual

Quando **admin** cria prontuário para um profissional:
- Campo `created_by` é setado para o `user_id` do profissional
- Profissional consegue ver/editar esse prontuário
- Mas profissional **não consegue criar novos** para adultos (dropdown vazio)

---

## 7. 💡 SOLUÇÃO PROPOSTA

### Opção A: Criar Tabela Pivot (Recomendado)

**Segue o padrão arquitetural existente (`kid_professional`)**

#### 1. Criar Migration

**Arquivo:** `database/migrations/YYYY_MM_DD_create_professional_user_patient_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('professional_user_patient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_id')
                  ->constrained('professionals')
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->timestamps();

            // Evitar duplicatas
            $table->unique(['professional_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('professional_user_patient');
    }
};
```

#### 2. Adicionar Relacionamento em Professional.php

```php
/**
 * Patients (adult users) assigned to this professional
 */
public function patients()
{
    return $this->belongsToMany(User::class, 'professional_user_patient')
                ->whereNull('users.deleted_at')
                ->where('users.allow', 1)
                ->orderBy('users.name');
}
```

#### 3. Adicionar Relacionamento em User.php

```php
/**
 * Professionals assigned to this user (when user is a patient)
 */
public function assignedProfessionals()
{
    return $this->belongsToMany(Professional::class, 'professional_user_patient')
                ->whereNull('professionals.deleted_at');
}
```

#### 4. Atualizar MedicalRecordsController.php

**Método getUserPatientsForUser():**

```php
private function getUserPatientsForUser()
{
    if (auth()->user()->can('medical-record-list-all')) {
        // Admin sees all active users
        return User::where('allow', 1)->orderBy('name')->get();
    }

    // Professional sees only their assigned user patients
    $professional = auth()->user()->professional->first();

    if (!$professional) {
        return collect([]);
    }

    return $professional->patients()
                        ->orderBy('name')
                        ->get();
}
```

#### 5. Criar Interface de Atribuição

**Nova view:** `resources/views/professionals/assign-patients.blade.php`

Funcionalidades:
- Admin seleciona Professional
- Vê lista de Users (pacientes adultos) disponíveis
- Atribui/remove pacientes do profissional
- Similar à tela de atribuição de Kids a Profissionais

**Nova rota em web.php:**
```php
Route::get('professionals/{professional}/assign-patients', [ProfessionalController::class, 'assignPatients'])
     ->name('professionals.assign-patients');
Route::post('professionals/{professional}/sync-patients', [ProfessionalController::class, 'syncPatients'])
     ->name('professionals.sync-patients');
```

#### 6. Atualizar MedicalRecord Scope

**Em app/Models/MedicalRecord.php (linhas 136-163):**

```php
public function scopeForAuthProfessional(Builder $query)
{
    $user = auth()->user();
    $professional = $user->professional->first();

    if (!$professional) {
        return $query->whereRaw('1 = 0');
    }

    return $query->where(function ($q) use ($professional, $user) {
        // Records created by this professional
        $q->where('created_by', $user->id)
          // OR records of Kids assigned to professional
          ->orWhere(function ($subQ) use ($professional) {
              $subQ->where('patient_type', 'App\\Models\\Kid')
                   ->whereIn('patient_id', function ($kidQuery) use ($professional) {
                       $kidQuery->select('kid_id')
                                ->from('kid_professional')
                                ->where('professional_id', $professional->id);
                   });
          })
          // OR records of Users (patients) assigned to professional
          ->orWhere(function ($subQ) use ($professional) {
              $subQ->where('patient_type', 'App\\Models\\User')
                   ->whereIn('patient_id', function ($userQuery) use ($professional) {
                       $userQuery->select('user_id')
                                 ->from('professional_user_patient')
                                 ->where('professional_id', $professional->id);
                   });
          });
    });
}
```

---

### Opção B: Campo Direto (Menos Flexível)

**Não recomendado**, mas mais simples:

```php
// Migration: Adicionar em users table
$table->foreignId('professional_id')->nullable()->constrained()->nullOnDelete();
```

**Desvantagens:**
- Um paciente só pode ter **um profissional**
- Não segue padrão N:N do sistema (Kid pode ter múltiplos profissionais)
- Quebra consistência arquitetural

---

## 8. 📁 ARQUIVOS CRÍTICOS PARA MODIFICAR

### Se Implementar Opção A (Recomendado)

| Arquivo | Ação | Descrição |
|---------|------|-----------|
| `database/migrations/YYYY_MM_DD_create_professional_user_patient_table.php` | **Criar** | Nova tabela pivot |
| `app/Models/Professional.php` | **Editar** | Adicionar `patients()` relationship |
| `app/Models/User.php` | **Editar** | Adicionar `assignedProfessionals()` relationship |
| `app/Http/Controllers/MedicalRecordsController.php` | **Editar** | Atualizar `getUserPatientsForUser()` |
| `app/Models/MedicalRecord.php` | **Editar** | Atualizar scope `forAuthProfessional()` |
| `app/Http/Controllers/ProfessionalController.php` | **Editar** | Adicionar métodos `assignPatients()` e `syncPatients()` |
| `resources/views/professionals/assign-patients.blade.php` | **Criar** | Interface de atribuição |
| `routes/web.php` | **Editar** | Adicionar rotas de atribuição |
| `database/seeders/RoleAndPermissionSeeder.php` | **Editar** | Adicionar permissões `professional-assign-patients` |

---

## 9. ✅ CADASTRO ATUAL DE PACIENTES ADULTOS (FUNCIONAL)

### Como Cadastrar Hoje

1. Admin vai em: **Cadastro > Usuários > Novo Usuário**
2. Preenche formulário:
   - Nome: "Maria Silva"
   - Email: "maria@exemplo.com"
   - Telefone: "(11) 98765-4321"
   - Endereço completo
3. **Perfil (Roles):** Deixa em branco (não seleciona nenhum)
4. **Liberado para acessar o sistema:** ✓ Checked
5. Salva → User criado com `allow=true`

### Resultado

```php
User {
    id: 123,
    name: "Maria Silva",
    email: "maria@exemplo.com",
    phone: "(11) 98765-4321",
    allow: true,
    roles: [],  // Nenhum role = paciente
    professional()->count(): 0  // Não é profissional
}
```

### Como Criar Prontuário para Paciente Adulto (Admin)

1. Admin vai em: **Prontuário > Evolução dos Casos > Novo Prontuário**
2. Seleciona:
   - **Tipo de Paciente:** "Adulto"
   - **Paciente:** "Maria Silva - maria@exemplo.com"
   - **Profissional:** (se admin criando para profissional)
3. Preenche dados do prontuário
4. Salva → Prontuário criado com `patient_type = 'App\Models\User'`

---

## 10. 🔄 PRÓXIMOS PASSOS RECOMENDADOS

### Fase 1: Decisão (Stakeholder)

- [ ] **Decidir:** Implementar atribuição Professional→User paciente?
  - **Sim:** Seguir para Fase 2
  - **Não:** Manter workaround atual (apenas admin cria para adultos)

### Fase 2: Implementação (Se aprovado)

1. [ ] Criar migration `professional_user_patient`
2. [ ] Adicionar relationships em `Professional` e `User` models
3. [ ] Atualizar `getUserPatientsForUser()` no controller
4. [ ] Atualizar scope `forAuthProfessional()` no model
5. [ ] Criar interface de atribuição (view + controller methods)
6. [ ] Adicionar rotas de atribuição
7. [ ] Adicionar permissões para atribuição
8. [ ] Testar fluxo completo:
   - Admin atribui paciente adulto a profissional
   - Profissional vê paciente no filtro
   - Profissional cria prontuário para paciente
   - Profissional vê prontuário criado

### Fase 3: Documentação

- [ ] Atualizar `medical-records.md` removendo TODOs
- [ ] Documentar novo relacionamento em `PROFESSIONAL_USER_RELATIONSHIP.md`
- [ ] Atualizar `CLAUDE.md` com novo padrão

---

## 📝 Conclusão

### Estado Atual

O sistema **suporta** pacientes adultos e **funciona** parcialmente:

- ✅ Admin consegue cadastrar pacientes adultos (Users)
- ✅ Admin consegue criar prontuários para qualquer User
- ✅ Profissionais conseguem ver prontuários que criaram
- ❌ Profissionais **não conseguem** filtrar/selecionar Users no dropdown
- ❌ Profissionais **não conseguem** criar novos prontuários para adultos

### Motivo

Não há sistema de atribuição de pacientes adultos a profissionais (diferente de Kids que têm `kid_professional` pivot).

### Recomendação Final

**Implementar Opção A: Tabela pivot `professional_user_patient`**

**Razões:**
1. Consistência arquitetural (segue padrão de `kid_professional`)
2. Flexibilidade (N:N - paciente pode ter múltiplos profissionais)
3. Fácil manutenção (mesma lógica que Kids)
4. Completar funcionalidade iniciada (remover TODOs)

**Estimativa:** 4-6 horas de desenvolvimento + testes

---

**Arquivo gerado em:** 23/12/2025
**Por:** Claude Code (análise automatizada)
