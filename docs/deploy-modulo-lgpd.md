# Deploy do Módulo LGPD — Instruções para Produção

## Ambiente

- **Servidor:** Hostinger (compartilhado)
- **PHP:** 8.2
- **Deploy:** Webhook GitHub → merge na `main` → deploy automático
- **Repositório:** https://github.com/fabiovige/maieutica (branch `main`)
- **Path no servidor:** `/home/u350247040/domains/maieuticavalia.com.br/public_html/`

---

## Pré-requisitos

O servidor já possui tudo que o módulo precisa:
- ✅ PHP 8.2 com extensões necessárias (mbstring, pdo, json, openssl, tokenizer, xml, ctype, fileinfo, bcmath)
- ✅ MySQL/MariaDB (nd_pdo_mysql)
- ✅ GD e Imagick (para DomPDF)
- ✅ OPcache habilitado
- ✅ Memory limit 1536M (mais que suficiente para geração de PDF)

### Limitação importante

O servidor tem `disableFunctions` que bloqueia `exec`, `shell_exec`, `system`, `passthru`. Isso significa que **não é possível rodar comandos artisan via PHP**. Todos os comandos pós-deploy devem ser executados via:
- **Terminal SSH da Hostinger** (hPanel → Avançado → Terminal SSH)
- **Cron Jobs** (hPanel → Avançado → Cron Jobs)

---

## Passos para Deploy

### 1. Merge da branch para main

```bash
# No seu ambiente local
git checkout main
git pull origin main
git merge feat/modulo-lgpd
git push origin main
```

O webhook do GitHub vai disparar o deploy automático na Hostinger.

### 2. Após o deploy — Executar via SSH

Acesse o terminal SSH da Hostinger e execute:

```bash
cd /home/u350247040/domains/maieuticavalia.com.br/public_html

# Instalar dependências (caso o deploy não faça automaticamente)
php artisan optimize:clear

# Rodar migrations (cria as 5 tabelas do módulo LGPD)
php artisan migrate --force

# Rodar seeder de permissões LGPD
php artisan db:seed --class="App\Modules\Lgpd\Infrastructure\Seeders\LgpdPermissionSeeder" --force

# Limpar caches e reotimizar
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Configurar Cron Job para verificação de prazos

No hPanel da Hostinger (Avançado → Cron Jobs), adicione **um novo** cron job:

```
0 6 * * * /usr/bin/php /home/u350247040/domains/maieuticavalia.com.br/public_html/artisan schedule:run >> /dev/null 2>&1
```

**Explicação:** O Laravel scheduler roda a cada minuto normalmente, mas como você só precisa dos jobs LGPD (06:00 e 07:00), configurar para rodar às 06:00 é suficiente. Porém, o ideal é o scheduler rodar a cada minuto:

```
* * * * * /usr/bin/php /home/u350247040/domains/maieuticavalia.com.br/public_html/artisan schedule:run >> /dev/null 2>&1
```

> **Nota:** Se você já tem o cron do `queue:work`, mantenha-o. O scheduler é separado. Os jobs LGPD usam `QUEUE_CONNECTION=sync`, então executam inline sem precisar do queue worker.

### 4. Atribuir permissões aos usuários

Após o deploy, é necessário atribuir as permissões LGPD aos roles/usuários que devem acessar o módulo. Via Tinker no SSH:

```bash
php artisan tinker
```

```php
// Atribuir todas as permissões LGPD ao role 'admin' (ou o role desejado)
$role = \Spatie\Permission\Models\Role::findByName('admin');
$role->givePermissionTo([
    'lgpd-consent-manage',
    'lgpd-consent-list',
    'lgpd-consent-show',
    'lgpd-access-log-view',
    'lgpd-request-manage',
    'lgpd-request-list',
    'lgpd-request-show',
    'lgpd-report-generate',
    'lgpd-retention-manage',
    'lgpd-retention-list',
]);
```

Ou, se preferir criar um role específico:

```php
$role = \Spatie\Permission\Models\Role::create(['name' => 'lgpd-operador', 'guard_name' => 'web']);
$role->givePermissionTo([
    'lgpd-consent-list',
    'lgpd-consent-show',
    'lgpd-access-log-view',
    'lgpd-request-list',
    'lgpd-request-show',
]);

// Atribuir ao usuário
$user = \App\Models\User::find(1); // seu ID
$user->assignRole('lgpd-operador');
```

---

## Verificação Pós-Deploy

Após completar os passos acima, verifique:

1. **Acesse** `https://maieuticavalia.com.br/lgpd/consents` — deve carregar a tela de consentimentos
2. **Acesse** `https://maieuticavalia.com.br/lgpd/requests` — deve carregar requisições de direitos
3. **Acesse** `https://maieuticavalia.com.br/lgpd/access-logs` — deve carregar logs de acesso
4. **Acesse** `https://maieuticavalia.com.br/lgpd/retention-policies` — deve carregar políticas de retenção
5. **Acesse** `https://maieuticavalia.com.br/lgpd/reports/compliance` — deve carregar formulário de relatório

