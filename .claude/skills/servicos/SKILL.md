---
description: Services (Checklist, Overview), helpers, enums, jobs, notifications
---

Leia `docs/servicos.md` na íntegra. Use-o para responder perguntas sobre services, helpers, enums, jobs, notifications e mail.

## Services

**ChecklistService** (`app/Services/ChecklistService.php`):
- `percentualDesenvolvimento($checklistId)` — calcula % de desenvolvimento
- Escala: nota 0=não testado, 1=não consegue, 2=inconsistente, 3=consistente
- Apenas nota 3 conta como "válido"
- Retorno: float 0-100

**OverviewService** (`app/Services/OverviewService.php`):
- `getOverviewData($kidId, $levelId?, $checklistId?)` — visão completa do paciente
- Retorna: kid, idade em meses, dados por domínio, % global, idade desenvolvimental, atraso, áreas fracas
- Usado em: overview do paciente, gráficos radar, relatórios

## Helpers (`app/helpers.php`)

- `label_case($text)` — snake_case → Title Case
- `get_progress_color($pct)` — hex color por percentual
- `get_progress_gradient($pct)` — gradiente CSS horizontal
- `get_chart_gradient($pct)` — gradiente CSS vertical

## Enums (`app/Enums/ProgressColors.php`)

11 cores mapeando 0-100% (vermelho → amarelo → verde)

## Jobs e Notifications

- `SendKidUpdateJob` — notifica admin ao atualizar paciente (User ID 2 hardcoded)
- `KidUpdateNotification` — email de atualização de criança
- `WelcomeNotification` — email de boas-vindas com senha temporária

## Mail (`app/Mail/`)

- `UserCreatedMail` — boas-vindas ao novo usuário
- `UserUpdatedMail` — aviso de atualização de conta
- `UserDeletedMail` — aviso de desativação de conta

Todos usam `ShouldQueue`. Para detalhes de email, consulte `/emails`.
