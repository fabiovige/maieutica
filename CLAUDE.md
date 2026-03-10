# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Maiêutica is a clinical psychology platform for cognitive assessment of children, focusing on tracking progress, managing professionals, guardians, and generating detailed reports. The system is **currently in production** at maieuticavaliacom.br.

**Version:** 1.0.18

**Stack:**
- Backend: Laravel 9.x (PHP ^8.0.2)
- Frontend: Vue 3.5 (Options API) + Bootstrap 5.3 + Chart.js 3.9
- Database: MySQL/MariaDB
- Build: Laravel Mix 6.x (Webpack)
- Auth: Spatie Laravel Permission ^6.9 (permission-based, NOT role-based)

## Critical Production Rules

⚠️ **This system is in production.** Never make changes that could break existing functionality.

- Test all changes manually before committing
- Always use migrations for database changes (never alter tables directly)
- Refactor incrementally and validate before merging
- Never optimize prematurely - stability comes first

## Common Commands

```bash
# Setup
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed && npm run dev

# Development
npm run watch          # Watch and recompile assets
npm run hot            # Hot reload (faster)
php artisan serve      # Start dev server

# Clear caches (custom composer script)
composer clear         # cache, route, view, config, clear-compiled

# Fresh database
composer fresh         # migrate:fresh --seed

# Testing
php artisan test                                    # All tests
php artisan test tests/Unit/ExampleTest.php         # Specific file
php artisan test --filter test_method_name          # Specific method
./vendor/bin/phpunit --testsuite Unit               # Test suite

# Code quality
./vendor/bin/pint                                   # Laravel Pint formatter
./vendor/bin/php-cs-fixer fix                       # PHP CS Fixer
```

## Architecture Overview

### Core Domain

The system centers around **cognitive assessment checklists** for children (Kids) and **medical records** for both children and adults:

```
Kid (Criança) ─────────────────────────────────────────────────────────────
 ├── has many Checklists (evaluations with Competences rated 0-3)
 ├── belongs to Professionals (via kid_professional pivot)
 ├── has many Planes (development plans)
 ├── has many MedicalRecords (morphMany)
 └── has many GeneratedDocuments (morphMany)

User (can be Adult Patient when allow=true AND no professional role)
 ├── has many MedicalRecords (morphMany)
 └── has many GeneratedDocuments (morphMany)

Checklist → checklist_competence (pivot with note 0-3) → Competence → Domain/Level
```

**Note Scale:** 0=Not tested, 1=Emerging, 2=Inconsistent, 3=Consistent

### Models (23 total)

| Category | Models |
|----------|--------|
| **Users & Auth** | `User`, `Professional`, `ProfessionalProfile`, `Specialty`, `Address` |
| **Patients** | `Kid`, `Responsible` |
| **Assessment** | `Checklist`, `ChecklistCompetence`, `Competence`, `Level`, `Domain`, `DomainLevel` |
| **Plans** | `Plane`, `CompetencePlane` |
| **Records** | `MedicalRecord` (polymorphic), `GeneratedDocument` (polymorphic) |
| **Permissions** | `Role`, `Ability`, `AbilityRole` |
| **Logging** | `Log` |

### Controllers

**Web Controllers (15)** - `app/Http/Controllers/`

| Controller | Responsibility |
|------------|----------------|
| `KidsController` | CRUD kids, overview, PDF, charts, level/domain analysis |
| `ChecklistController` | CRUD checklists, fill form, clone, chart view |
| `ProfessionalController` | CRUD professionals, assign patients, activate/deactivate |
| `UserController` | CRUD users, trash management, PDF |
| `RoleController` | CRUD roles, trash management |
| `CompetencesController` | CRUD competences, filter by level/domain |
| `MedicalRecordsController` | CRUD medical records (polymorphic), PDF, trash |
| `DocumentsController` | 6 document models, PDF generation, history |
| `PlaneAutomaticController` | Automatic development plan generation |
| `ProfileController` | Edit profile, change password, upload avatar |
| `TutorialController` | Tutorial pages |
| `HomeController` | Dashboard |
| `AddressController` | Address/CEP management |
| `DocumentationController` | Dynamic documentation |

