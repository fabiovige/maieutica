# CLAUDE.md — Constituição do Maiêutica

**Maiêutica** — Plataforma clínica de avaliação cognitiva infantil. Em produção em maieuticavaliacom.br.

**Versão:** 1.0.18 | **Stack:** Laravel 9.x · Vue 3.5 (Options API) · Bootstrap 5.3 · MySQL/MariaDB · Laravel Mix 6.x

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
npm run watch          # Recompilar assets
php artisan serve      # Servidor local
composer clear         # Limpa cache, route, view, config

# Banco
php artisan db:seed    # Popular banco (uso normal)
composer fresh         # migrate:fresh --seed (SOMENTE se pedido explicitamente)

# Qualidade
php artisan test
./vendor/bin/pint

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

## Estado das Features (Abril 2026)

| Feature | Status |
|---------|--------|
| CRUD de Kids + Checklists | Completo |
| Competências + Domínios + Níveis | Completo |
| Planos de desenvolvimento | Completo |
| Geração de PDF (checklist, overview, plano) | Completo |
| Prontuários — Kids e Adultos | Completo |
| Sidebar vertical + design system | Completo |
| Atribuição profissional ↔ paciente adulto | Completo |
| Notificações por e-mail (templates) | Parcial — fila com falhas |
| Cadastro de adulto via UI para profissional | Incompleto — apenas Admin |
| Agendamento de sessões | Não iniciado |
| Portal de responsáveis | Não iniciado |
| Exportação Excel/CSV | Não iniciado |

---

## Skills Disponíveis (Slash Commands)

Use `/nome` para carregar o contexto de cada domínio:

| Skill | Conteúdo |
|-------|----------|
| `/arquitetura` | Modelos, controllers, observers, jobs, middleware |
| `/dicionario` | Schema completo do banco (31 tabelas) |
| `/pacientes` | Kids vs. Adultos — tipos, fluxos, atribuição |
| `/prontuarios` | Prontuários polimórficos + versionamento |
| `/auth` | Sistema de permissões + relacionamento profissional/usuário |
| `/frontend` | Vue components, CSS, design system |
| `/tipografia` | Sistema tipográfico, variáveis CSS |
| `/sidebar` | Layout sidebar v2.0 |
| `/documentos` | Geração de PDFs, templates |
| `/deploy` | Manual de atualização em produção |
| `/sdd` | Metodologia Spec-Driven Development |

---

## Índice de Documentação

### Arquitetura e Dados

| Arquivo | Conteúdo |
|---------|----------|
| `docs/architecture.md` | Modelos, controllers, observers, jobs, middleware |
| `docs/dicionario-dados.md` | Schema completo do banco (31 tabelas) |
| `docs/PROFESSIONAL_USER_RELATIONSHIP.md` | Relacionamentos profissional/usuário + autorização |
| `docs/packages.md` | Todos os pacotes (backend, frontend, dev) |

### Domínio Clínico

| Arquivo | Conteúdo |
|---------|----------|
| `docs/TIPOS_DE_PACIENTES.md` | Kids vs. Adultos — tipos, fluxos, atribuição |
| `docs/medical-records.md` | Prontuários (polimórfico + versionamento) |
| `docs/documentos.md` | Geração de documentos PDF |

### Frontend e Design

| Arquivo | Conteúdo |
|---------|----------|
| `docs/frontend.md` | Vue components, CSS, design system de botões |
| `docs/tipografia.md` | Sistema tipográfico completo |
| `docs/novo-layout-sidebar.md` | Layout sidebar v2.0 |

### Operacional

| Arquivo | Conteúdo |
|---------|----------|
| `docs/logging.md` | Sistema de logging (observers + domain loggers) |
| `docs/testing.md` | Estrutura de testes e debugging |
| `docs/MANUAL_ATUALIZACAO_PRODUCAO.md` | Deploy em produção |

### Processo e Planejamento

| Arquivo | Conteúdo |
|---------|----------|
| `docs/SDD.md` | Metodologia Spec-Driven Development |
| `docs/PRD.md` | Product Requirements Document (Jan/2025 — parcialmente desatualizado) |
| `docs/analise_adulto.md` | Análise histórica — decisão de suporte a adultos |

### Specs Pendentes (features não implementadas)

| Arquivo | Conteúdo |
|---------|----------|
| `docs/remover-dropdown-acoes.md` | Substituir dropdowns por botões simples |
| `docs/ux-checklist-show-page.md` | Cards mobile-first para checklists |
| `docs/relatorios.md` | Plano de relatórios clínicos |
| `docs/plano-observabilidade-v2.md` | Stack de monitoring (Pail) |

### Referência Pontual

| Arquivo | Conteúdo |
|---------|----------|
| `docs/cep-autocomplete.md` | Busca de endereço por CEP (ViaCEP) |
| `docs/routes_checklist.md` | Tabela de rotas da API de checklists |

> Docs históricos (planos de implementação concluídos, investigações, tickets) estão em `docs/historico/`.
