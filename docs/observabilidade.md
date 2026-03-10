# Plano de Observabilidade - Maieutica

> Analise realizada em 10/03/2026 | Laravel 9.x | PHP ^8.0.2

---

## 1. Estado Atual

### O que ja temos

| Componente | Status | Detalhes |
|------------|--------|----------|
| Logs em arquivo | Ativo | Daily rotation, 60 dias de retencao |
| Log Viewer (UI) | Ativo | `arcanedev/log-viewer` em `/log-viewer`, requer auth |
| Audit log (banco) | Ativo | Tabela `logs` com CRUD de models (insert/update/remove/info) |
| Domain Loggers | Ativo | 6 servicos: Checklist, Kid, User, Professional, Role, MedicalRecord |
| Security Headers | Ativo | HSTS (prod), X-Frame-Options, CSP, Permissions-Policy |
| LGPD Compliance | Ativo | Nomes de criancas como iniciais nos logs, campos sensiveis mascarados |
| Debugbar | Instalado | `barryvdh/laravel-debugbar` ^3.14, mas desabilitado (comentado no providers) |

### O que falta (gaps criticos)

| Gap | Risco |
|-----|-------|
| Sem APM/Error Tracking | Erros vao pro arquivo silenciosamente, sem alertas |
| Sem Health Check | Load balancer nao detecta se app esta saudavel |
| Sem monitoramento de queries | Queries lentas ou N+1 passam despercebidas |
| Sem correlacao de requests | Impossivel rastrear uma acao do usuario entre requests |
| Sem monitoramento de filas | Failed jobs existem mas sem alerta ou UI |
| Sem metricas de performance | Nao sabemos tempo de resposta dos endpoints |
| Sem alerta de login falho | Brute force nao detectado |

---

## 2. Opcoes Compativeis com Laravel 9.x

### Opcao A: Laravel Telescope (Recomendada para dev/staging)

**O que e:** Dashboard de debug e monitoramento oficial do Laravel.

**Pacote:** `laravel/telescope` ^4.x (compativel com Laravel 9)

**O que monitora:**
- Requests e responses (tempo, status, headers)
- Queries SQL (com slow query highlight)
- Exceptions com stack trace completo
- Jobs e filas (sucesso, falha, tempo)
- Cache (hits, misses, puts)
- Logs em tempo real
- E-mails enviados (preview)
- Notifications
- Commands artisan
- Schedule
- Gates e policies (autorizacao)
- Dumps (`dump()` capturado)
- Models (create, update, delete)

**Prós:**
- Instalacao simples (`composer require laravel/telescope`)
- UI bonita em `/telescope`
- Nao precisa de servico externo
- Gratuito
- Storage em banco local (tabela `telescope_entries`)
- Pruning automatico (configuravel)

**Contras:**
- Consome recursos do mesmo servidor
- Nao recomendado para producao pesada (pode ser habilitado seletivamente)
- Sem alertas/notificacoes automaticas
- Sem metricas agregadas (SLA, percentis)

**Instalacao:**
```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

**Configuracao para producao (seletivo):**
```php
// app/Providers/TelescopeServiceProvider.php
Telescope::filter(function (IncomingEntry $entry) {
    if ($this->app->environment('local')) {
        return true;
    }
    return $entry->isReportableException() ||
           $entry->isFailedRequest() ||
           $entry->isFailedJob() ||
           $entry->isSlowQuery() ||
           $entry->hasMonitoredTag();
});
```

---

### Opcao B: Sentry (Recomendada para producao)

**O que e:** Plataforma de error tracking e APM.

**Pacote:** `sentry/sentry-laravel` ^3.x (compativel com Laravel 9)

**O que monitora:**
- Exceptions com contexto completo (usuario, request, breadcrumbs)
- Performance (traces de requests, queries, jobs)
- Releases tracking (associa erros a deploys)
- Alertas por e-mail, Slack, webhook
- Dashboards com metricas agregadas

**Prós:**
- Plano gratuito (5K eventos/mes, 10K transacoes performance)
- Minimo impacto no servidor (envia eventos async)
- Alertas automaticos por e-mail/Slack
- Agrupamento inteligente de erros
- Source maps e stack traces ricos
- Release health tracking

**Contras:**
- Dependencia de servico externo (sentry.io)
- Plano gratuito limitado em volume
- Dados sensiveis saem do servidor (configuravel com scrubbing)

**Instalacao:**
```bash
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=https://SEU_DSN@sentry.io/ID
```

**Configuracao `.env`:**
```env
SENTRY_LARAVEL_DSN=https://examplePublicKey@o0.ingest.sentry.io/0
SENTRY_TRACES_SAMPLE_RATE=0.2
```

---

### Opcao C: Health Check Endpoint (Simples e essencial)

**Pacote:** Nenhum necessario (implementacao manual)

**Implementacao proposta:**
```php
// routes/web.php (ou api.php)
Route::get('/health', function () {
    $checks = [];

    // Database
    try {
        DB::connection()->getPdo();
        $checks['database'] = 'ok';
    } catch (\Exception $e) {
        $checks['database'] = 'fail';
    }

    // Cache
    try {
        Cache::put('health_check', true, 10);
        $checks['cache'] = Cache::get('health_check') ? 'ok' : 'fail';
    } catch (\Exception $e) {
        $checks['cache'] = 'fail';
    }

    // Disk
    $checks['disk'] = disk_free_space(storage_path()) > 100 * 1024 * 1024 ? 'ok' : 'warning';

    // Queue (se usar database driver)
    try {
        $failedJobs = DB::table('failed_jobs')->count();
        $checks['queue'] = $failedJobs > 10 ? 'warning' : 'ok';
        $checks['failed_jobs'] = $failedJobs;
    } catch (\Exception $e) {
        $checks['queue'] = 'unknown';
    }

    $allOk = !in_array('fail', $checks);

    return response()->json([
        'status' => $allOk ? 'healthy' : 'unhealthy',
        'version' => 'v2.2.0',
        'timestamp' => now()->toIso8601String(),
        'checks' => $checks,
    ], $allOk ? 200 : 503);
});
```

---

### Opcao D: Slow Query Log (Simples, sem pacote)

**Implementacao proposta:**
```php
// app/Providers/AppServiceProvider.php → boot()
if (!app()->isProduction() || config('app.log_slow_queries')) {
    DB::listen(function ($query) {
        if ($query->time > 500) { // > 500ms
            Log::warning('Slow query detected', [
                'sql' => $query->sql,
                'time_ms' => $query->time,
                'connection' => $query->connectionName,
            ]);
        }
    });
}
```

---

### Opcao E: Request Logging Middleware

**Implementacao proposta:**
```php
// app/Http/Middleware/RequestLogger.php
class RequestLogger
{
    public function handle($request, Closure $next)
    {
        $request->attributes->set('request_start', microtime(true));
        return $next($request);
    }