**API Controllers (8)** - `app/Http/Controllers/Api/`

| Controller | Endpoints |
|------------|-----------|
| `LevelController` | Resource API for levels |
| `DomainController` | Resource API + getInitials |
| `CompetenceController` | Resource API for competences |
| `ChecklistController` | Resource API + getCompetencesByNote |
| `KidController` | Resource API + byuser |
| `PlaneController` | new, delete, store, showCompetences, showByKids |
| `ChecklistRegisterController` | storeSingle, progressbar |
| `ChartController` | percentage calculations |

### Services

- `ChecklistService` - Evaluation logic, calculations
- `OverviewService` - Progress summary and overview

### Layout System

**Novo Layout com Sidebar (v2.0)** - Implementado em 2026-02-08

O sistema utiliza um layout moderno com sidebar vertical, seguindo padrões de sistemas médicos/clínicos:

```
┌─────────────────────────────────────────────────────────────┐
│  [LOGO]          Breadcrumb > Item          [Perfil] [Sair] │  ← Header
├──────────┬──────────────────────────────────────────────────┤
│          │                                                  │
│  MENU    │              CONTEÚDO FLUIDO                    │
│  LATERAL │              (container-fluid)                   │
│          │                                                  │
│  • Item  │                                                  │
│  • Item  │                                                  │
│  • Item  │                                                  │
│          │                                                  │
└──────────┴──────────────────────────────────────────────────┘
```

**Arquivos do Layout:**
- `resources/views/layouts/app.blade.php` - Layout principal (sidebar + header + conteúdo, estilos inline no `<style>`)
- `resources/views/layouts/app-backup.blade.php` - Backup do layout anterior (navbar horizontal)
- `resources/views/layouts/app-sidebar.blade.php` - Implementação alternativa do sidebar (teste)
- `resources/sass/_sidebar-layout.scss` - Estilos SCSS do sidebar (720 linhas, criado mas **não importado** - estilos estão inline no app.blade.php)

**Seções do Sidebar:**
- Dashboard
- Denver (Checklists, Crianças)
- Prontuários
- Documentos
- Cadastros (Profissionais, Usuários, Papéis, Competências, Especialidades)
- Administração (Lixeira com contadores via badges)

**Features:**
- Sidebar fixo à esquerda (260px)
- Container fluid (largura total)
- Header com breadcrumb à esquerda, perfil à direita
- Responsivo: sidebar recolhe em tablets (< 992px)
- Mobile: sidebar vira drawer com overlay
- Toggle para colapsar sidebar em desktop (70px com ícones)
- Estados salvos no localStorage
- Submenus colapsáveis com animação
- Estado ativo com borda esquerda destacada

**Responsividade:**
| Breakpoint | Sidebar | Comportamento |
|------------|---------|---------------|
| ≥992px | Fixo visível (260px) | Pode colapsar para 70px |
| <992px | Drawer | Escondido, botão hamburger |
| <576px | Full width | Breadcrumb mostra só item ativo |

**Para usar em views:**
```blade
@extends('layouts.app')

@section('title', 'Título da Página')

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="#">Pai</a></li>
    <li class="breadcrumb-item active">Atual</li>
@endsection

@section('header-actions')
    {{-- Botões no header (opcional) --}}
    <a href="#" class="btn btn-primary btn-sm">Novo</a>
@endsection

@section('content')
    {{-- Conteúdo com container-fluid automático --}}
@endsection
```

**Nota:** Os estilos do sidebar estão **inline** no `app.blade.php` (dentro de `<style>`), não no SCSS compilado. O arquivo `_sidebar-layout.scss` existe como referência mas não é importado no `app.scss`.

### Frontend (Vue 3 + Blade)

**Vue Components (9)** - `resources/js/components/`

