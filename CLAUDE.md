# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Maiêutica is a clinical psychology platform for cognitive assessment of children, focusing on tracking progress, managing professionals, guardians, and generating detailed reports. The system is **currently in production** at maieuticavaliacom.br.

**Stack:**
- Backend: Laravel 9.x (PHP 8+)
- Frontend: Vue 3 + Bootstrap 5 + Chart.js
- Database: MySQL/MariaDB
- Build: Laravel Mix (Webpack)

## Critical Production Rules

⚠️ **This system is in production.** Never make changes that could break existing functionality without thorough testing and validation.

- Prioritize stability and backwards compatibility
- Test all changes manually before committing
- Refactor incrementally and validate in staging
- Never optimize prematurely - stability comes first
- Document significant changes

## Common Commands

### Development Setup
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve
```

### Development Workflow
```bash
# Watch and recompile assets during development
npm run watch

# Hot reload (faster development)
npm run hot

# Build for production
npm run production

# Clear all Laravel caches
composer clear
# This runs: cache:clear, route:clear, view:clear, config:clear, clear-compiled

# Fresh database with seeders
composer fresh
# This runs: php artisan migrate:fresh --seed
```

### Testing
```bash
# Run all tests
php artisan test
# or
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit --testsuite Unit
./vendor/bin/phpunit --testsuite Feature

# Run specific test file
php artisan test tests/Unit/ExampleTest.php
./vendor/bin/phpunit tests/Feature/ExampleTest.php

# Run specific test method
php artisan test --filter test_method_name
```

### Code Quality
```bash
# Run Laravel Pint (code formatter)
./vendor/bin/pint

# Run PHP CS Fixer
./vendor/bin/php-cs-fixer fix

# Generate IDE helper files (already runs on composer update)
php artisan ide-helper:generate
php artisan ide-helper:meta
```

## Architecture Overview

### Core Domain Structure

The system centers around **cognitive assessment checklists** for children/patients (Kids) and **medical records** for both children and adults:

- **Kids (Crianças)**: Central entity representing child patients
- **Users (Adultos)**: Can also function as adult patients (when not assigned professional roles)
- **Checklists**: Cognitive evaluation forms filled by professionals
- **Competences**: Individual cognitive skills/abilities evaluated
- **Domains**: Groups of related competences (cognitive areas)
- **Levels**: Difficulty/complexity levels for competences
- **Planes**: Intervention/development plans generated from assessments
- **Medical Records (Prontuários)**: Clinical evolution records for both Kids and Users (polymorphic)

### Key Relationships

```
Kid (Criança)
 ├── has many Checklists (evaluations over time)
 ├── belongs to Professionals (assigned therapists via kid_professional pivot)
 ├── has Planes (development plans)
 ├── has many MedicalRecords (morphMany - polymorphic)
 └── has many GeneratedDocuments (morphMany - polymorphic)

User (Paciente Adulto quando allow=true e sem role de profissional)
 ├── has many MedicalRecords (morphMany - polymorphic)
 ├── has many GeneratedDocuments (morphMany - polymorphic)
 └── belongs to Professionals (via professional_user_patient pivot - TO BE IMPLEMENTED)

MedicalRecord (Prontuário)
 ├── belongs to Patient (morphTo - Kid OR User)
 ├── belongs to Professional (who signed)
 ├── belongs to User (who generated it - created_by)
 └── supports versioning and session tracking

GeneratedDocument (Histórico de Documentos)
 ├── belongs to Documentable (morphTo - Kid OR User)
 ├── belongs to Professional (who signed)
 ├── stores HTML content for PDF regeneration
 └── tracks metadata (IP, user_agent, title, etc.)

Checklist
 ├── belongs to Kid
 ├── has many Competences (through pivot checklist_competence)
 └── stores evaluation notes (0=not tested, 1=emerging, 2=inconsistent, 3=consistent)

Competence
 ├── belongs to Domain
 ├── belongs to Level
 └── appears in multiple Checklists

Domain
 └── groups related Competences (e.g., cognitive areas)

Plane (Development Plan)
 ├── belongs to Kid
 └── contains CompetencePlanes (targeted skills to develop)
