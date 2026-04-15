# JIRA-001: Correção de Pacientes, Denver e Continuidade de Prontuários

**Data:** 2026-03-23
**Status:** Implementado (3 fases)
**Investigação:** `docs/fix-001.md`

---

## Plano de Implementação

### Fase 1 — Prontuários funcionais (prioridade alta) ✓

**Objetivo:** Permitir que profissionais criem prontuários para qualquer paciente (criança ou adulto) sem fricção.

**O que foi feito:**
1. `MedicalRecordsController` — Removida separação Kid/User. Todos os pacientes vêm da tabela `kids`. Adicionado endpoint AJAX `patientHistory` para buscar histórico.
2. `medical-records/create.blade.php` — Dropdown único "Paciente" com idade visível. Painel lateral mostra histórico ao selecionar paciente.
3. `medical-records/index.blade.php` — Removido filtro "Tipo Paciente". Dropdown único de pacientes com idade.
4. `medical-records/edit.blade.php` — Simplificado para dropdown único de pacientes.
5. `medical-records/show.blade.php` — Adicionada seção "Outros Registros deste Paciente" com tabela de prontuários anteriores.
6. `MedicalRecordRequest` — `patient_type` agora é sempre `App\Models\Kid` via `prepareForValidation()`.

**Arquivos modificados:**
- `app/Http/Controllers/MedicalRecordsController.php`
- `app/Http/Requests/MedicalRecordRequest.php`
- `resources/views/medical-records/create.blade.php`
- `resources/views/medical-records/index.blade.php`
- `resources/views/medical-records/edit.blade.php`
- `resources/views/medical-records/show.blade.php`
- `routes/web.php` (adicionadas rotas `patient-history` e `history`)

---

### Fase 2 — Denver com filtro de idade (prioridade média) ✓

**Objetivo:** Restringir checklists Denver a crianças ≤ 6 anos.

**O que foi feito:**
1. `Kid` model — Adicionado `scopeDenverEligible()` que filtra por `TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) <= 6`.
2. `Kid::getDenverEligibleKids()` — Novo método estático que chama `getKids(denverOnly: true)`.
3. `Kid::getKids()` — Aceita parâmetro `$denverOnly` para aplicar filtro.
4. `ChecklistController@create()` — Agora usa `Kid::getDenverEligibleKids()`.
5. `checklists/create.blade.php` — Mostra idade ao lado do nome e label "(até 6 anos - Denver)".

**Arquivos modificados:**
- `app/Models/Kid.php`
- `app/Http/Controllers/ChecklistController.php`
- `resources/views/checklists/create.blade.php`

---

### Fase 3 — Visão centrada no paciente (prioridade menor) ✓

**Objetivo:** Integrar prontuários na página do paciente.

**O que foi feito:**
1. `KidsController@show()` — Carrega prontuários (`medicalRecords` com `is_current_version=true`, limit 10).
2. `kids/show-details.blade.php` — Adicionada seção "Prontuários" com tabela e botão "Novo Prontuário".
3. `routes/web.php` — Rota `medical-records/{medicalRecord}/history` mapeada para `MedicalRecordsController@history`.

**Arquivos modificados:**
- `app/Http/Controllers/KidsController.php`
- `resources/views/kids/show-details.blade.php`
- `routes/web.php`

---

## Decisões Técnicas

- **Não migrar dados** — adultos permanecem na tabela `kids`
- **Não renomear entidades** — `kids` continua sendo `kids` no código
- **patient_type fixo** — prontuários criados via novo fluxo usam `App\Models\Kid` como `patient_type`
- **Compatibilidade** — suporte a `App\Models\User` como patient_type mantido no model (polimorfismo preservado)
- **Denver filtro** — hard filter no dropdown (só mostra ≤ 6 anos), não soft warning

## Notas

- Testes pré-existentes falhando por `role_id` column na UserFactory (bug anterior, não relacionado)
- Todos os arquivos PHP passam em `php -l` (syntax check)
- Caches limpos com `route:clear`, `view:clear`, `cache:clear`