| Component | Purpose |
|-----------|---------|
| `Competences.vue` | **Largest** - Evaluation interface (rating 0-3) |
| `Planes.vue` | Development plan management |
| `Charts.vue` | Radar charts (Chart.js) |
| `Checklists.vue` | List/manage checklists |
| `Dashboard.vue` | Dashboard view |
| `Resume.vue` | Kid summary |
| `Resumekid.vue` | Specific kid summary |
| `TableDescriptions.vue` | Description tables |
| `Initials.vue` | Avatar initials |

**Composables (9)** - `resources/js/composables/`
- `charts.js`, `checklists.js`, `checklistregisters.js`, `competences.js`
- `domains.js`, `kids.js`, `levels.js`, `planes.js`, `expiringStorage.js`

**Data Flow:** Blade renders structure → Vue mounts → Axios fetches data
**DataTables:** jQuery plugin for server-side pagination
**Webpack alias:** `@` → `resources/js/`

### Observers (6)

| Observer | Purpose |
|----------|---------|
| `KidObserver` | Kid create/delete/restore events |
| `ChecklistObserver` | Checklist changes |
| `UserObserver` | User notifications/emails |
| `ProfessionalObserver` | Professional logging |
| `RoleObserver` | Permission changes logging |
| `ResponsibleObserver` | Responsible changes logging |

### Jobs & Notifications

**Jobs:** `SendKidUpdateJob` - Queue job for kid update notifications

**Notifications:**
- `KidUpdateNotification` - Update notification
- `WelcomeNotification` - Welcome notification

**Mails:** `UserCreatedMail`, `UserUpdatedMail`, `UserDeletedMail`

### Helpers & Utilities

**Functions** (`app/helpers.php`):
```php
label_case($text)                  // "snake_case" → "Label Case"
get_progress_color($percentage)    // Returns HTML color by %
get_progress_gradient($percentage) // Returns CSS gradient
get_chart_gradient($percentage)    // Returns chart gradient
```

**Enums:** `ProgressColors` - Color logic by percentage

### Key Features

**Assessment Flow:** Professional creates Checklist → rates Competences (0-3) → system calculates % per Domain → generates radar charts → can clone for longitudinal tracking

**Medical Records (Prontuários):** Polymorphic (`patient_type` = `App\Models\Kid` or `App\Models\User`), uses `forAuthProfessional()` scope for filtering. See `docs/medical-records.md`.

**Document Generation:** HTML stored in `generated_documents` table → PDF generated on-demand via DomPDF. Uses inline CSS, base64 images. Template: `resources/views/documents/declaracao.blade.php`. See `docs/documentos.md`.

**Adult Patients:** User with `allow=true` AND no professional role. Professional→User assignment partially implemented. See `docs/analise_adulto.md`.

**Level/Domain Analysis:** Comparative radar charts at `/analysis/{kidId}/level/{levelId}` for visual progress tracking.

### Database Schema (31 tables)

> Full data dictionary: `docs/dicionario-dados.md`

#### Users & Professionals

| Table | Key Columns | Description |
|-------|-------------|-------------|
| `users` | `id`, `name`, `email`, `password`, `type` enum(i,e), `allow` bool, `phone`, `postal_code`, `street`, `number`, `complement`, `neighborhood`, `city`, `state`, `provider_id`, `provider_email`, `provider_avatar`, `avatar` | System users. Adult patient when `allow=true` + no professional role |
| `professionals` | `id`, `registration_number`, `bio`, `is_intern` bool, `specialty_id` FK | Health professionals. Linked to user via `user_professional` pivot |
| `professional_profiles` | `id`, `user_id` FK UNIQUE, `specialty_id` FK, `registration_number`, `bio` | Legacy table (empty, unused) |
| `specialties` | `id`, `name`, `description` | Professional specialties (Psychology, Speech Therapy, etc.) |

#### Patients

| Table | Key Columns | Description |
|-------|-------------|-------------|
| `kids` | `id`, `user_id` FK, `responsible_id` FK, `name`, `gender` enum(M,F), `ethnicity` enum(8 values), `birth_date`, `photo` | Children (pediatric patients) |