```

### Backend Architecture

**MVC + Services Pattern:**
- **Controllers** (`app/Http/Controllers/`): Handle HTTP requests, delegate to services
  - `KidsController.php`: Most complex controller (patient management, PDF generation, overview charts)
  - `ChecklistController.php`: Evaluation management, chart visualization
  - `CompetencesController.php`: Competence/skill management with domain filtering
  - `DocumentsController.php`: Professional document generation (declarations, reports) using standardized PDF templates, includes document history
  - `MedicalRecordsController.php`: Medical records (prontuários) management for Kids and Users (polymorphic), with versioning support
  - `ProfessionalController.php`: Professional management including patient assignment (Kids and Users)

- **Models** (`app/Models/`): Eloquent ORM models with relationships
  - Uses `BaseModel` for common functionality
  - Most models use soft deletes and timestamps

- **Services** (`app/Services/`): Business logic layer
  - `ChecklistService`: Calculate development percentages from evaluations
  - `OverviewService`: Generate analytical data for dashboards

- **Policies** (`app/Policies/`): Authorization logic using Spatie Laravel Permission
  - Role-based access control (RBAC)

- **Helpers** (`app/helpers.php`): Global helper functions
  - Autoloaded via composer.json
  - Includes `get_progress_color()`, `get_progress_gradient()` for visual feedback

### Frontend Architecture

**Vue 3 Components** (`resources/js/components/`):
- Single File Components without Composition API (Options API style)
- Major components:
  - `Competences.vue`: Complex competence evaluation interface (largest component)
  - `Planes.vue`: Development plan creation/editing
  - `Charts.vue`: Chart.js wrapper for radar/line charts
  - `Checklists.vue`: Checklist management interface

**Data Flow:**
- Blade templates render initial page structure
- Vue components mount and fetch data via Axios
- DataTables (jQuery plugin) for server-side pagination on index pages
- Chart.js for data visualization (radar charts for competence analysis)

**Asset Compilation** (`webpack.mix.js`):
- Vue 3 SFC compilation
- SCSS compilation with Bootstrap 5
- Alias: `@` points to `resources/js/`

### Database Layer

**Key Tables:**
- `kids`: Child patient records
- `users`: User accounts (can also function as adult patients when allow=true and no professional role)
- `checklists`: Evaluation sessions (for Kids)
- `competences`: Cognitive skills library
- `domains`: Competence groupings
- `levels`: Difficulty levels
- `checklist_competence`: Pivot with evaluation notes (0-3 scale)
- `planes`: Development plans
- `competence_planes`: Skills targeted in plans
- `medical_records`: Clinical evolution records (polymorphic: Kids OR Users)
- `generated_documents`: Document generation history (stores HTML for PDF regeneration)
- `professional_user_patient`: Pivot for Professional-User (adult patient) assignment (TO BE FULLY IMPLEMENTED)
- `kid_professional`: Pivot for Professional-Kid assignment
- `users`, `roles`, `permissions`: Spatie permission system

**Note Scale:**
- 0: Not tested
- 1: Emerging
- 2: Inconsistent
- 3: Consistent (fully developed)

### Key Features & Flows

**Assessment Flow:**
1. Professional creates Checklist for a Kid
2. Fills evaluation by rating Competences (0-3 scale)
3. System calculates development percentages per Domain
4. Generates visual charts (radar charts for competence profiles)
5. Can clone previous checklists for longitudinal tracking

**Reporting:**
- PDF generation using DomPDF and TCPDF packages
- Overview dashboard with progress tracking
- Comparative analysis between evaluation sessions
- Automatic development plan generation based on weakest competences

**Medical Records (Prontuários) System:**
- Clinical evolution tracking for both Kids (children) and Users (adults)
- **Polymorphic relationship**: `patient_type` field stores `App\Models\Kid` or `App\Models\User`
- **Versioning support**: Tracks session history and evolution over time
- **PDF generation**: Medical records can be exported to PDF
- **Authorization**:
  - Admin can view/edit all medical records
  - Professionals can view/edit only records of their assigned patients or records they created
  - Scope `forAuthProfessional()` filters records based on professional assignment
- **Features**:
  - Session tracking with dates
  - Development notes and observations
  - Professional signatures
  - Trash/restore functionality
- **Note**: Professional→User (adult patient) assignment is partially implemented. Currently:
  - ✅ Admin can create medical records for any User
  - ✅ Professionals can view records they created
  - ❌ Professionals cannot create new records for adult patients (dropdown filter empty)
  - 🚧 Requires full implementation of `professional_user_patient` pivot table relationship

**Adult Patients System:**
- **User as Patient**: Users can function as adult patients when `allow = true` and user has no professional role
- **Differentiation**:
  - User is **professional** when linked in `user_professional` pivot (has professional relationship)
  - User is **adult patient** when `allow = true` AND not linked to any professional role
- **Registration Flow** (by Admin):
  1. Create User via Cadastro > Usuários > Novo Usuário
  2. Leave "Perfil (Roles)" blank
  3. Check "Liberado para acessar o sistema" (`allow=true`)
  4. User becomes adult patient (can receive medical records and documents)
- **Limitations** (Current Implementation):
  - Professional-to-User patient assignment system not fully implemented
  - Workaround: Admin creates records on behalf of professionals
  - See `docs/adulto.md` and `docs/analise_adulto.md` for detailed analysis

**Document Generation History:**
- All generated documents (6 models: Declaração, Laudo, Parecer, Relatório) are now **stored in database**
- **Storage method**: HTML content stored (not PDF file), allows unlimited regeneration
- **Table**: `generated_documents` with polymorphic relationship to Kids/Users
- **Features**:
  - Historical tracking with audit trail (who generated, when, from where)
  - PDF regeneration on-demand from stored HTML
  - Metadata tracking (IP, user agent, document title)
  - Form data preservation (JSON)
  - Download history accessible at `/documents/history`
- **Authorization**: Same pattern as medical records (professionals see own + assigned patients)
- **Benefits**:
  - Compliance and audit requirements
  - No disk storage needed (HTML stored in DB, PDF generated in memory)
  - Can update template and regenerate old documents
- See `docs/documentos.md` for complete implementation details

**PDF Document Generation (Standard Pattern):**
- **Controller:** `DocumentsController` (`app/Http/Controllers/DocumentsController.php`)
- **Standard Template:** `declaracao.blade.php` (`resources/views/documents/declaracao.blade.php`)
- **Key Implementation Details:**
  - Uses `Barryvdh\DomPDF\Facade\Pdf` for PDF generation
  - Images (logos, watermarks) are base64-encoded and embedded directly in HTML using `data:image/png;base64,{encoded_data}`
  - Template uses **inline CSS** (required by DomPDF - external stylesheets don't work reliably)
  - **Fixed header/footer** using `position: fixed` with negative top/bottom positioning
  - **Watermark** using `position: fixed` with low opacity (0.60) and z-index: -1
  - **Layout structure:**
    - Header: Logo centered at top
    - Title: Centered, uppercase, bold
    - Content: Justified text with dynamic data interpolation
    - Signature: Centered with line, professional name and CRP
    - Footer: Contact information with SVG icons (aligned left)
    - Date/location: Right-aligned at bottom
  - **Font:** DejaVu Sans (default, supports UTF-8/special characters)
  - **Paper format:** A4 portrait via `->setPaper('A4', 'portrait')`
  - **Output method:** `->stream('filename.pdf')` for browser preview, or `->download()` for direct download
- **Asset Requirements:**
  - Watermark: `public/images/bg-doc.png`
  - Logo: `public/images/logo-doc.jpg`
  - SVG Icons (footer): `public/images/{globe, telephone-fill, whatsapp, geo-alt-fill}.svg`
- **Data Structure Pattern:**
  ```php
  $data = [
      'nome_paciente' => 'Patient Name',
      'dias_horarios' => 'Schedule description',
      'previsao_termino' => 'End date (optional)',
      'nome_psicologo' => 'Psychologist Name',
      'crp' => 'CRP Number',
      'cidade' => 'City',
      'data_formatada' => 'Formatted date',
      'watermark' => base64_encode(file_get_contents(...)),
      'logo' => base64_encode(file_get_contents(...)),
  ];
  ```
- **CSS Positioning Strategy:**
  - Content sections use `position: relative` with `top` offset to control vertical spacing
  - Margins: `body` has 80px top, 40px sides, 60px bottom to accommodate fixed header/footer
  - Header fixed at `top: -40px`, footer at `bottom: -10px`
- **When creating new document types:**
  - Copy `declaracao.blade.php` as template base
  - Modify content structure as needed
  - Keep header/footer/watermark pattern consistent
  - Ensure all images are base64-encoded
  - Use inline CSS only
  - Test with different content lengths to ensure proper page breaks

**File Uploads:**
- **Kid Photo/Avatar Upload:**
  - **Accepted formats:** JPG, JPEG, PNG, BMP, GIF, SVG, WEBP (Laravel `image` validation)
  - **Maximum size:** 1 MB (1024 KB)
  - **Storage location:** `public/images/kids/`
  - **Naming convention:** `{timestamp}_{kid_id}.{extension}`
  - **Implementation:** `KidsController::uploadPhoto()` at line 867
  - **Validation:** Located at `app/Http/Controllers/KidsController.php:870-872`
  - **Note:** No automatic image resizing - images should be pre-sized appropriately
  - Old photos are automatically deleted when new ones are uploaded

**Security & Authorization:**
- Laravel Sanctum for API authentication
- Spatie Laravel Permission for role/permission management
- **Permission-Based Authorization System** (CRITICAL):
  - ✅ **ALWAYS use `can()` for authorization checks** - e.g., `$user->can('user-edit')`
  - ❌ **NEVER use `hasRole()` in business logic** - e.g., `$user->hasRole('admin')`
  - Roles are assigned to users (`assignRole()`) to group permissions
  - Authorization checks use ONLY permissions (`can()`)
  - See "Authorization System" section below for detailed explanation
- ReCAPTCHA v3 integration (biscolab/laravel-recaptcha)
- CSRF protection on all forms
- Remember-me functionality on login

### Important Packages

**Backend:**
- `spatie/laravel-permission`: Role-based access control
- `yajra/laravel-datatables-oracle`: Server-side DataTables
- `barryvdh/laravel-dompdf`: PDF generation
- `arcanedev/log-viewer`: Web-based log viewer
- `renatomarinho/laravel-page-speed`: Performance optimization middleware
- `laravellegends/pt-br-validator`: Brazilian Portuguese validation rules

**Frontend:**
- `vue@3.5`: Vue 3 framework
- `chart.js@3.9`: Data visualization
- `sweetalert2`: Modern alerts/modals
- `vee-validate`: Form validation
- `jquery-mask-plugin`: Input masking (CPF, phone, etc.)
- `bootstrap@5.3`: UI framework

## Development Guidelines

### Backend (from .cursor/rules/backend.mdc)

- Use Eloquent efficiently with eager loading to avoid N+1 queries
- Validate inputs with Laravel Form Requests
- Keep controllers thin - use Services for complex business logic
- Use middleware for authorization checks
- Implement caching for frequently accessed data
- Use queues for time-consuming tasks
- Transactions for multi-step database operations
- Never hardcode credentials - use `.env`
- Log errors and important events appropriately

### Frontend (from .cursor/rules/frontend.mdc)

- Follow Vue 3 best practices with component composition
- Break UI into reusable components
- Avoid overusing reactivity (watch/computed)
- Validate forms using Vee-Validate
- Ensure WCAG accessibility guidelines
- Test across browsers and devices
- Optimize bundle size (minify CSS/JS, compress images)

### Code Quality Standards

- Follow SOLID principles and keep code DRY
- Write clean, readable code with appropriate documentation
- Follow PSR standards for PHP, ESLint for JavaScript
- Remove commented-out code before committing
- Prioritize simplicity over complexity
- Use descriptive commit messages (Conventional Commits style)
- Conduct code reviews before merging

## Debugging & Monitoring

```bash
# View application logs in browser (requires auth)
# Visit: /log-viewer

