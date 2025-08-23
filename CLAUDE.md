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
- **Business Logic**: Main workflows handled by dedicated Services (`ChecklistService`, `OverviewService`) and Controllers
- **Authentication**: Multi-role system using Spatie Laravel Permission with custom policies
- **Data Structure**: Checklists contain multiple competences grouped by domains and levels, creating assessment hierarchies

### Frontend (Vue 3 + Bootstrap 5)
- **Components**: Reusable Vue components in `resources/js/components/` for charts, checklists, and UI elements
- **Integration**: Vue components mounted within Blade templates, not SPA
- **Charts**: Chart.js integration for assessment visualization and progress tracking
- **Forms**: vee-validate for form validation, SweetAlert2 for user interactions

### Key Relationships
- Kids belong to Responsibles and are associated with Professionals through many-to-many relationships
- Checklists track Kid assessments across multiple Competences
- Competences are organized by Domains and Levels for structured evaluation
- Planes (assessment plans) link Competences for specific evaluation protocols

## Docker Environment

### Services
- **app**: PHP 8.1 FPM with Laravel application
- **nginx**: Web server serving on port 3005 
- **mysql**: MySQL 8.0 with persistent volume

### Development Commands
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

# Run migrations and seeders
docker compose exec app php artisan migrate:fresh --seed

# Clear application caches
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear

# Generate application key
docker compose exec app php artisan key:generate

# Access logs via log-viewer
# Navigate to http://localhost:3005/log-viewer
```

## Database Management

### Migrations
- Database schema managed through Laravel migrations in `database/migrations/`
- Use `php artisan migrate:fresh --seed` for clean database setup with test data
- Critical tables: users, kids, checklists, competences, domains, levels, professionals

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

## Configuration Notes

### Logging
- Application uses daily log rotation (`config/logging.php` set to 'daily')
- Log viewer accessible at `/log-viewer` (middleware: web + auth)
- Custom database logger available for structured logging

### File Uploads
- Kid photos stored in `public/images/kids/`
- User avatars in `public/images/avatars/`
- File validation through form requests

### Environment Variables
- Database: `maieutica` database, user `maieutica`, password `secret`
- Application key required for encryption and sessions
- Log level configurable via `LOG_LEVEL` (default: debug)

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
- to memorize
- to memorize
- to memorize