#### Cognitive Assessment

| Table | Key Columns | Description |
|-------|-------------|-------------|
| `checklists` | `id`, `kid_id` FK, `level` enum(1-4), `situation` char (a=open, f=finished), `description` | Cognitive evaluations applied to a child |
| `competences` | `id`, `level_id` FK, `domain_id` FK, `code` int, `description`, `description_detail`, `percentil_25/50/75/90` | Cognitive competences evaluated (446 records) |
| `checklist_competence` | `checklist_id` FK, `competence_id` FK, `note` int(0-3) | **Pivot**: notes per competence. 0=Not tested, 1=Emerging, 2=Inconsistent, 3=Consistent |
| `levels` | `id`, `level` enum(1-4), `name` | Assessment levels (4 records) |
| `domains` | `id`, `name` UNIQUE, `initial` UNIQUE, `color` char(7) | Cognitive domains (19 records, e.g. Language, Motor, Social) |
| `domain_level` | `domain_id` FK, `level_id` FK | **Pivot**: which domains belong to which levels |

#### Development Plans

| Table | Key Columns | Description |
|-------|-------------|-------------|
| `planes` | `id`, `kid_id` FK, `checklist_id` FK, `name`, `is_active` bool | Development plans based on checklists |
| `competence_plane` | `plane_id` FK, `competence_id` FK | **Pivot**: competences included in each plan |

#### Medical Records & Documents

| Table | Key Columns | Description |
|-------|-------------|-------------|
| `medical_records` | `id`, `parent_id` FK(self), `version` int, `is_current_version` bool, `patient_type`, `patient_id`, `session_date`, `complaint`, `objective_technique`, `evolution_notes`, `referral_closure`, `html_content`, `created_by` FK | **Polymorphic** (patient_type=Kid\|User). Self-referencing for versioning |
| `generated_documents` | `id`, `model_type` tinyint, `documentable_type`, `documentable_id`, `professional_id` FK, `generated_by` FK, `html_content`, `form_data` JSON, `metadata` JSON, `generated_at` | **Polymorphic** (documentable_type=Kid\|User). HTML stored, PDF on-demand |
| `document_templates` | `id`, `name`, `type`, `html_content`, `description`, `available_placeholders` JSON, `version`, `is_active` bool | Templates for document generation |

#### Pivot/Relationship Tables

| Table | Key Columns | Description |
|-------|-------------|-------------|
| `kid_professional` | `id`, `kid_id` FK, `professional_id` FK, `is_primary` bool | UNIQUE(kid_id, professional_id). Child-Professional assignment |
| `user_professional` | `id`, `user_id` FK UNIQUE, `professional_id` FK | 1:1 link between User account and Professional record |
| `professional_user_patient` | `id`, `professional_id` FK, `user_id` FK, `is_primary` bool | UNIQUE(professional_id, user_id). Professional-Adult patient (partially implemented) |

#### Authorization (Spatie Permission)

| Table | Key Columns | Description |
|-------|-------------|-------------|
| `roles` | `id`, `name`, `guard_name` | UNIQUE(name, guard_name). Permission containers (3 roles) |
| `permissions` | `id`, `name`, `guard_name` | UNIQUE(name, guard_name). Pattern: `{entity}-{action}[-all]` (93 permissions) |
| `role_has_permissions` | `permission_id` FK, `role_id` FK | PK(permission_id, role_id) |
| `model_has_roles` | `role_id` FK, `model_type`, `model_id` | PK(role_id, model_id, model_type). Polymorphic |
| `model_has_permissions` | `permission_id` FK, `model_type`, `model_id` | PK(permission_id, model_id, model_type). Direct permissions (currently empty) |

#### Audit & Logging

| Table | Key Columns | Description |
|-------|-------------|-------------|
| `logs` | `id`, `object`, `object_id`, `action` enum(insert,update,remove,info), `description`, `creation_date`, `created_by` | Audit log of system actions |

#### Laravel Infrastructure

