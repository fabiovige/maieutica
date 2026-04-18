---
description: Middleware de segurança, headers, CSRF, XSS, health check, proteções do sistema
---

Leia `docs/seguranca.md` na íntegra. Use-o para responder perguntas sobre segurança, middleware, headers, proteções e configurações de segurança.

## Camadas de Segurança

```
Request → SecurityHeaders → EncryptCookies → VerifyCsrfToken → Authenticate → AclMiddleware → Controller → Policy
```

## Middleware Customizados

**AclMiddleware** (`app/Http/Middleware/AclMiddleware.php`):
- Verifica `$user->allow` — logout se `false`
- SuperAdmin bypassa tudo
- **Status:** Lógica de verificação por rota incompleta (contém `dd()`)

**SecurityHeaders** (`app/Http/Middleware/SecurityHeaders.php`):
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: geolocation=(), microphone=(), camera=()`
- HSTS somente em produção

## Proteções Ativas

- **CSRF:** `@csrf` em forms + meta tag para AJAX (axios)
- **XSS:** Blade `{{ }}` escapa automaticamente
- **SQL Injection:** Eloquent com bindings parametrizados
- **Clickjacking:** `X-Frame-Options: SAMEORIGIN`
- **HTTPS:** HSTS em produção (1 ano)
- **reCAPTCHA:** v2 no login (`config/recaptcha.php`)
- **Soft Deletes:** Dados nunca são perdidos permanentemente
- **Auditoria:** Observers + Domain Loggers

## Health Check (`GET /health`)

Rota pública. Verifica: database, cache, disk (>100MB), queue (failed_jobs <10).
Retorna JSON com status `healthy/unhealthy` (HTTP 200/503).

## Pendências

- AclMiddleware com lógica incompleta
- CSP (Content-Security-Policy) não implementado
- Rate limiting em rotas API
- Verificação SSL do reCAPTCHA em produção

Para autorização detalhada, consulte `/auth`.
