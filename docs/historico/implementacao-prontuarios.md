# Plano de Implementação: Sistema de Prontuários Médicos

## Objetivo

Implementar sistema completo de prontuários médicos (Medical Records) como **nova feature isolada**, garantindo **zero impacto** em funcionalidades existentes em produção.

## ⚠️ Garantias de Segurança

> [!IMPORTANT]
> Esta é uma **feature completamente nova** que será implementada de forma isolada:
> - ✅ **Novas tabelas** - sem modificar tabelas existentes
> - ✅ **Novos models** - código 100% novo
> - ✅ **Novas rotas** - namespace isolado
> - ✅ **Novas permissões** - não afeta permissões existentes
> - ✅ **Migrations reversíveis** - rollback seguro disponível

> [!WARNING]
> **Modificações Mínimas em Código Existente:**
> Apenas 3 arquivos existentes serão modificados com adições seguras:
> 1. `Kid.php` - adicionar método de relacionamento (não afeta queries existentes)
> 2. `User.php` - adicionar método de relacionamento (não afeta queries existentes)
> 3. `web.php` - adicionar novas rotas (isoladas)
> 4. `menu.blade.php` - adicionar novo dropdown (condicional por permissão)
> 5. `AuthServiceProvider.php` - registrar nova policy
> 6. `RoleAndPermissionSeeder.php` - adicionar novas permissões

---

## Arquivos Novos (100% Isolados)

### Database

#### [NEW] Migration: `create_medical_records_table.php`
- Nova tabela `medical_records` com relacionamento polimórfico
- Não afeta tabelas existentes
- Reversível com `php artisan migrate:rollback`

### Models

#### [NEW] `app/Models/MedicalRecord.php`
- Model completamente novo
- Relacionamento polimórfico com `Kid` e `User`
- Soft deletes, audit trail (created_by, updated_by, deleted_by)

### Authorization

#### [NEW] `app/Policies/MedicalRecordPolicy.php`
- Policy isolada para Medical Records
- Segue padrão existente do sistema
- Permissões: 9 novas (não afeta existentes)

### Validation

#### [NEW] `app/Http/Requests/MedicalRecordRequest.php`
- Validação de formulários
- Validação condicional polimórfica (Kid vs User)

### Controller

#### [NEW] `app/Http/Controllers/MedicalRecordsController.php`
- Controller completo com CRUD
- Métodos: index, create, store, show, edit, update, destroy, trash, restore
- Segue padrão existente (KidsController)

### Logging

#### [NEW] `app/Services/Logging/MedicalRecordLogger.php`
- Logger LGPD-compliant
- Sanitização de dados sensíveis
- Segue padrão existente (KidLogger)

### Views

#### [NEW] `resources/views/medical-records/index.blade.php`
Listagem de prontuários com filtros

#### [NEW] `resources/views/medical-records/create.blade.php`
Formulário de criação

#### [NEW] `resources/views/medical-records/edit.blade.php`
Formulário de edição

#### [NEW] `resources/views/medical-records/show.blade.php`
Visualização detalhada

#### [NEW] `resources/views/medical-records/trash.blade.php`
Lixeira (admin only)

---

## Modificações em Arquivos Existentes

> [!CAUTION]
> As modificações abaixo são **mínimas e seguras**. Cada uma foi cuidadosamente planejada para não afetar funcionalidades existentes.

### [MODIFY] `app/Models/Kid.php`

**Adição:** Método de relacionamento polimórfico

```php
// Adicionar no final da classe, antes do fechamento
public function medicalRecords()
{
    return $this->morphMany(MedicalRecord::class, 'patient')
                ->orderBy('session_date', 'desc');
}
```

**Impacto:** ✅ ZERO
- Apenas adiciona novo método
- Não modifica queries existentes
- Não afeta relacionamentos atuais
- Lazy loading - só carrega quando chamado explicitamente

---

### [MODIFY] `app/Models/User.php`

**Adição:** Método de relacionamento polimórfico

```php
// Adicionar no final da classe, antes do fechamento
public function medicalRecords()
{
    return $this->morphMany(MedicalRecord::class, 'patient')
                ->orderBy('session_date', 'desc');
}
```

**Impacto:** ✅ ZERO
- Apenas adiciona novo método
- Não modifica queries existentes
- Não afeta relacionamentos atuais
- Lazy loading - só carrega quando chamado explicitamente

---

### [MODIFY] `routes/web.php`

**Adição:** Novas rotas isoladas

```php
// Adicionar após rotas de checklists (linha ~58)

// Medical Records
Route::middleware(['auth'])->group(function () {
    Route::get('medical-records/trash', [MedicalRecordsController::class, 'trash'])
        ->name('medical-records.trash');
    Route::post('medical-records/{id}/restore', [MedicalRecordsController::class, 'restore'])
        ->name('medical-records.restore');
    Route::resource('medical-records', MedicalRecordsController::class);
});
```

