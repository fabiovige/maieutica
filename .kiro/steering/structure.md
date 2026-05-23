# Project Structure

## Root Layout

```
app/                    # PHP application code
database/               # Migrations, seeders, factories
resources/              # Views, JS, SCSS, lang files
routes/                 # web.php, api.php
public/css/             # Compiled CSS + static CSS files
docs/                   # Project documentation (13 active docs)
docs/specs/             # Pending feature specs
docs/historico/         # Completed plans and historical analyses
tests/                  # Feature and Unit tests
```

## Backend (`app/`)

```
app/
  Models/               # 24 Eloquent models
  Http/
    Controllers/        # 19 web controllers (Blade + DataTables)
    Controllers/Api/    # 8 API controllers (JSON for Vue components)
    Middleware/         # AclMiddleware, SecurityHeaders, etc.
    Requests/           # Form request validation classes
    Resources/          # API resource transformers
  Policies/             # 10 authorization policies
  Observers/            # 6 observers (Checklist, Kid, Professional, Responsible, Role, User)
  Services/
    ChecklistService.php
    OverviewService.php
    Log/                # Custom Monolog handler — writes to `logs` DB table
    Logging/            # 6 domain loggers (one per entity)
  Mail/                 # UserCreatedMail, UserUpdatedMail, UserDeletedMail
  Jobs/                 # SendKidUpdateJob
  Notifications/        # KidUpdateNotification, WelcomeNotification
  Providers/            # Service providers
  Enums/                # ProgressColors
  Util/                 # MyPdf helper
  View/Components/      # Blade class-based components
  helpers.php           # Global helpers (autoloaded via Composer)
```

### Model Hierarchy
- `BaseModel` (extends `Model`) — used by most models; includes `SoftDeletes` + `HasFactory`; audit fields (`created_by`, `updated_by`, `deleted_by`) exist in tables but boot logic is handled by Observers
- `User` extends `Authenticatable` — uses `HasRoles` (Spatie) + `HasApiTokens` (Sanctum); does NOT extend BaseModel
- `Checklist` extends `Model` directly — has its own `SoftDeletes` + `HasFactory`

### Key Relationships
```
User ←M:N→ Professional       (pivot: user_professional)
Professional → Specialty
Kid → Responsible              (belongsTo)
Kid → Checklist                (hasMany) → Competence (M:N pivot with note)
Checklist → Plane              (hasMany) → Competence (M:N)
MedicalRecord → patient        (morphTo: Kid or User)
GeneratedDocument → documentable (morphTo: Kid or User)
```

## Frontend (`resources/`)

```
resources/
  js/
    app.js              # Entry point — registers Vue components globally, jQuery setup
    bootstrap.js        # Axios + Echo setup
    components/         # 9 Vue components (Options API)
      Charts.vue, Checklists.vue, Competences.vue, Dashboard.vue
      Initials.vue, Planes.vue, Resume.vue, Resumekid.vue, TableDescriptions.vue
    composables/        # 8 JS composables (charts, checklists, competences, domains, kids, levels, planes)
    utils/              # photoUtils.js
  sass/
    app.scss            # Main entry — imports config, variables, custom, Bootstrap, buttons
    _config.scss        # CSS custom properties (color tokens, etc.)
    _variables.scss     # SCSS variables
    _custom.scss        # App-wide custom styles
    _buttons.scss       # Standardized button system (must load after Bootstrap)
    _sidebar-layout.scss
    _bootstrap-overrides.scss
    custom.scss         # Compiled to public/css/custom.css (served directly)
  views/
    layouts/
      app.blade.php     # Main layout with sidebar (sidebar styles are inline here)
      guest.blade.php   # Unauthenticated layout
    auth/               # Login, register, password reset (standalone, no app.css)
    components/         # Reusable Blade components
    emails/             # Email templates (layout.blade.php + 3 user lifecycle templates)
    documents/
      layouts/          # pdf-base layout for DomPDF
    [entity]/           # One folder per entity: kids, checklists, users, professionals, etc.
  lang/
    pt-BR/              # All validation and system messages in Brazilian Portuguese
    pt-BR.json          # JS translation strings
  vendor/
    datatable/          # Vendored DataTables assets
    jquery/             # Vendored jQuery
```

## Routes (`routes/`)

- `web.php` — All Blade routes. Pattern: `resource CRUD` + `GET {id}/trash` + `POST {id}/restore` + specialized routes (PDF, chart, overview, datatable)
- `api.php` — JSON endpoints consumed by Vue components mounted in Blade pages

## Database (`database/`)

```
migrations/   # Schema history — always use migrations, never ALTER TABLE directly
seeders/      # DatabaseSeeder orchestrates all seeders
factories/    # Address, Checklist, ChecklistRegister, Kid, Responsible, User
```

## Authorization Pattern

**Always use `can()`, never `hasRole()` for authorization checks:**

```php
// ✅ Correct
$user->can('kid-edit')
@can('kid-edit') ... @endcan
$this->authorize('update', $kid)

// ❌ Wrong — breaks the permission architecture
if ($user->hasRole('admin')) { }
```

Permission naming: `{entity}-{action}[-all]` — e.g., `kid-list`, `kid-list-all`, `medical-record-edit`

`hasRole()` is only for role assignment: `$user->assignRole('profissional')`

## Controller Pattern

Web controllers return Blade views and use DataTables for listings. API controllers return JSON for Vue components. Both layers follow the same resource pattern with extra routes for domain-specific actions.

Standard extra routes beyond CRUD:
- `GET /entity/trash` — soft-deleted records
- `POST /entity/{id}/restore` — restore from trash
- `GET /entity/{id}/pdf` — generate PDF
- `GET /entity/{id}/chart` — chart data
- `*/datatable/index` — server-side DataTable endpoint

## Side Effects Architecture

Business side effects flow through two layers:
1. **Observers** (`app/Observers/`) — triggered by Eloquent events; handle logging and audit
2. **Domain Loggers** (`app/Services/Logging/`) — entity-specific loggers called from Observers; write to the `logs` DB table via the custom Monolog handler in `app/Services/Log/`
