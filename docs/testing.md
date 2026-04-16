# Testes e Debugging — Maiêutica

## Estrutura de Testes (20 testes)

```
tests/
├── Feature/
│   ├── Api/ChecklistApiTest.php
│   ├── Auth/AuthenticationTest.php
│   └── Controllers/
│       ├── ChecklistControllerTest.php
│       ├── KidsControllerTest.php
│       └── MedicalRecordsControllerTest.php
└── Unit/
    ├── Models/ (6 testes)
    │   ├── ChecklistModelTest.php
    │   ├── GeneratedDocumentModelTest.php
    │   ├── KidModelTest.php
    │   ├── MedicalRecordModelTest.php
    │   ├── PlaneModelTest.php
    │   └── UserModelTest.php
    └── Policies/ (7 testes)
        ├── ChecklistPolicyTest.php
        ├── GeneratedDocumentPolicyTest.php
        ├── KidPolicyTest.php
        ├── MedicalRecordPolicyTest.php
        ├── PlanePolicyTest.php
        ├── ProfessionalPolicyTest.php
        └── RolePolicyTest.php
```

## Comandos de Teste

```bash
# Todos os testes
php artisan test

# Arquivo específico
php artisan test tests/Unit/ExampleTest.php

# Método específico
php artisan test --filter test_method_name

# Suite específica
./vendor/bin/phpunit --testsuite Unit
```

## Qualidade de Código

```bash
./vendor/bin/pint           # Laravel Pint formatter
./vendor/bin/php-cs-fixer fix  # PHP CS Fixer
```

## Debugging

```bash
# Logs no browser (requer auth)
/log-viewer

# Logs no terminal
tail -f storage/logs/laravel.log

# Debug bar: APP_DEBUG=true no .env
```

## Banco de Dados (Desenvolvimento)

```bash
# Popular banco (NUNCA apagar dados sem pedir explicitamente)
php artisan db:seed

# Fresh + seed (SOMENTE se usuário pedir explicitamente)
composer fresh   # equivale a: php artisan migrate:fresh --seed
```

> **IMPORTANTE:** Nunca executar `migrate:fresh` ou qualquer comando que apague dados do banco local de desenvolvimento sem confirmação explícita do usuário.

## Limpeza de Cache

```bash
composer clear   # limpa: cache, route, view, config, clear-compiled
```
