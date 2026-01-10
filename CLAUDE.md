# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Maiêutica is a clinical psychology platform for cognitive assessment of children, focusing on tracking progress, managing professionals, guardians, and generating detailed reports. The system is **currently in production** at maieuticavaliacom.br.

**Stack:**
- Backend: Laravel 9.x (PHP 8.0+)
- Frontend: Vue 3 (Options API) + Bootstrap 5 + Chart.js
- Database: MySQL/MariaDB
- Build: Laravel Mix (Webpack)
- Auth: Spatie Laravel Permission (permission-based, NOT role-based)

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

### Backend (MVC + Services)

- **Controllers** (`app/Http/Controllers/`): HTTP handling, delegates to services
  - `KidsController`: Most complex (patient management, PDF, charts)
  - `ChecklistController`, `MedicalRecordsController`, `DocumentsController`
- **Models** (`app/Models/`): Eloquent with `BaseModel`, soft deletes, timestamps
- **Services** (`app/Services/`): Business logic (`ChecklistService`, `OverviewService`)
- **Policies** (`app/Policies/`): Permission-based authorization (see Authorization section)
- **Helpers** (`app/helpers.php`): `get_progress_color()`, `get_progress_gradient()`

### Frontend (Vue 3 + Blade)

- **Vue Components** (`resources/js/components/`): Options API style
  - `Competences.vue`: Complex evaluation interface (largest)
  - `Planes.vue`, `Charts.vue`, `Checklists.vue`
- **Data Flow**: Blade renders structure → Vue mounts → Axios fetches data
- **DataTables**: jQuery plugin for server-side pagination on index pages
- **Chart.js**: Radar charts for competence profiles
- **Webpack alias**: `@` → `resources/js/`

### Key Features

**Assessment Flow:** Professional creates Checklist → rates Competences (0-3) → system calculates % per Domain → generates radar charts → can clone for longitudinal tracking

**Medical Records (Prontuários):** Polymorphic (`patient_type` = `App\Models\Kid` or `App\Models\User`), uses `forAuthProfessional()` scope for filtering. See `docs/medical-records.md`.

**Document Generation:** HTML stored in `generated_documents` table → PDF generated on-demand via DomPDF. Uses inline CSS, base64 images. Template: `resources/views/documents/declaracao.blade.php`. See `docs/documentos.md`.

**Adult Patients:** User with `allow=true` AND no professional role. Professional→User assignment partially implemented. See `docs/analise_adulto.md`.

### Key Packages

**Backend:** `spatie/laravel-permission`, `yajra/laravel-datatables-oracle`, `barryvdh/laravel-dompdf`, `laravellegends/pt-br-validator`

**Frontend:** `vue@3.5`, `chart.js@3.9`, `sweetalert2`, `vee-validate`, `jquery-mask-plugin`, `bootstrap@5.3`

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

### Policy Pattern (all policies follow this)

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

See `docs/PROFESSIONAL_USER_RELATIONSHIP.md` for detailed authorization patterns.

## Documentation (`docs/` folder)

| File | Description |
|------|-------------|
| `medical-records.md` | Medical Records system (polymorphic, versioning) |
| `documentos.md` | Document Generation History (HTML storage, PDF on-demand) |
| `analise_adulto.md` | Adult patients analysis (partially implemented) |
| `PROFESSIONAL_USER_RELATIONSHIP.md` | Professional-User relationships and lifecycle |
| `MANUAL_ATUALIZACAO_PRODUCAO.md` | Production deployment manual |

## Important Notes

- **Brazilian localization:** `laravellegends/pt-br-validator` for CPF, dates, phones
- **Page speed:** Auto minify via `laravel-page-speed` middleware
- **IDE helpers:** Auto-generated on `composer update`
- **Windows dev:** Project developed on MINGW64, paths may differ
- **Known limitation:** Professionals cannot create medical records for adult patients via UI. Workaround: Admin creates on behalf. Requires `professional_user_patient` pivot completion.