    public function terminate($request, $response)
    {
        $duration = round((microtime(true) - $request->attributes->get('request_start')) * 1000, 2);

        if ($duration > 1000 || $response->getStatusCode() >= 500) {
            Log::channel('daily')->warning('Slow/Error request', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'status' => $response->getStatusCode(),
                'duration_ms' => $duration,
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);
        }
    }
}
```

---

## 3. Plano de Implementacao (Fases)

### Fase 1 - Essencial (1-2 dias)

| Item | Esforco | Impacto |
|------|---------|---------|
| Instalar Laravel Telescope | Baixo | Alto - visibilidade imediata de queries, requests, jobs |
| Endpoint `/health` | Baixo | Alto - monitoramento basico de saude |
| Habilitar Debugbar em local | Minimo | Medio - debug de queries N+1 |
| Slow query log (>500ms) | Baixo | Alto - detectar gargalos |

**Resultado:** Visibilidade completa em dev/staging, health check em producao.

### Fase 2 - Producao (1 semana)

| Item | Esforco | Impacto |
|------|---------|---------|
| Instalar Sentry (error tracking) | Baixo | Alto - alertas de erros em tempo real |
| Request logging middleware | Baixo | Medio - detectar endpoints lentos |
| Configurar Telescope seletivo em prod | Medio | Alto - monitorar sem impacto |
| Configurar canal Slack para logs criticos | Baixo | Alto - alertas imediatos |

**Resultado:** Erros monitorados com alerta, performance rastreada.

### Fase 3 - Maturidade (2-3 semanas)

| Item | Esforco | Impacto |
|------|---------|---------|
| Dashboard de metricas customizado | Alto | Alto - visao de negocio |
| Monitoramento de filas com UI | Medio | Medio - jobs health |
| Login failure rate limiting | Medio | Alto - seguranca |
| Agendamento de pruning (Telescope + logs) | Baixo | Medio - manutencao |
| Metricas de negocio (tempo de avaliacao, PDFs gerados) | Medio | Alto - KPIs |

**Resultado:** Observabilidade madura com metricas de negocio.

---

## 4. Recomendacao Final

Para o cenario atual do Maieutica (Laravel 9, producao, equipe pequena):

### Implementar agora:
1. **Laravel Telescope** - Visibilidade total em dev, seletivo em prod
2. **Health Check** `/health` - Essencial para qualquer sistema em producao
3. **Slow Query Log** - Zero custo, alto valor

### Implementar em breve:
4. **Sentry** (plano gratuito) - Error tracking profissional com alertas
5. **Request Logger** - Detectar endpoints lentos

### Manter:
6. **Log Viewer** (`arcanedev/log-viewer`) - Continua util para analise de logs em arquivo
7. **Domain Loggers** (6 servicos) - Excelente base de audit trail

### Nao implementar agora:
- New Relic / DataDog - Custo alto para o momento
- ELK Stack (Elasticsearch + Logstash + Kibana) - Complexidade desnecessaria
- Prometheus + Grafana - Overengineering para o tamanho atual

---

## 5. Compatibilidade de Pacotes

| Pacote | Versao | Laravel 9 | PHP 8.0 | Custo |
|--------|--------|-----------|---------|-------|
| `laravel/telescope` | ^4.x | Sim | Sim | Gratuito |
| `sentry/sentry-laravel` | ^3.x | Sim | Sim | Gratuito (5K eventos/mes) |
| `spatie/laravel-health` | ^1.x | Sim | Sim | Gratuito |
| `laravel/horizon` | ^5.x | Sim | Sim | Gratuito (requer Redis) |
| `arcanedev/log-viewer` | Atual | Sim | Sim | Gratuito (ja instalado) |

---

## 6. Estimativa de Impacto no Servidor

| Componente | CPU | RAM | Disco |
|------------|-----|-----|-------|
| Telescope (dev) | Baixo | ~50MB | ~100MB/semana (prunavel) |
| Telescope (prod seletivo) | Minimo | ~20MB | ~20MB/semana |
| Sentry | Minimo | ~10MB | Nenhum (externo) |
| Health Check | Desprezivel | Desprezivel | Nenhum |
| Slow Query Log | Desprezivel | Desprezivel | Minimo |
| Request Logger | Desprezivel | Desprezivel | Minimo |

---

## Resumo

O Maieutica ja tem uma **base solida** (6 domain loggers, audit trail, LGPD compliance, security headers). O proximo passo e adicionar **visibilidade em tempo real** (Telescope) e **alertas automaticos** (Sentry), sem complexidade desnecessaria. A Fase 1 pode ser implementada em 1-2 dias com impacto imediato na capacidade de diagnostico.