| Table | Description |
|-------|-------------|
| `sessions` | Database session driver (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) |
| `jobs` | Queue jobs (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`) |
| `failed_jobs` | Failed queue jobs (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) |
| `password_resets` | Password reset tokens (`email`, `token`, `created_at`) |
| `personal_access_tokens` | Sanctum tokens (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`) |
| `migrations` | Laravel migration tracking (`id`, `migration`, `batch`) |

#### Common Database Patterns

- **Soft Delete** (`deleted_at`): `users`, `kids`, `checklists`, `planes`, `professionals`, `professional_profiles`, `specialties`, `roles`, `medical_records`, `generated_documents`, `document_templates`
- **Audit fields** (`created_by`, `updated_by`, `deleted_by` → `users.id`): `users`, `kids`, `checklists`, `planes`, `professionals`, `professional_profiles`, `specialties`, `roles`, `medical_records`, `generated_documents`
- **Polymorphic relations**: `medical_records` (patient_type+patient_id), `generated_documents` (documentable_type+documentable_id), `model_has_roles/permissions` (model_type+model_id), `personal_access_tokens` (tokenable_type+tokenable_id)
- **Enums**: `users.type` (i/e), `kids.gender` (M/F), `kids.ethnicity` (8 values), `checklists.level` (1-4), `levels.level` (1-4), `logs.action` (insert/update/remove/info)

### Key Packages

**Backend:**
- `spatie/laravel-permission` ^6.9 - Permission-based auth
- `yajra/laravel-datatables-oracle` - Server-side DataTables
- `barryvdh/laravel-dompdf` - PDF generation
- `elibyy/tcpdf-laravel` ^9.1 - Alternative PDF
- `laravellegends/pt-br-validator` ^9.1 - Brazilian validation
- `arcanedev/log-viewer` - Browser-based logs
- `laravel/socialite` ^5.16 - OAuth
- `biscolab/laravel-recaptcha` ^6.1 - reCAPTCHA
- `renatomarinho/laravel-page-speed` ^2.1 - Auto minify

**Frontend:**
- `vue` ^3.5.13 (Options API)
- `bootstrap` ^5.3.3
- `chart.js` ^3.9.1
- `sweetalert2` ^11.4.17
- `vee-validate` ^4.6.7
- `jquery-mask-plugin` ^1.14.16
- `axios` ^1.7.9
- `vue-chart-3` ^3.1.8
- `vue3-select2-component` ^0.1.7

### Tipografia

> Documentação completa: `docs/tipografia.md`

**Fonte:** Nunito (Google Fonts) - Visual clean e profissional

**Pesos disponíveis:** 300 (Light), 400 (Regular), 500 (Medium), 600 (Semi-bold), 700 (Bold), 800 (Extra-bold)

**Escala de Tamanhos:**

| Elemento | Tamanho | Uso |
|----------|---------|-----|
| `body` | 1rem (16px) | Texto base |
| `h1` | 1.625rem (26px) | Título de página |
| `h2` | 1.375rem (22px) | Título de seção |
| `h3` | 1.25rem (20px) | Subtítulo |
| `h4` | 1.125rem (18px) | Card header |
| `h5` | 1rem (16px) | Label |
| `h6` | 0.9375rem (15px) | Small header |
| `.form-control` | 0.9375rem (15px) | Inputs e selects |
| `.form-label` | 0.875rem (14px) | Labels de formulário |
| `.table` | 0.875rem (14px) | Corpo de tabela |
| `.table th` | 0.8125rem (13px) | Headers de tabela |
| `.btn` | 0.9375rem (15px) | Botões padrão |
| `.btn-sm` | 0.875rem (14px) | Botões pequenos |
| `.badge` | 0.75rem (12px) | Badges |
| `.fs-xs` | 0.8125rem (13px) | Captions |
| `.fs-sm` | 0.875rem (14px) | Texto secundário |

**Configuração:**
- SCSS: `resources/sass/_config.scss` → `$font-size-base: 1rem`
- CSS Vars: `public/css/custom.css` → `--fs-base`, `--fs-xs`, `--fs-sm`, etc.
- Typography standalone: `public/css/typography.css` → Classes utilitárias adicionais
- PDF: `DejaVu Sans` (requisito DomPDF), classes em `pdf-base.blade.php`

