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

The system centers around **cognitive assessment checklists** for children/patients (Kids):

- **Kids (Crianças)**: Central entity representing patients
- **Checklists**: Cognitive evaluation forms filled by professionals
- **Competences**: Individual cognitive skills/abilities evaluated
- **Domains**: Groups of related competences (cognitive areas)
- **Levels**: Difficulty/complexity levels for competences
- **Planes**: Intervention/development plans generated from assessments

### Key Relationships

```
Kid (Criança)
 ├── has many Checklists (evaluations over time)
 ├── belongs to Professionals (assigned therapists)
 └── has Planes (development plans)

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
- `kids`: Patient records
- `checklists`: Evaluation sessions
- `competences`: Cognitive skills library
- `domains`: Competence groupings
- `levels`: Difficulty levels
- `checklist_competence`: Pivot with evaluation notes (0-3 scale)
- `planes`: Development plans
- `competence_planes`: Skills targeted in plans
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

### Important Notes on Policies

All policies (RolePolicy, UserPolicy, ProfessionalPolicy) are **standardized** to follow the same pattern:
1. Each method checks base permission OR `-all` permission
2. Trash/restore methods use `edit` permission (not a separate `restore` permission)
3. Force delete only uses `-all` permission
4. No role checks (`hasRole`) anywhere in policies

See `docs/PROFESSIONAL_USER_RELATIONSHIP.md` for detailed documentation on Professional-User relationships and authorization patterns.

## Important Notes

- The system uses Brazilian Portuguese localization (`lucascudo/laravel-pt-br-localization`)
- CPF validation and Brazilian date/phone formats are handled by `laravellegends/pt-br-validator`
- Page speed optimizations are automatic via middleware (minify HTML/CSS/JS)
- IDE helper files are regenerated automatically on `composer update`
- Session lifetime is configurable via SESSION_LIFETIME in .env (default 120 minutes)
- **Database changes:** Never alter database tables without explicit authorization
- **Authorization:** See "Authorization System" section - NEVER use `hasRole()` for authorization logic, ALWAYS use `can()` with permissions