---
description: Modelos, controllers, observers, services, helpers, padrões arquiteturais
---

Leia `docs/architecture.md` na íntegra e use-o como contexto para responder perguntas sobre a arquitetura do Maiêutica.

## Regras Arquiteturais

**Domínio central:** Checklists de Avaliação Multidimensional (notas 0-3) para Kids, com prontuários polimórficos para Kids e adultos.

**Camadas do sistema:**
- Controllers (thin) → Services (lógica) → Models (Eloquent) → Observers (eventos)
- Autorização via Policies (10 no total), NUNCA `hasRole()` — sempre `can()`
- Logging em duas camadas: Observer (automático) + Domain Logger (explícito no controller)

**Padrões obrigatórios:**
- `BaseModel` = classe base abstrata com SoftDeletes + audit fields
- Relações polimórficas: `MedicalRecord` e `GeneratedDocument` (patient_type/documentable_type)
- Professional ↔ User = 1:1 via pivot `user_professional` (UNIQUE constraint em user_id)
- Professional ↔ Kid = N:N via pivot `kid_professional` (campo `is_primary`)
- Todos os pacientes (crianças e adultos) na tabela `kids`, classificados por `birth_date`

**API Controllers** (8 em `app/Http/Controllers/Api/`): usados pelos composables Vue via Axios.

**Services:** `ChecklistService` (avaliação/cálculos) e `OverviewService` (resumo de progresso).

**Helpers** (`app/helpers.php`): `label_case()`, `get_progress_color()`, `get_progress_gradient()`, `get_chart_gradient()`.

Se a pergunta for sobre tabela/coluna específica, consulte também `docs/dicionario-dados.md`.
