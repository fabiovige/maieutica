---
description: Schema do banco (31 tabelas), convenções, enums, pivots
---

Leia `docs/dicionario-dados.md` na íntegra. Use-o para responder perguntas sobre o schema do banco.

## Convenções do Banco (31 tabelas)

**Padrões obrigatórios:**
- **Soft Delete** (`deleted_at`): users, kids, checklists, planes, professionals, roles, medical_records, generated_documents
- **Audit fields** (`created_by`, `updated_by`, `deleted_by`): tabelas principais — FK para `users.id`
- **Migrations:** toda alteração via migration, NUNCA `ALTER TABLE` direto
- **BaseModel:** classe base abstrata que inclui SoftDeletes + audit fields

**Relações polimórficas:**
- `medical_records`: `patient_type` + `patient_id` (Kid)
- `generated_documents`: `documentable_type` + `documentable_id` (Kid ou User)
- `model_has_roles/permissions`: Spatie Permission (polimórfico)

**Enums importantes:**
- `users.type`: `i` (interno) / `e` (externo)
- `kids.gender`: `M` / `F`
- `kids.ethnicity`: 8 valores possíveis
- `checklists.level`: 1-4 (níveis Avaliação Multidimensional)
- `checklists.situation`: `a` (aberto) / `f` (finalizado)
- `logs.action`: insert / update / remove / info

**Pivots:**
- `user_professional` — User ↔ Professional (1:1 com UNIQUE)
- `kid_professional` — Kid ↔ Professional (N:N com `is_primary`)
- `checklist_competence` — Checklist ↔ Competence (nota 0-3)
- `competence_plane` — Competence ↔ Plane
- `domain_level` — Domain ↔ Level

**Escala Avaliação Multidimensional (notas):** 0=Não testado, 1=Emergente, 2=Inconsistente, 3=Consistente

Para entender como os modelos se relacionam no código, consulte `/arquitetura`.