# View logs in terminal
tail -f storage/logs/laravel.log

# Enable debug bar (already installed in dev)
# Set APP_DEBUG=true in .env
# Debug bar appears at bottom of pages
```

## Authorization System

### ⚠️ CRITICAL: Permission-Based Authorization (NOT Role-Based)

This system uses **Permission-Based Authorization**, NOT traditional Role-Based Authorization. This is a fundamental architectural decision that MUST be followed throughout the codebase.

### The Golden Rules

**✅ DO:**
- Use `$user->can('permission-name')` for ALL authorization checks
- Use `@can('permission-name')` in Blade views
- Assign roles to users with `$user->assignRole('role-name')` (roles group permissions)
- Use Policies with `$user->can('permission')` internally
- Check permissions like: `user-list`, `user-edit`, `user-delete`, `user-list-all`, etc.

**❌ NEVER:**
- Use `$user->hasRole('role-name')` for authorization logic
- Use `@role('role-name')` in Blade views
- Hard-code role names like `'admin'`, `'profissional'` in conditions
- Check roles in controllers, policies, or business logic

### Why Permissions, Not Roles?

1. **Flexibility**: Permissions are granular and can be combined flexibly
2. **Maintainability**: Changing role names doesn't break code
3. **Scalability**: Easy to add new permissions without touching role checks
4. **Clarity**: `can('user-edit')` is clearer than `hasRole('admin')`

### How It Works

```php
// ✅ CORRECT - Permission-based check
if ($user->can('user-edit')) {
    // Allow editing
}

