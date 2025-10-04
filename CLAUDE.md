# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Maiêutica is a Laravel 9 + Vue 3 application for psychological clinic and associated therapies management. The system is in production at maieuticavaliacom.br.

## Development Commands

### Docker Environment

Start development environment:
```bash
docker-compose up -d
```

Build and rebuild containers:
```bash
docker compose build
docker compose exec app composer install
docker compose exec app php artisan migrate
```

Access application: http://localhost:3005

### PHP/Laravel Commands

Run migrations:
```bash
php artisan migrate
php artisan migrate:fresh --seed
```

Clear all caches:
```bash
composer clear
```

Generate IDE helpers:
```bash
php artisan ide-helper:generate
php artisan ide-helper:meta
php artisan ide-helper:models
```

### Testing

Run all tests:
```bash
vendor/bin/phpunit
```

Run specific test suite:
```bash
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Feature
```

Run single test file:
```bash
vendor/bin/phpunit tests/Unit/Rules/StrongPasswordTest.php
```

### Code Quality

Check code style:
```bash
composer cs-fixer-check
composer cs-fixer-dry-run
```

Fix code style:
```bash
composer cs-fixer-fix
vendor/bin/pint
```

### Frontend Commands

Build assets for development:
```bash
npm run dev
```

Watch for changes:
```bash
npm run watch
```

Build for production:
```bash
npm run prod
```

Lint JavaScript/Vue:
```bash
npm run lint
npm run lint:fix
```

Format code:
```bash
npm run format
npm run format:check
```

## Architecture

### Backend Structure

The application follows Clean Architecture principles with a service-oriented approach:

- **Controllers** (`app/Http/Controllers`): Thin controllers that delegate to services
- **Services** (`app/Services`): Business logic layer
  - `AuthorizationService`: Permission and authorization logic
  - `KidService`, `ProfessionalService`, `ResponsibleService`, `UserService`: Entity-specific services
  - `Security/`: Security-related services (encryption, validation)
  - `Lgpd/`: LGPD compliance services
  - `Backup/`: Backup functionality
  - `Log/`: Logging services
- **Repositories** (`app/Repositories`): Data access layer
- **DTOs** (`app/DTOs`): Data transfer objects for type-safe data passing
- **Value Objects** (`app/ValueObjects`): Immutable domain objects
- **Specifications** (`app/Specifications`): Business rule specifications
- **Policies** (`app/Policies`): Laravel authorization policies
- **Observers** (`app/Observers`): Model event observers
- **Traits** (`app/Traits`): Reusable functionality (e.g., EncryptedAttributes)
- **Helpers** (`app/helpers.php`): Global helper functions (safe_html, safe_js, safe_attribute, etc.)

### Frontend Structure

Vue 3 application with composition API:

- **Components** (`resources/js/components`): Reusable Vue components
- **Pages** (`resources/js/pages`): Page-level components
- **Composables** (`resources/js/composables`): Vue composition functions
- **Utils** (`resources/js/utils`): JavaScript utilities

### Security & LGPD

The system implements security and LGPD (Brazilian Data Protection Law) compliance:

- Encrypted attributes for sensitive data (EncryptedAttributes trait)
- RBAC using Spatie Laravel Permission package
- Security middleware and headers
- Data sanitization helpers (safe_html, safe_js, safe_attribute)
- Audit logging (AuditLogController)

### Key Domain Models

- **User**: System users with role-based permissions
- **Professional**: Psychologists and therapists
- **Responsible**: Legal guardians
- **Kid**: Children/patients
- **Checklist**: Assessment checklists
- **Competence**: Professional competencies

## Coding Standards

### PHP

- Follow PSR-12 coding standard (enforced by PHP-CS-Fixer)
- Apply SOLID principles
- Avoid else statements (use early returns, guard clauses, polymorphism)
- Use type hints and return types
- Never add code comments (code should be self-documenting)
- Use design patterns from https://refactoring.guru/pt-br/design-patterns/php when appropriate

### Vue/JavaScript

- Use Vue 3 Composition API
- Follow ESLint configuration (.eslintrc.js)
- Use Prettier for code formatting
- Avoid reactivity overuse (watch/computed)
- Break UI into reusable components

### Database

- Use migrations for all schema changes
- Use Eloquent ORM with eager loading to avoid N+1 queries
- Normalize schemas appropriately
- Use indexes for performance
- Implement soft deletes where records may need recovery
- Use transactions for operations that must succeed or fail together

## Important Guidelines

### Production System

This is a production system at maieuticavaliacom.br:

- Never make breaking changes without tests and validation
- Prioritize stability and compatibility
- Refactor incrementally and validate in staging first
- Test all optimizations in real scenarios
- Always run tests after changes

### Code Changes

- Always analyze the flow, business rules, and product context before changes
- Think holistically, not in isolation
- Look for patterns and opportunities to eliminate code repetition
- ALWAYS prefer editing existing files to creating new ones
- NEVER proactively create documentation files unless explicitly requested
- Execute all tests after changes or new features
- Always understand package.json and composer.json before making changes

### Testing Strategy

Always execute tests when making changes. Tests are located in:
- Unit tests: `tests/Unit/`
- Feature tests: `tests/Feature/`

Test coverage includes ValueObjects, Rules, Specifications, Traits, and business logic.

## Environment Variables

Key configuration in .env:
- `APP_NAME`, `APP_DESCRIPTION`, `APP_VERSION`
- Database: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`
- Mail configuration
- Session and cache drivers
- Queue connection

## Database

MySQL 8.0 via Docker (port 3306)
- Database: maieutica
- Seeders available in `database/seeders/`

## Additional Services

MailHog for email testing:
- SMTP: localhost:1025
- Web UI: http://localhost:8025
