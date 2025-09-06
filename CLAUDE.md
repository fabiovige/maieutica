# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Communication Language
**ALWAYS COMMUNICATE IN BRAZILIAN PORTUGUESE** - All interactions with the user must be in Portuguese from Brazil.

## Project Overview

Maiêutica is a web platform for psychological clinics specializing in cognitive assessment of children. It's a Laravel 9 application with Vue 3 frontend, running in Docker containers for development and production.

**Core Domain**: Child psychological evaluation, progress tracking, professional management, and detailed reporting.

## Architecture

### Backend (Laravel 9)
- **Models**: Core entities are `Kid`, `Checklist`, `Competence`, `Domain`, `Level`, `Professional`, `Responsible`, and `User`
- **Business Logic**: Main workflows handled by dedicated Services (`ChecklistService`, `OverviewService`, `KidService`, `ProfessionalService`) and Controllers
- **Authentication**: Multi-role system using Spatie Laravel Permission with custom policies
- **Data Structure**: Checklists contain multiple competences grouped by domains and levels, creating assessment hierarchies
- **Form Validation**: Dedicated Form Request classes for input validation (e.g., `KidRequest`, `ChecklistRequest`)

### Frontend (Vue 3 + Bootstrap 5)
- **Components**: Reusable Vue components in `resources/js/components/` for charts, checklists, and UI elements
- **Integration**: Vue components mounted within Blade templates, not SPA
- **Charts**: Chart.js integration via vue-chart-3 for assessment visualization and progress tracking
- **Forms**: vee-validate for form validation, SweetAlert2 for user interactions
- **UI Libraries**: Bootstrap 5, Bootstrap Icons, jQuery UI for datepicker, Select2 for advanced dropdowns

### Key Relationships
- Kids belong to Responsibles and are associated with Professionals through many-to-many relationships
- Checklists track Kid assessments across multiple Competences
- Competences are organized by Domains and Levels for structured evaluation
- Planes (assessment plans) link Competences for specific evaluation protocols

## Essential Commands

### Docker Development Commands
```bash
# Start containers
docker compose up -d

# Install PHP dependencies
docker compose exec app composer install

# Install Node dependencies  
docker compose exec app npm ci

# Build assets for development
docker compose exec app npm run dev

# Build assets for production
docker compose exec app npm run production

# Watch assets for changes
docker compose exec app npm run watch

# Run migrations and seeders (fresh database with test data)
docker compose exec app php artisan migrate:fresh --seed

# Clear all caches
docker compose exec app composer clear

# Access logs via log-viewer
# Navigate to http://localhost:3005/log-viewer
```

### Code Quality & Linting Commands
```bash
# PHP Code Style (using PHP CS Fixer)
docker compose exec app composer cs-fixer-check     # Check for style issues with diff
docker compose exec app composer cs-fixer-fix       # Auto-fix style issues
docker compose exec app composer code-style         # Alias for cs-fixer-fix

# JavaScript/Vue Linting
docker compose exec app npm run lint                # Check for JS/Vue issues
docker compose exec app npm run lint:fix            # Auto-fix JS/Vue issues

# JavaScript/Vue Formatting (Prettier)
docker compose exec app npm run format              # Format JS/Vue files
docker compose exec app npm run format:check        # Check formatting without changes
```

### Testing Commands
```bash
# PHPUnit tests (if tests directory exists)
docker compose exec app php artisan test
docker compose exec app ./vendor/bin/phpunit

# Run specific test file
docker compose exec app ./vendor/bin/phpunit tests/Feature/ExampleTest.php

# Run tests with coverage
docker compose exec app ./vendor/bin/phpunit --coverage-html coverage
```

## Database Management

### Migrations
- Database schema managed through Laravel migrations in `database/migrations/`
- Use `php artisan migrate:fresh --seed` for clean database setup with test data
- Critical tables: users, kids, checklists, competences, domains, levels, professionals, responsibles

