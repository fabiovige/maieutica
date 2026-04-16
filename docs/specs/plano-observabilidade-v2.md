# Plano de Observabilidade v2 - Maieutica

> Criado em 23/03/2026 | Laravel 9.x | PHP ^8.0.2
> Substitui: `arcanedev/log-viewer` (instavel) + `sentry/sentry-laravel` (trial expirado)

---

## Motivacao

- **Log-viewer** (`arcanedev/log-viewer`): instavel, trava com arquivos grandes, UI limitada
- **Sentry**: trial expirando, dependencia de servico externo pago
- **Necessidade**: solucao 100% gratuita, self-hosted, robusta para producao

---

## Stack Proposta (100% gratuita e self-hosted)

| Componente | Substitui | Custo | Impacto |
|------------|-----------|-------|---------|
| **Laravel Telescope** (seletivo em prod) | Log-viewer + parte do Sentry | Gratuito | Alto |
| **Health Check `/health`** | Nenhum (novo) | Gratuito | Alto |
| **Slow Query Log** (>500ms) | Nenhum (novo) | Gratuito | Alto |
| **Request Logger Middleware** | Parte do Sentry APM | Gratuito | Medio |
| **Notificacao Slack/Email em erros criticos** | Alertas do Sentry | Gratuito | Alto |
| **Log rotation + pruning automatico** | Manutencao manual | Gratuito | Medio |

---

## Comparativo: Log-viewer vs Telescope

| Funcionalidade | Log-viewer | Telescope |
|----------------|------------|-----------|
| Logs de create/update/delete | Sim (arquivo) | Sim (Model Watcher - banco, pesquisavel) |
| Logs de login/logout | Sim (arquivo) | Sim (Request Watcher + Gate Watcher) |
| Exceptions com stack trace | Sim (arquivo) | Sim (Exception Watcher - contexto completo) |
| Queries SQL executadas | Nao | Sim (Query Watcher - tempo, highlight lentas) |
| Emails enviados (preview) | Nao | Sim (Mail Watcher) |
| Jobs da fila (sucesso/falha) | Nao | Sim (Job Watcher) |
| Cache hits/misses | Nao | Sim (Cache Watcher) |
| Notifications enviadas | Nao | Sim (Notification Watcher) |
| Commands artisan | Nao | Sim (Command Watcher) |
| Gates/Policies avaliadas | Nao | Sim (Gate Watcher) |
| Filtragem e busca | Basica (por data/nivel) | Avancada (por tipo, tag, user, status) |
| Performance | Lento com arquivos grandes | Rapido (banco com indices) |
| Pruning automatico | Nao | Sim (configuravel) |

**Nota:** Os 6 Domain Loggers existentes (Checklist, Kid, User, Professional, Role, MedicalRecord) continuam funcionando - gravam na tabela `logs` do banco, complementar ao Telescope.

---

## Fase 1 - Substituir Log-viewer e Sentry (1-2 dias)

### 1.1 - Instalar Laravel Telescope

**Pacote:** `laravel/telescope` ^4.x (compativel com Laravel 9 + PHP 8.0)

**Instalacao:**
```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

**Configuracao para producao (seletivo):**
```php
// app/Providers/TelescopeServiceProvider.php
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;

Telescope::filter(function (IncomingEntry $entry) {
    if ($this->app->environment('local')) {
        return true; // Em dev, registra tudo
    }

    // Em producao, registra apenas o essencial
    return $entry->isReportableException() ||
           $entry->isFailedRequest() ||
           $entry->isFailedJob() ||
           $entry->isSlowQuery() ||
           $entry->hasMonitoredTag();
});
```

**Acesso:** `/telescope` (protegido por auth + gate)

**Gate de autorizacao:**
```php
// TelescopeServiceProvider.php
protected function gate()
{
    Gate::define('viewTelescope', function ($user) {
        return $user->can('admin-panel'); // Ou permissao especifica
    });
}
```

**Pruning automatico:**
```php
// app/Console/Kernel.php
$schedule->command('telescope:prune --hours=48')->daily(); // Prod: manter 48h
```

**Impacto no servidor:**
- CPU: Minimo (seletivo em prod)
- RAM: ~20MB em prod, ~50MB em dev
- Disco: ~20MB/semana em prod (prunavel)

---

### 1.2 - Health Check Endpoint

**Rota:** `GET /health` (sem autenticacao - para monitoramento externo)

```php
// routes/web.php
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
    $freeSpace = disk_free_space(storage_path());
    $checks['disk'] = $freeSpace > 100 * 1024 * 1024 ? 'ok' : 'warning';
    $checks['disk_free_mb'] = round($freeSpace / 1024 / 1024);

    // Queue (failed jobs)
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
        'version' => config('app.version', 'unknown'),
        'environment' => app()->environment(),
        'timestamp' => now()->toIso8601String(),
        'checks' => $checks,
    ], $allOk ? 200 : 503);
});
```

**Monitoramento externo (gratuito):**
- UptimeRobot (https://uptimerobot.com) - 50 monitores gratis, alerta por email
- Configurar para pingar `GET /health` a cada 5 minutos
- Alerta se status != 200

---

### 1.3 - Notificacao automatica de erros criticos

**Opcao A: Canal Slack no logging**
```php
// config/logging.php - adicionar ao stack de producao
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack-critical'],
    ],
    'slack-critical' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'level' => 'error', // Notifica errors e critical
        'username' => 'Maieutica Bot',
        'emoji' => ':warning:',
    ],
],
```

**Opcao B: Email para admin em exceptions**
```php
// app/Exceptions/Handler.php
public function register()
{
    $this->reportable(function (Throwable $e) {
        // Notificar admin por email em erros criticos (producao)
        if (app()->isProduction() && $this->shouldNotifyAdmin($e)) {
            try {
                Mail::raw(
                    "Erro em producao:\n\n" .
                    get_class($e) . ": " . $e->getMessage() . "\n\n" .
                    "Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n\n" .
                    "URL: " . request()->fullUrl() . "\n" .
                    "User: " . (auth()->id() ?? 'Guest') . "\n" .
                    "IP: " . request()->ip(),
                    function ($message) {
                        $message->to(config('app.admin_email'))
                                ->subject('[Maieutica] Erro em Producao');
                    }
                );
            } catch (\Exception $mailError) {
                // Falha silenciosa - nao quebrar o sistema por causa de notificacao
            }
        }
    });
}

