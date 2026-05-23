# Tech Stack

## Backend
- **Framework:** Laravel 9.52 (PHP ^8.0.2)
- **Auth:** Laravel Sanctum (API tokens) + Spatie Laravel Permission ^6.9 (permission-based, 93 permissions, 10 roles)
- **Database:** MySQL / MariaDB
- **ORM:** Eloquent with SoftDeletes on most models
- **PDF:** barryvdh/laravel-dompdf + elibyy/tcpdf-laravel
- **Tables:** yajra/laravel-datatables-oracle (server-side DataTables)
- **Validation:** laravellegends/pt-br-validator (Brazilian formats: CPF, CNPJ, phone)
- **Flash messages:** laracasts/flash
- **Log viewer:** arcanedev/log-viewer (route: `/log-viewer`)
- **reCAPTCHA:** biscolab/laravel-recaptcha + josiasmontag/laravel-recaptchav3
- **Social login:** laravel/socialite
- **CORS:** fruitcake/laravel-cors
- **Page speed:** renatomarinho/laravel-page-speed

## Frontend
- **Framework:** Vue 3.5 — **Options API only** (not Composition API)
- **CSS:** Bootstrap 5.3 + Bootstrap Icons 1.11
- **Charts:** Chart.js 3.9 + vue-chart-3
- **Form validation:** vee-validate ^4
- **Selects:** vue3-select2-component + Select2
- **Alerts:** SweetAlert2 + vue-sweetalert2
- **Masks:** jquery-mask-plugin + vue-jquery-mask
- **Date picker:** jQuery UI Datepicker (pt-BR locale)
- **Architecture:** Not a SPA — Vue components are mounted as reactive islands inside Blade templates. jQuery coexists with Vue.

## Build System
- **Bundler:** Laravel Mix 6.x (Webpack)
- **CSS preprocessor:** Sass (SCSS)
- **Webpack alias:** `@` → `resources/js`

## Common Commands

```bash
# Assets
npm run dev          # Compile assets once
npm run watch        # Compile assets with watch
npm run prod         # Production build (minified)

# Laravel
php artisan serve    # Local dev server
composer clear       # Clear all caches (route, view, config, compiled + dumpautoload)

# Database
php artisan db:seed              # Seed database (normal use)
composer fresh                   # migrate:fresh --seed — ONLY when explicitly requested; destroys all data

# Testing
php artisan test                             # Run all tests
php artisan test --filter=TestName           # Run specific test
php artisan test tests/Unit/Models/          # Run a directory

# Code style
./vendor/bin/pint                # Fix code style (Laravel Pint)
./vendor/bin/pint --test         # Dry-run (report only, no changes)
```

## SCSS Load Order
1. `_config.scss` → `_variables.scss` → `_custom.scss`
2. Bootstrap
3. `_buttons.scss` (must come after Bootstrap)

## CSS Load Order in HTML
1. `app.css` (compiled by Mix)
2. `custom.css` (served directly from `public/css/`)
3. `typography.css` (served directly from `public/css/`)

## Key Notes
- **Sidebar styles** are inline in `app.blade.php`, not in compiled SCSS.
- **Login page** (`auth/login.blade.php`) is standalone — does not load `app.css` or `custom.css`.
- **PDF templates** extend `documents.layouts.pdf-base` and must use `DejaVu Sans` font.
- **Tests use the real database** — `DB_DATABASE :memory:` is commented out in `phpunit.xml`. Tests run against the DB configured in `.env`.
- **Queue:** `QUEUE_CONNECTION=sync` by default. There are 7 records in `failed_jobs` — investigate before enabling async workers.
- **Global helpers** auto-loaded via Composer (`app/helpers.php`): `label_case()`, `get_progress_color()`, `get_progress_gradient()`, `get_chart_gradient()`.