Se receber **403 Forbidden**, o usuário logado não tem as permissões LGPD. Volte ao passo 4.

---

## Tabelas criadas pela migration

| Tabela | Descrição |
|--------|-----------|
| `lgpd_consent_records` | Registros de consentimento |
| `lgpd_access_logs` | Logs de acesso a prontuários (imutável) |
| `lgpd_data_requests` | Requisições de direitos dos titulares |
| `lgpd_retention_policies` | Políticas de retenção por categoria |
| `lgpd_consent_legal_basis_history` | Histórico de alterações de base legal |

---

## Permissões registradas

| Permissão | Descrição |
|-----------|-----------|
| `lgpd-consent-manage` | Criar e revogar consentimentos |
| `lgpd-consent-list` | Listar consentimentos |
| `lgpd-consent-show` | Ver detalhes de consentimento |
| `lgpd-access-log-view` | Visualizar logs de acesso |
| `lgpd-request-manage` | Criar e processar requisições |
| `lgpd-request-list` | Listar requisições |
| `lgpd-request-show` | Ver detalhes de requisição |
| `lgpd-report-generate` | Gerar relatório PDF |
| `lgpd-retention-manage` | Configurar políticas de retenção |
| `lgpd-retention-list` | Listar políticas de retenção |

---

## Jobs agendados

| Job | Horário | Função |
|-----|---------|--------|
| `CheckDataRequestDeadlinesJob` | 06:00 | Verifica prazos de requisições, alerta ≤5 dias, marca vencidas |
| `CheckRetentionPoliciesJob` | 07:00 | Verifica dados com retenção expirada, notifica operadores |

> Estes jobs só executam se o `schedule:run` estiver configurado no cron.

---

## Rollback (se necessário)

Se precisar reverter as migrations do módulo LGPD:

```bash
cd /home/u350247040/domains/maieuticavalia.com.br/public_html

# Reverter as 5 migrations do módulo (na ordem inversa)
php artisan migrate:rollback --path=app/Modules/Lgpd/Infrastructure/Migrations --force
```

Isso remove as tabelas mas mantém o código. Para remover as permissões:

```bash
php artisan tinker
```

```php
\Spatie\Permission\Models\Permission::where('name', 'like', 'lgpd-%')->delete();
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
```

---

## Nota: correção de permissões de log (somente ambiente de desenvolvimento)

> ⚠️ **Não afeta a Hostinger.** Em produção todos os processos PHP (site + cron) rodam sob o **mesmo usuário** da conta, então os logs sempre nascem com o dono correto e este problema não ocorre. A correção abaixo é exclusiva do **ambiente de desenvolvimento em Docker**.

**Sintoma (apenas no Docker):** `The stream or file ".../storage/logs/laravel-AAAA-MM-DD.log" could not be opened in append mode: Failed to open stream: Permission denied`.

**Causa:** os serviços `queue` e `scheduler` rodavam `artisan` como `root`, criando o log do dia com dono `root` e impedindo o `php-fpm` (`www-data`) de gravar.

**Correção estrutural aplicada (dev):**
- `docker/php/entrypoint.sh` — rebaixa todo comando PHP de aplicação para `www-data` via `gosu` (setup do `app`, `queue:work`, `schedule:run`). Só o *master* do `php-fpm` fica root.
- `Dockerfile` — adicionado o pacote `gosu`.

Por exigirem rebuild da imagem, **após dar pull dessas mudanças em qualquer máquina de dev** execute:

```bash
docker compose build app
docker compose up -d --force-recreate app queue scheduler
```

**Único item desta correção que chega em produção:** `config/logging.php` ganhou `'permission' => 0664` nos canais `daily`/`single`. É inócuo na Hostinger (usuário único) e é aplicado normalmente pelo `php artisan config:cache` do deploy — nenhuma ação extra necessária.

---

## Resumo rápido (checklist)

- [ ] Merge `feat/modulo-lgpd` → `main`
- [ ] Aguardar deploy automático via webhook
- [ ] SSH: `php artisan migrate --force`
- [ ] SSH: `php artisan db:seed --class="App\Modules\Lgpd\Infrastructure\Seeders\LgpdPermissionSeeder" --force`
- [ ] SSH: `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- [ ] Cron: adicionar `schedule:run` (se ainda não tem)
- [ ] Tinker: atribuir permissões LGPD ao role admin
- [ ] Testar acesso às URLs `/lgpd/*`