### CSS Architecture

**Arquivos CSS carregados no HTML:**
```html
<link href="{{ asset('css/app.css') }}" rel="stylesheet">   <!-- SCSS compilado -->
<link href="{{ asset('css/custom.css') }}" rel="stylesheet"> <!-- CSS direto -->
<link href="{{ asset('css/typography.css') }}" rel="stylesheet"> <!-- CSS direto -->
```

**SCSS Load Order** (`resources/sass/app.scss`):
```scss
@use './config';        // $primary, $font-size-base
@use './variables';     // Bootstrap variable overrides
@use './_custom';       // Custom SCSS
@use 'bootstrap/scss/bootstrap';
@use './buttons';       // Sistema de botões (após Bootstrap)
```

**Arquivos SCSS:**
| Arquivo | Função |
|---------|--------|
| `_config.scss` | Variáveis base (`$primary: #AD6E9B`, `$font-size-base`) |
| `_variables.scss` | Overrides do Bootstrap |
| `_custom.scss` | Estilos customizados |
| `_buttons.scss` | Sistema de botões padronizado (608 linhas) |
| `_sidebar-layout.scss` | Estilos do sidebar (referência, não importado) |
| `_bootstrap-overrides.scss` | Overrides adicionais do Bootstrap |
| `app.scss` | Entry point + tipografia + estilos da aplicação |

**Importante:**
- `custom.css` e `typography.css` são **standalone** (não compilados) - mudanças são imediatas
- `app.scss` deve ser compilado com `npm run dev` → gera `public/css/app.css`
- Cor primária rosa `#AD6E9B` unificada em SCSS (`_config.scss`) e CSS (`custom.css`)

### Design System - Botões

O sistema possui um **Sistema de Botões Padronizado** em `resources/sass/_buttons.scss` (608 linhas) com estilo clínico/institucional sóbrio.

**Classes disponíveis:**