private function shouldNotifyAdmin(Throwable $e): bool
{
    // Ignorar erros comuns que nao precisam de notificacao
    return !($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException ||
             $e instanceof \Illuminate\Session\TokenMismatchException ||
             $e instanceof \Illuminate\Validation\ValidationException);
}
```

---

## Fase 2 - Monitoramento ativo (3-5 dias)

### 2.1 - Slow Query Log

```php
// app/Providers/AppServiceProvider.php -> boot()
if (config('app.log_slow_queries', false)) {
    DB::listen(function ($query) {
        if ($query->time > 500) { // > 500ms
            Log::warning('Slow query detected', [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time_ms' => $query->time,
                'connection' => $query->connectionName,
            ]);
        }
    });
}
```

**Env:** `LOG_SLOW_QUERIES=true`

---

### 2.2 - Request Logger Middleware

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

        // Logar requests lentos (>1s) ou com erro (5xx)
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

**Registro:**
```php
// app/Http/Kernel.php -> $middleware (global)
\App\Http\Middleware\RequestLogger::class,
```

---

### 2.3 - Login failure monitoring

```php
// app/Providers/EventServiceProvider.php
use Illuminate\Auth\Events\Failed;

protected $listen = [
    Failed::class => [
        \App\Listeners\LogFailedLogin::class,
    ],
];
```

```php
// app/Listeners/LogFailedLogin.php
class LogFailedLogin
{
    public function handle(Failed $event)
    {
        Log::warning('Login failed', [
            'email' => $event->credentials['email'] ?? 'unknown',
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
```

---

## Fase 3 - Maturidade (1-2 semanas)

### 3.1 - Pruning automatico agendado

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Telescope: manter 48h em producao
    $schedule->command('telescope:prune --hours=48')->daily()->at('03:00');

    // Limpar logs antigos (> 30 dias)
    $schedule->exec('find ' . storage_path('logs') . ' -name "*.log" -mtime +30 -delete')
             ->daily()->at('03:30');
}
```

### 3.2 - Remover pacotes obsoletos

```bash
# Remover log-viewer
composer remove arcanedev/log-viewer
rm config/log-viewer.php

# Remover Sentry
composer remove sentry/sentry-laravel
rm config/sentry.php

# Remover rota de teste do Sentry
# routes/web.php -> remover Route::get('/sentry-test', ...)

# Limpar Handler.php (remover referencia ao Sentry)
# app/Exceptions/Handler.php -> remover app('sentry')->captureException()

# Limpar .env
# Remover: SENTRY_LARAVEL_DSN, SENTRY_TRACES_SAMPLE_RATE, SENTRY_RELEASE, SENTRY_ENVIRONMENT
```

### 3.3 - Dashboard admin (opcional)

Pagina `/admin/monitoring` com:
- Total de erros nas ultimas 24h (via Telescope API)
- Queries lentas do dia
- Failed jobs pendentes
- Status do health check
- Uso de disco

---

## Cronograma

| Fase | Itens | Prazo | Status |
|------|-------|-------|--------|
| **Fase 1** | Telescope + Health Check + Alertas | 1-2 dias | Pendente |
| **Fase 2** | Slow Query + Request Logger + Login Monitor | 3-5 dias | Pendente |
| **Fase 3** | Pruning + Remover pacotes + Dashboard | 1-2 semanas | Pendente |

---

## Resultado Esperado

Apos implementacao completa:

- **Visibilidade total** de requests, queries, exceptions, jobs, emails (Telescope)
- **Alertas automaticos** em erros criticos (Slack/Email)
- **Health check** monitoravel externamente (UptimeRobot gratuito)
- **Zero dependencia** de servicos externos pagos
- **Manutencao automatica** (pruning de logs e Telescope)
- **Seguranca** (login failure monitoring)
- **Performance** (slow query + request logger)
- **100% self-hosted** e gratuito