// ❌ WRONG - Role-based check (DO NOT USE)
if ($user->hasRole('admin')) {
    // This breaks our architecture!
}
```

### Permission Naming Convention

Permissions follow the pattern: `{entity}-{action}[-all]`

**Standard Actions:**
- `list` - View listing/index
- `show` - View single record
- `create` - Create new records
- `edit` - Update existing records
- `delete` - Soft delete (move to trash)
- `restore` - Restore from trash (uses `edit` permission)

**Suffixes:**
- No suffix: Basic permission (can act on own records or assigned records)
- `-all`: Admin permission (can act on ALL records globally)

**Examples:**
```
user-list         → Can list users
user-list-all     → Can list ALL users (admin)
user-edit         → Can edit users
user-edit-all     → Can edit ALL users (admin)
professional-create     → Can create professionals
professional-delete-all → Can delete ANY professional (admin)
```

### Policy Pattern

All policies follow this standardized pattern:

```php
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('user-list') || $user->can('user-list-all');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('user-edit') || $user->can('user-edit-all');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can('user-delete') || $user->can('user-delete-all');
    }

    // viewTrash and restore use 'edit' permission
    public function viewTrash(User $user): bool
    {
        return $user->can('user-edit') || $user->can('user-list-all');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->can('user-edit') || $user->can('user-edit-all');
    }
}
```

**Pattern Rules:**
1. Always check base permission OR `-all` permission
2. `viewTrash()` uses `edit` OR `list-all` permission
3. `restore()` uses `edit` OR `edit-all` permission (restore is editing state)
4. `forceDelete()` only uses `-all` permission (destructive action)

### Blade View Examples

```blade
{{-- ✅ CORRECT - Use @can with permission --}}
@can('user-create')
    <a href="{{ route('users.create') }}">Create User</a>
