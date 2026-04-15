# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

**Maiêutica** — Plataforma clínica de avaliação cognitiva infantil. Em produção em maieuticavaliacom.br.

**Versão:** 1.0.18 | **Stack:** Laravel 9.52 · Vue 3.5 (Options API) · Bootstrap 5.3 · MySQL/MariaDB · Laravel Mix 6.x

---

## Regras Inegociáveis

**Este sistema está em produção.**

- Nunca quebrar funcionalidades existentes — testar antes de commitar
- Sempre usar migrations (nunca `ALTER TABLE` direto)
- Refatorar incrementalmente — estabilidade antes de elegância
- **Nunca esvaziar o banco local** — usar `php artisan db:seed`

---

## Autorização — CRÍTICO

**SEMPRE `can()`, NUNCA `hasRole()` para autorização:**

```php
$user->can('user-edit')              // ✅ Controller/Service
@can('user-edit') ... @endcan        // ✅ Blade
$this->authorize('update', $user);   // ✅ Policy

if ($user->hasRole('admin')) { }     // ❌ ERRADO — quebra a arquitetura
```

**Padrão:** `{entidade}-{ação}[-all]` — ex: `kid-list`, `kid-list-all`, `medical-record-edit`

**Roles** = apenas para atribuição: `$user->assignRole('profissional')`

**10 Policies:** `Checklist`, `Kid`, `MedicalRecord`, `GeneratedDocument`, `Plane`, `Professional`, `User`, `Role`, `Responsible`, `Competence`

---

## Comandos Essenciais

```bash
# Desenvolvimento
npm run dev            # Compilar assets (uma vez)
npm run watch          # Compilar assets (contínuo)
php artisan serve      # Servidor local
composer clear         # Limpa cache, route, view, config (dumpautoload + 4 clears)

# Banco
php artisan db:seed    # Popular banco (uso normal)
composer fresh         # migrate:fresh --seed (SOMENTE se pedido explicitamente)

# Testes
php artisan test                              # Todos os testes
php artisan test --filter=NomeDoTeste         # Teste específico
php artisan test tests/Unit/Models/           # Diretório específico

# Lint
./vendor/bin/pint                             # Fix style (Laravel Pint)
./vendor/bin/pint --test                      # Dry-run (apenas reportar)

# Logs
# Browser: /log-viewer | Terminal: tail -f storage/logs/laravel.log
```

---

## Notas Rápidas

- **Estilos sidebar:** Inline em `app.blade.php`, não no SCSS compilado
- **Fonte base:** 16px (1rem) em todos os arquivos
- **Login:** `auth/login.blade.php` standalone — não carrega `app.css`/`custom.css`
- **PDF:** Templates estendem `documents.layouts.pdf-base`, fonte `DejaVu Sans`
- **Pacientes:** Todos na tabela `kids` — criancas (idade < 13) e adultos (idade >= 13), calculado por `birth_date`. Constante: `Kid::ADULT_AGE_YEARS`
- **Failed Jobs:** 7 registros em `failed_jobs` — investigar antes de usar workers de fila

---

## Skills Disponíveis (Slash Commands)

Use `/nome` para carregar o contexto + regras de negócio de cada domínio:

| Skill | Conteúdo |
|-------|----------|
| `/arquitetura` | Modelos, controllers, observers, services, helpers, padrões arquiteturais |
| `/auth` | Autorização (`can()` NUNCA `hasRole()`), permissions, policies, regras Professional↔User |
| `/pacientes` | Modelo unificado Kids, classificação por birth_date, scopes adults/children |
| `/prontuarios` | Prontuários polimórficos, versionamento, scope forAuthProfessional |
| `/dicionario` | Schema do banco (31 tabelas), convenções, enums, pivots |
| `/frontend` | Vue 3 Options API, CSS architecture, componentes, compilação |
| `/tipografia` | Fonte Nunito, tokens CSS, ordem de carregamento |
| `/sidebar` | Layout sidebar v2.0, estilos inline, menu com permissões |
| `/documentos` | Geração de PDFs, 6 modelos, DomPDF, polimorfismo |
| `/logging` | Duas camadas (Observer + Domain Logger), LGPD, armazenamento |
| `/testing` | Estrutura de testes, comandos, debugging, lint |
| `/deploy` | Manual de atualização em produção |
| `/sdd` | Metodologia Spec-Driven Development |

---

## Estrutura de Documentação

```
docs/           → 13 docs ativos (referenciados pelas skills acima)
docs/specs/     → 4 specs de features pendentes
docs/historico/ → Planos concluídos, análises históricas, implementações passadas
```

**Ponto de entrada:** sempre pelas skills (`/nome`). Os docs em `docs/` são referência detalhada que as skills carregam automaticamente.