**Import necessário:**
```php
use App\Http\Controllers\MedicalRecordsController;
```

**Impacto:** ✅ ZERO
- Rotas completamente novas
- Namespace isolado (`/medical-records/*`)
- Não conflita com rotas existentes
- Middleware `auth` padrão do sistema

---

### [MODIFY] `resources/views/layouts/menu.blade.php`

**Adição:** Novo dropdown no menu

```blade
{{-- Adicionar após dropdown "Documents" --}}

@can('medical-record-list')
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle @if (request()->is('medical-records*')) active @endif"
           href="#"
           id="medicalRecordsDropdown"
           role="button"
           data-bs-toggle="dropdown"
           aria-expanded="false">
            <i class="bi bi-file-medical"></i> Prontuários
        </a>
        <ul class="dropdown-menu" aria-labelledby="medicalRecordsDropdown">
            <li>
                <a class="dropdown-item" href="{{ route('medical-records.index') }}">
                    <i class="bi bi-list"></i> Listar Prontuários
                </a>
            </li>
            @can('medical-record-create')
                <li>
                    <a class="dropdown-item" href="{{ route('medical-records.create') }}">
                        <i class="bi bi-plus-circle"></i> Novo Prontuário
                    </a>
                </li>
            @endcan
            @can('medical-record-list-all')
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="{{ route('medical-records.trash') }}">
                        <i class="bi bi-trash"></i> Lixeira
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcan
```

**Impacto:** ✅ ZERO
- Apenas adiciona novo item de menu
- Condicional por permissão (`@can`)
- Não afeta itens existentes
- Usuários sem permissão não veem o menu

---

### [MODIFY] `app/Providers/AuthServiceProvider.php`

**Adição:** Registrar nova policy

```php
use App\Models\MedicalRecord;
use App\Policies\MedicalRecordPolicy;

protected $policies = [
    // ... existentes
    MedicalRecord::class => MedicalRecordPolicy::class,
];
```

**Impacto:** ✅ ZERO
- Apenas registra nova policy
- Não afeta policies existentes
- Padrão do sistema

---

### [MODIFY] `database/seeders/RoleAndPermissionSeeder.php`

**Adição:** Novas permissões

```php
// Adicionar no array $permissions (linha ~113)
'medical-record-list',
'medical-record-show',
'medical-record-create',
'medical-record-edit',
'medical-record-delete',
'medical-record-list-all',
'medical-record-show-all',
'medical-record-edit-all',
'medical-record-delete-all',

// Adicionar no array $permissionsProfissional (linha ~164)
'medical-record-list',
'medical-record-show',
'medical-record-create',
'medical-record-edit',
'medical-record-delete',
```

**Impacto:** ✅ ZERO
- Apenas adiciona novas permissões
- Não modifica permissões existentes
- Roles existentes não são afetados
- Executar: `php artisan db:seed --class=RoleAndPermissionSeeder`

---

## Estratégia de Implementação Segura

### Fase 1: Backend (Sem Interface)
1. ✅ Criar migration
2. ✅ Criar models e relacionamentos
3. ✅ Criar policy e adicionar permissões
4. ✅ Criar request validation
5. ✅ Criar controller
6. ✅ Criar logger
7. ✅ Adicionar rotas

**Validação:** Testar via Postman/Insomnia antes de criar interface

### Fase 2: Frontend
8. ✅ Criar views
9. ✅ Adicionar menu

**Validação:** Testar manualmente cada tela

### Fase 3: Testing
10. ✅ Testes manuais completos
11. ✅ Validar autorização
12. ✅ Validar logs LGPD

---

## Plano de Rollback

Se necessário reverter a feature:

```bash
# 1. Remover migration
php artisan migrate:rollback --step=1

# 2. Remover permissões do banco (opcional)
# Executar SQL manual ou recriar seeder

# 3. Deletar arquivos novos
# Todos os arquivos [NEW] listados acima

# 4. Reverter modificações
# Git checkout dos 6 arquivos modificados
```

---

## Verificação de Impacto Zero

### Testes de Regressão Recomendados

Após implementação, validar que funcionalidades existentes continuam funcionando:

- [ ] Login/Logout
- [ ] CRUD de Kids (crianças)
- [ ] CRUD de Checklists
- [ ] CRUD de Competences
- [ ] CRUD de Professionals
- [ ] CRUD de Users
- [ ] Geração de PDFs existentes
- [ ] Dashboard/Overview
- [ ] Permissions existentes

---

## Cronograma Estimado

- **Fase 1 (Backend):** ~2-3 horas
- **Fase 2 (Frontend):** ~2-3 horas
- **Fase 3 (Testing):** ~1-2 horas

**Total:** ~5-8 horas de desenvolvimento + testes

---

## Próximos Passos

1. ✅ Revisar este plano
2. ✅ Aprovar implementação
3. ✅ Iniciar Fase 1 (Backend)