| Classe | Uso | Cor |
|--------|-----|-----|
| `btn-primary` | Ação principal, salvar | Azul médico (#2563eb) |
| `btn-secondary` | Cancelar, voltar, limpar | Cinza azulado (#64748b) |
| `btn-success` | Confirmar, ativar, download | Verde saúde (#059669) |
| `btn-danger` | Excluir, desativar, alerta | Vermelho (#dc2626) |
| `btn-warning` | Editar, modificar, cautela | Laranja (#d97706) |
| `btn-info` | Visualizar, informação | Ciano (#0891b2) |
| `btn-light` | Ações secundárias | Cinza claro |
| `btn-dark` | Ações especiais | Cinza escuro |

**Variantes Outline:** `btn-outline-primary`, `btn-outline-secondary`, etc.

**Tamanhos:**
- `btn-sm` - 13px (tabelas, ações compactas)
- Padrão - 14px (formulários)
- `btn-lg` - 15px (CTAs importantes)

**Botões Contextuais (especiais):**
- `btn-action-primary` - CTA principal com sombra
- `btn-cancel` - Cancelar/voltar
- `btn-save` - Salvar
- `btn-delete` - Excluir (outline)
- `btn-edit` - Editar (outline)
- `btn-view` - Visualizar (outline)
- `btn-download` - Download
- `btn-restore` - Restaurar da lixeira
- `btn-filter` - Filtrar

**Estados especiais:** `btn-loading` (spinner animado), `btn-has-badge`, `btn-icon` (quadrado), `btn-block` (full width)

**Padrão de botões em tabelas (ícone + texto):**
```blade
<!-- Ações em tabela - SEMPRE usar ícone + label -->
<a href="..." class="btn btn-primary btn-sm"><i class="bi bi-eye"></i> Ver</a>
<a href="..." class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i> Editar</a>
<button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Excluir</button>

<!-- Formulário -->
<button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Salvar</button>
<a href="{{ route('index') }}" class="btn btn-secondary">Cancelar</a>
```

**Guia de referência:** `resources/views/examples/buttons-guide.blade.php` (showcase completo)

**Development:**
- `laravel-pint` ^1.20
- `php-cs-fixer` ^3.68
- `phpunit` ^9.5.10
- `laravel-ide-helper` ^2.15
- `laravel-debugbar` ^3.14

## Testing

**Structure (20 tests):**

```
tests/
├── Feature/
│   ├── Api/ChecklistApiTest.php
│   ├── Auth/AuthenticationTest.php
│   └── Controllers/
│       ├── ChecklistControllerTest.php
│       ├── KidsControllerTest.php
│       └── MedicalRecordsControllerTest.php
└── Unit/
    ├── Models/ (6 tests)
    │   ├── ChecklistModelTest.php
    │   ├── GeneratedDocumentModelTest.php
    │   ├── KidModelTest.php
    │   ├── MedicalRecordModelTest.php
    │   ├── PlaneModelTest.php
    │   └── UserModelTest.php
    └── Policies/ (7 tests)
        ├── ChecklistPolicyTest.php
        ├── GeneratedDocumentPolicyTest.php
        ├── KidPolicyTest.php
        ├── MedicalRecordPolicyTest.php
        ├── PlanePolicyTest.php
        ├── ProfessionalPolicyTest.php
        └── RolePolicyTest.php
```

## Debugging

```bash
# Logs in browser (requires auth): /log-viewer
tail -f storage/logs/laravel.log   # Terminal
# Debug bar: APP_DEBUG=true in .env
```

## Authorization System

### ⚠️ CRITICAL: Permission-Based Authorization (NOT Role-Based)

**✅ ALWAYS use `can()` for authorization:**
```php
$user->can('user-edit')              // Controller/Service
@can('user-edit') ... @endcan        // Blade
$this->authorize('update', $user);   // Policy delegation
```

**❌ NEVER use `hasRole()` for authorization:**
```php
// WRONG - breaks architecture!
if ($user->hasRole('admin')) { }
@role('admin') ... @endrole
```

**Roles** = Containers for grouping permissions (for assignment only: `$user->assignRole('profissional')`)
**Permissions** = Actual authorization checks in code

### Permission Naming: `{entity}-{action}[-all]`

| Pattern | Meaning |
|---------|---------|
| `user-list` | Can list own/assigned records |
| `user-list-all` | Can list ALL records (admin) |
| `user-edit` | Can edit own/assigned records |
| `user-edit-all` | Can edit ALL records (admin) |

**Actions:** `list`, `show`, `create`, `edit`, `delete`, `restore` (uses `edit`)

### Policies (10 total)

All policies follow the same pattern:

```php
public function viewAny(User $user): bool {
    return $user->can('user-list') || $user->can('user-list-all');
}
public function update(User $user, User $model): bool {
    return $user->can('user-edit') || $user->can('user-edit-all');
}
// viewTrash/restore use 'edit' permission
// forceDelete uses only '-all' permission
```

**Available Policies:** `ChecklistPolicy`, `KidPolicy`, `MedicalRecordPolicy`, `GeneratedDocumentPolicy`, `PlanePolicy`, `ProfessionalPolicy`, `UserPolicy`, `RolePolicy`, `ResponsiblePolicy`, `CompetencePolicy`

See `docs/PROFESSIONAL_USER_RELATIONSHIP.md` for detailed authorization patterns.

## Middleware (10)

| Middleware | Purpose |
|------------|---------|
| `AclMiddleware` | Access control |
| `Authenticate` | Auth verification |
| `SecurityHeaders` | Security headers |
| `VerifyCsrfToken` | CSRF protection |
| `EncryptCookies` | Cookie encryption |
| `TrimStrings` | Input trimming |
| `TrustHosts` / `TrustProxies` | Proxy/host trust |
| `RedirectIfAuthenticated` | Guest redirect |
| `PreventRequestsDuringMaintenance` | Maintenance mode |

## Documentation (`docs/` folder - 20 files)

| File | Description |
|------|-------------|
| `tipografia.md` | **NEW** - Auditoria completa + plano de padronização tipográfica (5 fases) |
| `novo-layout-sidebar.md` | **NEW** - Documentação do layout sidebar |
| `dicionario-dados.md` | **NEW** - Dicionário de dados completo (31 tabelas) |
| `medical-records.md` | Medical Records system (polymorphic, versioning) |
| `documentos.md` | Document Generation History (HTML storage, PDF on-demand) |
| `analise_adulto.md` | Adult patients analysis (partially implemented) |
| `PROFESSIONAL_USER_RELATIONSHIP.md` | Professional-User relationships and lifecycle |
| `MANUAL_ATUALIZACAO_PRODUCAO.md` | Production deployment manual |
| `PRD.md` | Product Requirements Document |
| `VISÃO GERAL.md` | System overview |
| `implementacao-prontuarios.md` | Medical records implementation |
| `implementacao-cep.md` | CEP autocomplete implementation |
| `cep-autocomplete.md` | CEP feature docs |
| `checklistLogger.md` | Checklist logging |
| `professionalLogger.md` | Professional logging |
| `roleLogger.md` | Role logging |
| `userLogger.md` | User logging |
| `routes_checklist.md` | Checklist routes |
| `adulto.md` | Adult analysis |
| `jira.md` | Jira tracking |

## Recent Changes

- **2026-02-09:** Padronização de botões em tabelas (ícone + label) em todas as views de listagem e lixeira
- **2026-02-08:** Novo layout com sidebar vertical (substituiu navbar horizontal), ajuste de fonte para 15px na área de conteúdo
- **2026-02-08:** Padronização tipográfica completa (5 fases): Nunito, escala sóbria, CSS vars, PDF DejaVu Sans, cor rosa unificada
- **2026-02-08:** Sistema de botões padronizado (`_buttons.scss`) com paleta clínica/institucional
- **2026-02-08:** Dicionário de dados completo (`docs/dicionario-dados.md`)
- **2026-01-27:** Added `is_intern` field to `professionals` table (for intern tracking)
- **2025-12-28:** Created `professional_user_patient` pivot table
- **2025-12-22:** Medical records with polymorphic support and versioning
- **2025-12-06:** Generated documents with polymorphic support

## Project Statistics

| Metric | Value |
|--------|-------|
| Models | 23 |
| Controllers | 23 (15 web + 8 API) |
| Vue Components | 9 |
| Composables | 9 |
| Blade Templates | ~150 |
| Migrations | 19 |
| Tests | 20 |
| Policies | 10 |
| Observers | 6 |
| Documentation Files | 20 |
| Database Tables | 31 |
| SCSS Files | 7 |
| CSS Files (public) | 4 (app.css, custom.css, typography.css, cep-autocomplete.css) |

## Blade Components

| Component | Path | Purpose |
|-----------|------|---------|
| `kid-info-card` | `resources/views/components/kid-info-card.blade.php` | Card de informações da criança |
| `table-actions` | `resources/views/components/table-actions.blade.php` | **NEW** - Componente reutilizável para botões de ação em tabelas |

## Important Notes

- **Brazilian localization:** `laravellegends/pt-br-validator` for CPF, dates, phones
- **Page speed:** Auto minify via `laravel-page-speed` middleware
- **IDE helpers:** Auto-generated on `composer update`
- **Windows dev:** Project developed on MINGW64, paths may differ
- **OAuth:** Socialite configured for provider-based login
- **reCAPTCHA:** Both v2 and v3 configured
- **Sidebar styles inline:** Os estilos do sidebar estão no `<style>` do `app.blade.php`, não no SCSS compilado. O arquivo `_sidebar-layout.scss` existe como referência.
- **Font size base:** 16px (1rem) unificado em todos os arquivos (SCSS, CSS, inline).
- **Known limitation:** Professionals cannot create medical records for adult patients via UI. Workaround: Admin creates on behalf. Requires `professional_user_patient` pivot completion.
