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

**Security:**
- Laravel Sanctum for API authentication
- Spatie Laravel Permission for role/permission management
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

## Important Notes

- The system uses Brazilian Portuguese localization (`lucascudo/laravel-pt-br-localization`)
- CPF validation and Brazilian date/phone formats are handled by `laravellegends/pt-br-validator`
- Page speed optimizations are automatic via middleware (minify HTML/CSS/JS)
- IDE helper files are regenerated automatically on `composer update`
- Session lifetime is configurable via SESSION_LIFETIME in .env (default 120 minutes)
