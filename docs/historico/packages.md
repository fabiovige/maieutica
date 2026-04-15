# Pacotes — Maiêutica

> Atualizado em: 2026-04-13

## Backend (Composer — `require`)

### Core Laravel

| Pacote | Versão | Propósito |
|--------|--------|-----------|
| `laravel/framework` | ^9.0 | Framework principal |
| `laravel/sanctum` | ^2.14 | API token authentication |
| `laravel/tinker` | ^2.7 | REPL para debug |
| `laravel/ui` | ^3.4 | Scaffolding de autenticação |
| `laravel/socialite` | ^5.16 | OAuth (login social) |

### Funcionalidades

| Pacote | Versão | Propósito |
|--------|--------|-----------|
| `spatie/laravel-permission` | ^6.9 | Autorização baseada em permissões (CRÍTICO) |
| `yajra/laravel-datatables-oracle` | * | DataTables server-side |
| `barryvdh/laravel-dompdf` | * | Geração de PDF (principal) |
| `elibyy/tcpdf-laravel` | ^9.1 | PDF alternativo (TCPDF) |
| `laracasts/flash` | ^3.2.1 | Flash messages (toasts) |
| `laravellegends/pt-br-validator` | ^9.1 | Validação BR (CPF, datas, telefones) |
| `arcanedev/log-viewer` | * | Viewer de logs no browser (`/log-viewer`) |
| `renatomarinho/laravel-page-speed` | ^2.1 | Auto minify de HTML/CSS/JS |

### Segurança e Monitoramento

| Pacote | Versão | Propósito |
|--------|--------|-----------|
| `biscolab/laravel-recaptcha` | ^6.1 | reCAPTCHA v2 |
| `josiasmontag/laravel-recaptchav3` | ^1.0 | reCAPTCHA v3 |

### HTTP e Infraestrutura

| Pacote | Versão | Propósito |
|--------|--------|-----------|
| `guzzlehttp/guzzle` | ^7.2 | HTTP client (requisições externas) |
| `fruitcake/laravel-cors` | ^3.0 | CORS middleware |
| `datatables.net/datatables.net` | dev-master | DataTables core (JS/backend) |
| `datatables.net/datatables.net-dt` | dev-master | DataTables styling |

---

## Frontend (NPM — `dependencies`)

### Frameworks e UI

| Pacote | Versão | Propósito |
|--------|--------|-----------|
| `vue` | ^3.5.13 | Framework frontend (Options API) |
| `bootstrap` | ^5.3.3 | CSS framework |
| `bootstrap-icons` | ^1.11.3 | Ícones Bootstrap |
| `chart.js` | ^3.9.1 | Gráficos radar (avaliação Denver) |
| `vue-chart-3` | ^3.1.8 | Wrapper Vue para Chart.js |
| `sweetalert2` | ^11.4.17 | Modais/alerts |
| `vue-sweetalert2` | 5.0.5 | Wrapper Vue para SweetAlert2 |

### Formulários e Inputs

| Pacote | Versão | Propósito |
|--------|--------|-----------|
| `vee-validate` | ^4.6.7 | Validação de formulários Vue |
| `jquery-mask-plugin` | ^1.14.16 | Máscaras de input (CPF, telefone, CEP) |
| `vue-jquery-mask` | ^2.0.0 | Wrapper Vue para jQuery Mask |
| `vue3-select2-component` | ^0.1.7 | Select2 para Vue 3 |

### Utilitários

| Pacote | Versão | Propósito |
|--------|--------|-----------|
| `axios` | ^1.7.9 | HTTP client |
| `jquery` | ^3.6.0 | jQuery (para DataTables e plugins) |
| `jquery-ui` | ^1.13.1 | jQuery UI (sortable, draggable) |
| `vue3-loading-overlay` | ^0.0.0 | Loading overlay Vue |

---

## Desenvolvimento (NPM `devDependencies` + Composer `require-dev`)

### PHP Dev

| Pacote | Versão | Propósito |
|--------|--------|-----------|
| `laravel/pint` | ^1.20 | Formatter de código PHP |
| `friendsofphp/php-cs-fixer` | ^3.68 | PHP CS Fixer alternativo |
| `phpunit/phpunit` | ^9.5.10 | Testes unitários e feature |
| `fakerphp/faker` | ^1.9.1 | Dados fictícios para testes/seeds |
| `mockery/mockery` | ^1.4.4 | Mocks para testes |
| `barryvdh/laravel-ide-helper` | ^2.15 | IDE helpers (auto-gerado) |
| `barryvdh/laravel-debugbar` | ^3.14 | Debug bar (`APP_DEBUG=true`) |
| `spatie/laravel-ignition` | ^1.0 | Error page melhorada |
| `nunomaduro/collision` | ^6.1 | CLI error handler |
| `laravel/sail` | ^1.0.1 | Docker environment |
| `lucascudo/laravel-pt-br-localization` | ^1.2 | Tradução PT-BR |

### Build e Assets

| Pacote | Versão | Propósito |
|--------|--------|-----------|
| `laravel-mix` | ^6.0.49 | Build com Webpack |
| `sass` | ^1.85.0 | Compilador SCSS |
| `sass-loader` | ^11.0.1 | Webpack loader para SCSS |
| `vue-loader` | ^16.8.3 | Webpack loader para Vue SFC |
| `postcss` | ^8.1.14 | PostCSS processor |
| `autoprefixer` | 10.4.5 | Auto vendor prefixes |
| `resolve-url-loader` | ^5.0.0 | Resolve URLs em SCSS imports |
| `webpack-dev-server` | ^5.1.0 | Dev server com HMR |
| `@popperjs/core` | ^2.10.2 | Popper.js (tooltips/dropdowns Bootstrap) |
| `lodash` | ^4.17.19 | Utility library |