### Seeders
- `DatabaseSeeder` orchestrates all seeders with realistic test data
- Includes roles/permissions, users, kids, assessment data, and professional profiles
- Run seeders after any major schema changes

## Key Features

### Assessment System
- Checklists group multiple competences for structured evaluation
- Competences belong to domains (cognitive areas) and levels (difficulty/age appropriateness) 
- Progress tracking through percentage completion and percentile rankings
- Automatic plane generation based on assessment results

### User Management
- Role-based access: Admin, Professional, Responsible
- Multi-tenancy through user associations with kids and checklists
- Observer pattern for user lifecycle events (creation, updates, deletions)

### Reporting & Analytics
- PDF generation for assessment reports using TCPDF
- Interactive charts showing progress over time
- Dashboard with overview statistics and recent activity
- DataTables integration for advanced data tables with sorting/filtering

## Configuration Notes

### Container Services
- **App (Laravel)**: PHP 8.1 FPM running on internal network
- **Nginx**: Web server on port 3005
- **MySQL**: Database on port 3306 (database: `maieutica`, user: `maieutica`, password: `secret`)
- **MailHog**: Email testing on port 8025 (Web UI) and 1025 (SMTP)

### File Storage
- Kid photos: `public/images/kids/`
- User avatars: `public/images/avatars/`
- Logs: Daily rotation in `storage/logs/`

### Code Style Configuration
- **PHP**: PSR-12 standard with custom rules in `.php-cs-fixer.php`
- **JavaScript/Vue**: ESLint with Vue 3 rules in `.eslintrc.js`
- **Formatting**: Prettier configuration in `.prettierrc.json`

## Production Considerations

**CRITICAL**: This system is live in production at maieuticavaliacom.br
- Always prioritize stability over new features
- Test changes thoroughly before deployment
- Use incremental refactoring approach
- Never break existing functionality without validation
- Document significant changes

## Code Quality Guidelines

### Core Development Principles
- **NEVER ADD COMMENTS**: Code should be self-explanatory through clear naming and structure
- **UX/UI FIRST**: Always prioritize user experience - intuitive interfaces, responsive design, and smooth interactions
- **LEAN CODING**: Avoid unnecessary code - write only what's needed, refactor when appropriate, keep it simple
- **FOCUS ON SOLUTION**: Do exactly what is asked, nothing more, nothing less
- **NO UNNECESSARY FEATURES**: Never add visual feedback, tooltips, or interface changes unless explicitly requested
- **MINIMAL APPROACH**: Implement only the specific functionality requested without additional "improvements"

### CRITICAL DEVELOPMENT CONSTRAINTS
- **NEVER MODIFY LAYOUT STRUCTURE**: The current breadcrumb system in `layouts/app.blade.php` works perfectly and must NOT be changed
- **PRESERVE EXISTING PATTERNS**: Follow existing patterns for breadcrumbs using `@section('breadcrumb-items')` and `@section('actions')`
- **TEST SMALL CHANGES**: When making UI improvements, test incrementally and never break existing functionality
- **ROLLBACK ON FAILURE**: If a change breaks anything, immediately acknowledge failure and revert changes
- **STABILITY OVER INNOVATION**: This is production code - stability is more important than creating new components

### Backend (Laravel)
- Use Eloquent efficiently with eager loading to prevent N+1 queries
- Implement business logic in Service classes, keep Controllers thin
- Use Form Requests for input validation
- Follow repository pattern for complex queries
- Implement proper authorization through Policies

### Frontend (Vue/JavaScript)
- Break UI into reusable Vue components
- Use computed properties and watchers efficiently
- Validate forms using vee-validate
- Optimize for performance and accessibility
- Maintain consistent styling with Bootstrap 5
- Focus on smooth animations and responsive interactions

### Database
- Use migrations for all schema changes
- Implement proper indexing for performance
- Use soft deletes for recoverable records
- Normalize schemas to avoid redundancy