---
description: Features pendentes, roadmap, especificações de implementação
---

Leia `docs/specs-overview.md` na íntegra. Use-o para responder perguntas sobre features planejadas, especificações pendentes e roadmap.

## Features Pendentes

| Spec | Status | Prioridade | Esforço |
|------|--------|------------|---------|
| Observabilidade v2 | Pendente | Alta | 1-2 semanas |
| Relatórios | Pendente | Alta | 2-3 semanas |
| Remover Dropdown Ações | Pendente | Média | 1-2 dias |
| UX Checklist Show Page | Pendente | Média | 2-3 dias |
| PWA - Instalar como App | **Implementado** | — | — |

## Resumo Rápido

**Observabilidade v2** (`docs/specs/plano-observabilidade-v2.md`):
- Substituir log-viewer + Sentry por Laravel Telescope
- 3 fases: fundação → monitoramento ativo → maturidade
- Health check já implementado em `GET /health`

**Relatórios** (`docs/specs/relatorios.md`):
- 10 relatórios (clínicos + gerenciais + avançados)
- Exportação PDF/Excel (`maatwebsite/excel`)
- Novo controller + service + exports

**Remover Dropdown Ações** (`docs/specs/remover-dropdown-acoes.md`):
- 11 views Blade: dropdown → botões inline `d-flex gap-1`
- Permissões e SweetAlert2 mantidos

**UX Checklist Show Page** (`docs/specs/ux-checklist-show-page.md`):
- Lista: tabela → cards mobile-first
- Show: header + barra de ações
- Simplificação: todos os botões → único [Ver]

**PWA** (`docs/specs/pwa-instalar-como-app.md`):
- Implementado (commit `fa66ddf`)
- manifest.json, sw.js, ícones, meta tags

## Como Usar

1. Leia a spec completa antes de implementar
2. Carregue skills relevantes (`/auth`, `/frontend`, etc.)
3. Siga `/sdd` para novas specs
4. Specs ficam em `docs/specs/`
