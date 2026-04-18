# Specs — Features Pendentes

> Visao geral das especificacoes de features planejadas para o Maieutica.

---

## Resumo

| # | Spec | Status | Prioridade | Esforco | Arquivo |
|---|------|--------|------------|---------|---------|
| 1 | Observabilidade v2 | Pendente | Alta | 1-2 semanas | `docs/specs/plano-observabilidade-v2.md` |
| 2 | Relatorios | Pendente | Alta | 2-3 semanas | `docs/specs/relatorios.md` |
| 3 | Remover Dropdown Acoes | Pendente | Media | 1-2 dias | `docs/specs/remover-dropdown-acoes.md` |
| 4 | UX Checklist Show Page | Pendente | Media | 2-3 dias | `docs/specs/ux-checklist-show-page.md` |
| 5 | PWA - Instalar como App | Implementado | — | — | `docs/specs/pwa-instalar-como-app.md` |

---

## 1. Observabilidade v2

**Objetivo:** Substituir log-viewer (instavel) + Sentry (trial expirando) por stack self-hosted.

**Fases:**
1. **Fundacao (1-2 dias):** Laravel Telescope + Health Check + notificacoes de erro critico
2. **Monitoramento Ativo (3-5 dias):** Slow Query Log (>500ms), Request Logger (>1s ou 5xx), login failures
3. **Maturidade (1-2 semanas):** Auto-pruning (48h em prod), remover log-viewer/Sentry, dashboard admin

**Stack:** Laravel Telescope ^4.x (gratuito, self-hosted)  
**Impacto:** ~20MB disco/semana em prod, CPU minimo  
**Protecao:** Dashboard em `/telescope`, protegido por auth + gate

**Leia:** `docs/specs/plano-observabilidade-v2.md` para detalhes completos.

---

## 2. Relatorios

**Objetivo:** Sistema de relatorios clinicos e gerenciais com exportacao PDF/Excel.

**10 Relatorios Planejados:**

| Prioridade | Relatorio | Tipo |
|------------|-----------|------|
| Alta | Pacientes Cadastrados | Clinico |
| Alta | Progresso Individual | Clinico |
| Alta | Desempenho por Dominio | Clinico |
| Alta | Prontuarios (Sessoes) | Clinico |
| Media | Carga por Profissional | Gerencial |
| Media | Planos de Desenvolvimento | Gerencial |
| Media | Documentos Gerados | Auditoria |
| Baixa | Analise Populacional (Coorte) | Avancado |
| Baixa | Trilha de Auditoria | Avancado |
| Baixa | Avaliacoes Comparativas | Avancado |

**Arquitetura:**
- `ReportController` — novo controller
- `ReportService` — agregacao de queries
- `Exports/` — classes de exportacao (Excel)
- Biblioteca: `maatwebsite/excel` ^3.1

**Leia:** `docs/specs/relatorios.md` para detalhes completos.

---

## 3. Remover Dropdown Acoes

**Objetivo:** Substituir dropdowns Bootstrap por botoes inline simples.

**Escopo:** 11 arquivos Blade  
**Padrao:** `@component('table-actions')` -> `<div class="d-flex gap-1">` com `btn-sm`

**Prioridades:**
1. 6 views de lixeira (trash)
2. 2 listagens simples (roles, users)
3. 3 listagens complexas (professionals, kids, checklists)

**Casos especiais:**
- Checklists: acoes condicionais (checklist fechado)
- Professionals: Ativar/Desativar mutuamente exclusivos
- Kids: Duas views (tabela + cards)

**Leia:** `docs/specs/remover-dropdown-acoes.md` para detalhes.

---

## 4. UX Checklist Show Page

**Objetivo:** Redesign da listagem e pagina de detalhes do checklist (mobile-first).

**Fases:**
0. **Redesign da Lista:** Tabela -> cards mobile-first (col-12, flexbox horizontal)
1. **Pagina Show:** Header (nome crianca + nivel + status) + barra de acoes
2. **Simplificacao:** Substituir todos os botoes de acao por unico [Ver]
3. **Controller:** Passar `$isOpen`, `$isAdmin`, `$kidId` para view

**Leia:** `docs/specs/ux-checklist-show-page.md` para detalhes.

---

## 5. PWA - Instalar como App

**Status:** IMPLEMENTADO (commit `fa66ddf`)

**O que foi feito:**
- `public/manifest.json` — manifesto PWA
- `public/sw.js` — Service Worker (pass-through, sem cache)
- Icones em `public/images/icons/` (8 tamanhos)
- Meta tags PWA nos layouts
- Instalavel como app no Android/iOS

**Leia:** `docs/specs/pwa-instalar-como-app.md` para spec original.

---

## Como Usar as Specs

### Antes de implementar uma feature
1. Leia a spec completa: `docs/specs/[nome].md`
2. Carregue a skill relevante (`/auth`, `/frontend`, etc.)
3. Siga a metodologia SDD (`/sdd`) se aplicavel
4. Verifique dependencias com outras specs

### Ao criar nova spec
1. Use o template em `/sdd`
2. Coloque em `docs/specs/`
3. Atualize esta overview
4. Adicione status e prioridade
