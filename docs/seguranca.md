# Seguranca — Middleware, Headers e Protecoes

> Referencia de todas as camadas de seguranca do sistema Maieutica.

---

## Camadas de Seguranca

```
Request
  -> SecurityHeaders (headers de seguranca)
  -> EncryptCookies (cookies criptografados)
  -> VerifyCsrfToken (protecao CSRF)
  -> Authenticate (autenticacao)
  -> AclMiddleware (verificacao de permissao/allow)
  -> Controller (logica de negocio)
  -> Policy (autorizacao granular)
```

---

## Middleware Customizados

### AclMiddleware (`app/Http/Middleware/AclMiddleware.php`)

**Funcao:** Verifica se o usuario tem flag `allow = true`. Auto-logout se desabilitado.

**Comportamento:**
1. Verifica `$user->allow` — se `false`, faz logout com mensagem de erro
2. SuperAdmin bypassa todas as verificacoes
3. Logica de verificacao de permissoes por rota (parcialmente implementada)

**Status:** Logica de verificacao por rota incompleta — contem `dd()` de debug

**Registro:** `app/Http/Kernel.php` no grupo `web`

### SecurityHeaders (`app/Http/Middleware/SecurityHeaders.php`)

**Headers adicionados a TODAS as respostas:**

| Header | Valor | Proposito |
|--------|-------|-----------|
| X-Content-Type-Options | `nosniff` | Previne MIME sniffing |
| X-Frame-Options | `SAMEORIGIN` | Previne clickjacking |
| X-XSS-Protection | `1; mode=block` | Filtro XSS do browser |
| Referrer-Policy | `strict-origin-when-cross-origin` | Controla referrer |
| Permissions-Policy | `geolocation=(), microphone=(), camera=()` | Desabilita APIs sensíveis |
| Strict-Transport-Security | `max-age=31536000; includeSubDomains` | **Somente producao** — forca HTTPS |

**Registro:** `app/Http/Kernel.php` no grupo `web`

---

## Middleware Padrao do Laravel

| Middleware | Arquivo | Funcao |
|------------|---------|--------|
| Authenticate | `Authenticate.php` | Redireciona para login se nao autenticado |
| EncryptCookies | `EncryptCookies.php` | Criptografa cookies automaticamente |
| VerifyCsrfToken | `VerifyCsrfToken.php` | Protecao contra CSRF em POST/PUT/DELETE |
| PreventRequestsDuringMaintenance | `PreventRequestsDuringMaintenance.php` | Bloqueia requests em modo manutencao |
| RedirectIfAuthenticated | `RedirectIfAuthenticated.php` | Redireciona usuarios logados (guest middleware) |
| TrimStrings | `TrimStrings.php` | Remove espacos desnecessarios |
| TrustHosts | `TrustHosts.php` | Validacao de hosts confiáveis |
| TrustProxies | `TrustProxies.php` | Confianca em proxies (load balancer) |

---

## Autenticacao

### Mecanismos

| Tipo | Guard | Uso |
|------|-------|-----|
| Web | `session` | Login via formulario, cookies de sessao |
| API | `sanctum` | Tokens para componentes Vue (definido, pouco usado) |

### Login

- **Rota:** `POST /login`
- **Controller:** `Auth\LoginController`
- **Protecao:** reCAPTCHA v2 (configurado em `config/recaptcha.php`)
- **Rate limiting:** Padrao do Laravel (5 tentativas / minuto)
- **Layout:** `auth/login.blade.php` (standalone — nao carrega app.css)

### Reset de Senha

- **Fluxo:** Solicitar link -> Email com token -> Formulario reset -> Nova senha
- **Expiracao:** Configurada em `config/auth.php` (padrao: 60 min)

---

## Autorizacao

**Referencia completa:** Use `/auth` para detalhes de permissoes e policies.

**Resumo:**
- **Spatie Permission** — roles + permissions
- **10 Policies** — autorizacao granular por entidade
- **Padrao:** `can('entity-action')` — NUNCA `hasRole()`
- **Scope:** Profissionais veem apenas seus pacientes

---

## Protecoes Ativas

### CSRF
- Token em todos os formularios (`@csrf`)
- Meta tag `csrf-token` no layout para requisicoes AJAX
- Componentes Vue usam o token via `axios` (configurado em `bootstrap.js`)

### XSS
- Blade: `{{ }}` escapa automaticamente (usar `{!! !!}` apenas quando necessario)
- Header `X-XSS-Protection` como camada adicional
- CSP nao implementado (Content-Security-Policy)

### SQL Injection
- Eloquent ORM previne por padrao (bindings parametrizados)
- Query Builder com bindings: `DB::select('...', [$param])`
- **Cuidado:** Nunca usar concatenacao direta em queries

### Clickjacking
- Header `X-Frame-Options: SAMEORIGIN`

### HTTPS
- HSTS ativo em producao (1 ano, incluindo subdomains)
- Desenvolvimento local sem HTTPS (ok para dev)

---

## Configuracoes de Seguranca

### reCAPTCHA (`config/recaptcha.php`)
- **Versao:** v2 (checkbox)
- **Idioma:** pt-BR
- **Onde:** Formulario de login
- **Chaves:** Via `.env` (`RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY`)
- **Nota:** `curl_verify: false` — desabilitado para dev, verificar em prod

### Sessao (`config/session.php`)
- **Driver:** Configurado via `.env` (`SESSION_DRIVER`)
- **Lifetime:** Configurado via `.env` (`SESSION_LIFETIME`)
- **Secure cookie:** Apenas em HTTPS (padrao do Laravel)
- **HttpOnly:** Sim (padrao)

### CORS (`config/cors.php`)
- Configurado para permitir requisicoes da mesma origem
- API routes incluidas no padrao

---

## Health Check Publico

**Rota:** `GET /health` (sem autenticacao)

**Verificacoes:**
1. **Database** — conexao ativa
2. **Cache** — leitura/escrita funcional
3. **Disk** — espaco livre > 100MB
4. **Queue** — failed_jobs < 10

**Resposta:**
```json
{
  "status": "healthy|unhealthy",
  "version": "1.0.18",
  "environment": "local|production",
  "timestamp": "ISO8601",
  "checks": {
    "database": "ok|fail",
    "cache": "ok|fail",
    "disk": "ok|warning",
    "disk_free_mb": 1234,
    "queue": "ok|warning",
    "failed_jobs": 0
  }
}
```

**HTTP:** 200 se healthy, 503 se unhealthy

---

## Pontos de Atencao

### Implementados
- [x] Headers de seguranca em todas as respostas
- [x] CSRF em formularios e AJAX
- [x] Autenticacao session-based
- [x] Autorizacao por permissions + policies
- [x] Soft deletes (dados nunca sao perdidos)
- [x] Auditoria via Observers + Domain Loggers
- [x] reCAPTCHA no login
- [x] Health check endpoint

### Pendentes / A Melhorar
- [ ] AclMiddleware — logica de verificacao por rota incompleta
- [ ] CSP (Content-Security-Policy) — nao implementado
- [ ] Rate limiting em rotas API
- [ ] Auditoria de login falhos (spec observabilidade v2)
- [ ] Verificacao SSL do reCAPTCHA em producao
- [ ] Revisao de permissoes API (rotas sem middleware de auth aplicado)