@endcan

@can('user-edit')
    <button>Edit</button>
@endcan

@can('user-delete')
    <button>Delete</button>
@endcan

{{-- ❌ WRONG - Don't use @role --}}
@role('admin')
    <button>Admin Only</button>
@endrole
```

### Controller Examples

```php
// ✅ CORRECT - Use authorize() with Policy
public function index()
{
    $this->authorize('viewAny', User::class);
    // Policy internally checks: can('user-list') || can('user-list-all')
}

public function update(User $user)
{
    $this->authorize('update', $user);
    // Policy internally checks: can('user-edit') || can('user-edit-all')
}

// ✅ CORRECT - Direct permission check
if (auth()->user()->can('user-list-all')) {
    // Admin can see all users
}
```

### Roles vs Permissions

**What are Roles for?**
- Roles exist to **group permissions** for easier assignment
- Example: `'profissional'` role has permissions: `kid-list`, `kid-create`, `checklist-fill`, etc.
- Assign with: `$user->assignRole('profissional')`

**What are Permissions for?**
- Permissions are used for **actual authorization checks** in code
- Example: `if ($user->can('kid-list'))`
- NEVER check roles in authorization logic!

**Summary:**
- **Roles** = Containers for permissions (for organizational purposes)
- **Permissions** = Authorization logic (for security/access control)

### Available Permissions (Examples)

**Users:**
- `user-list`, `user-list-all`
- `user-show`, `user-show-all`
- `user-create`, `user-create-all`
- `user-edit`, `user-edit-all`
- `user-delete`, `user-delete-all`

**Roles:**
- `role-list`, `role-list-all`
- `role-show`, `role-show-all`
- `role-create`, `role-create-all`
- `role-edit`, `role-edit-all`
- `role-delete`, `role-delete-all`

**Professionals:**
- `professional-list`, `professional-list-all`
- `professional-show`, `professional-show-all`
- `professional-create`, `professional-create-all`
- `professional-edit`, `professional-edit-all`
- `professional-delete`, `professional-delete-all`
- `professional-activate`, `professional-deactivate`

**Kids:**
- `kid-list`, `kid-list-all`
- `kid-create`, `kid-edit`, `kid-delete`
- Similar pattern for other entities

**Medical Records:**
- `medical-record-list`, `medical-record-list-all`
- `medical-record-show`, `medical-record-show-all`
- `medical-record-create`
- `medical-record-edit`, `medical-record-edit-all`
- `medical-record-delete`, `medical-record-delete-all`

**Documents:**
- `document-list`, `document-list-all`
- `document-show`, `document-show-all`
- `document-download`
- `document-delete`, `document-delete-all`

### Important Notes on Policies

All policies (RolePolicy, UserPolicy, ProfessionalPolicy) are **standardized** to follow the same pattern:
1. Each method checks base permission OR `-all` permission
2. Trash/restore methods use `edit` permission (not a separate `restore` permission)
3. Force delete only uses `-all` permission
4. No role checks (`hasRole`) anywhere in policies

See `docs/PROFESSIONAL_USER_RELATIONSHIP.md` for detailed documentation on Professional-User relationships and authorization patterns.

## Additional Documentation

For detailed documentation on specific topics, see the `docs/` folder:

- `jira.md` - **Jira-friendly summary of feat/prontuario branch** - Complete feature documentation for project management
- `PROFESSIONAL_USER_RELATIONSHIP.md` - Comprehensive guide on Professional-User relationships, authorization patterns, and lifecycle management (creation, deletion, activation/deactivation)
- `implementacao-prontuarios.md` - Complete implementation plan for Medical Records system
- `medical-records.md` - Technical documentation for Medical Records system (961 lines)
- `adulto.md` / `analise_adulto.md` - Analysis of adult patients system and Professional-User patient assignment (partially implemented)
- `documentos.md` - Complete documentation on Document Generation History system (HTML storage, PDF regeneration, audit trail)
- `checklistLogger.md`, `professionalLogger.md`, `roleLogger.md`, `userLogger.md` - Logging documentation for specific entities
- `cep-autocomplete.md`, `implementacao-cep.md` - Brazilian postal code (CEP) integration documentation
- `routes_checklist.md` - Route structure documentation
- `MANUAL_ATUALIZACAO_PRODUCAO.md` - Production deployment and update manual
- `PRD.md`, `VISÃO GERAL.md` - Product requirements and overview documentation

## Important Notes

- The system uses Brazilian Portuguese localization (`lucascudo/laravel-pt-br-localization`)
- CPF validation and Brazilian date/phone formats are handled by `laravellegends/pt-br-validator`
- Page speed optimizations are automatic via middleware (minify HTML/CSS/JS)
- IDE helper files are regenerated automatically on `composer update`
- Session lifetime is configurable via SESSION_LIFETIME in .env (default 120 minutes)
- **Database changes:** Never alter database tables without explicit authorization
- **Authorization:** See "Authorization System" section - NEVER use `hasRole()` for authorization logic, ALWAYS use `can()` with permissions
- **Windows development:** This project is developed on Windows (MINGW64). Most commands work cross-platform, but file paths use Windows format in local development
- **Always use migrations:** Como estamos usando Laravel, sempre use migrations para não danificar o banco e os dados de produção, mesmo em ambiente de desenvolvimento
- **Recent Features (v2.2.0):**
  - ✅ **Medical Records System**: Fully implemented - manages clinical records for Kids and Users (adults)
  - ✅ **Document Generation History**: Fully implemented - stores HTML, enables PDF regeneration and audit trail
  - 🚧 **Adult Patients**: Partially implemented - Users can be adult patients, but Professional→User assignment needs completion (see `docs/analise_adulto.md`)
  - ⚠️ **Known Limitation**: Professionals cannot currently create medical records for adult patients via UI (dropdown empty). Workaround: Admin creates on behalf of professionals. Full implementation requires `professional_user_patient` pivot table relationship completion.