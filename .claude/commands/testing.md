Leia `docs/testing.md` na íntegra. Use-o para responder perguntas sobre testes e debugging.

## Estrutura de Testes (21 arquivos)

```
tests/
├── Feature/
│   ├── Api/ChecklistApiTest.php
│   ├── Auth/AuthenticationTest.php
│   └── Controllers/ (Checklist, Kids, MedicalRecords)
└── Unit/
    ├── Models/ (Checklist, GeneratedDocument, Kid, MedicalRecord, Plane, User)
    ├── Policies/ (Checklist, GeneratedDocument, Kid, MedicalRecord, Plane, Professional, Role)
    └── Services/ (ChecklistService, OverviewService)
```

## Comandos

```bash
php artisan test                              # Todos
php artisan test --filter=NomeDoTeste         # Método específico
php artisan test tests/Unit/Models/           # Diretório
./vendor/bin/phpunit --testsuite Unit         # Suite Unit
./vendor/bin/phpunit --testsuite Feature      # Suite Feature
```

## Padrões de Teste

- **Models:** Testar constantes, accessors, relationships, scopes
- **Policies:** Testar cada permission para admin, profissional, e usuário sem permissão
- **Controllers (Feature):** Testar rotas com autenticação, autorização, e dados válidos/inválidos
- **Services:** Testar lógica de negócio isolada

## Debugging

```bash
# Logs no browser (requer auth)
/log-viewer

# Logs no terminal
tail -f storage/logs/laravel.log

# Debug bar: APP_DEBUG=true no .env (nunca em produção)
```

## Regras

- **NUNCA** rodar `migrate:fresh` ou esvaziar banco sem solicitação explícita
- Usar `php artisan db:seed` para popular dados de teste
- `composer fresh` = `migrate:fresh --seed` (SOMENTE se pedido explicitamente)
- Limpeza de cache: `composer clear` (dumpautoload + cache + route + view + config)

## Lint

```bash
./vendor/bin/pint           # Fix style (Laravel Pint)
./vendor/bin/pint --test    # Dry-run (apenas reportar)
```
