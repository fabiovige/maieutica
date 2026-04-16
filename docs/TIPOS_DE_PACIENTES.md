# Tipos de Pacientes no Maieutica

> Atualizado: 2026-04-15

Todos os pacientes do sistema sao registrados na tabela `kids`. A distincao entre crianca e adulto e calculada dinamicamente pela **data de nascimento**: idade >= 13 anos = adulto, < 13 anos = crianca. Constante: `Kid::ADULT_AGE_YEARS = 13`.

---

## Modelo Unificado: Kid

**Tabela:** `kids`

| Campo | Tipo | Descricao |
|-------|------|-----------|
| `name` | varchar | Nome completo |
| `birth_date` | date | Data de nascimento (obrigatorio) |
| `gender` | char(1) | M/F |
| `ethnicity` | varchar | Etnia |
| `responsible_id` | FK users | Responsavel legal (criancas) |
| `photo` | varchar | Foto do paciente |

**Classificacao automatica:** `$kid->is_adult` e um accessor computado — retorna `true` se `diffInYears(birth_date, now()) >= 13`.

**Relacionamentos:**
```php
$kid->professionals()       // Profissionais via kid_professional (many-to-many)
$kid->checklists()          // Avaliacoes Denver (apenas criancas)
$kid->planes()              // Planos de desenvolvimento (apenas criancas)
$kid->medicalRecords()      // Prontuarios (morphMany, patient_type = App\Models\Kid)
$kid->generatedDocuments()  // Documentos gerados (morphMany)
$kid->responsible()         // Responsavel legal (belongsTo User)
```

**Scopes:**
```php
Kid::adults()->get();           // Pacientes com idade >= 13 anos
Kid::children()->get();         // Pacientes com idade < 13 anos (ou sem birth_date)
Kid::denverEligible()->get();   // Criancas elegiveis ao Denver (ate 60 meses)
```

**Atribuicao a Profissional:** Pivot `kid_professional` com campo `is_primary` — funciona para criancas e adultos.

---

## Crianca vs. Adulto

| Aspecto | Crianca (idade < 13) | Adulto (idade >= 13) |
|---------|----------------------|----------------------|
| Tabela | `kids` | `kids` |
| Classificacao | Automatica por `birth_date` | Automatica por `birth_date` |
| Responsavel legal | Sim (`responsible_id`) | Nao |
| Checklists Denver | Sim (ate 60 meses) | Nao |
| Planos de desenvolvimento | Sim | Nao |
| Prontuarios | Sim | Sim |
| Documentos gerados | Sim | Sim |
| Pivot de atribuicao | `kid_professional` | `kid_professional` |
| `patient_type` nos prontuarios | `App\Models\Kid` | `App\Models\Kid` |

**Nota:** Um paciente que completa 13 anos e automaticamente reclassificado como adulto. Nao ha flag manual.

---

## Fluxo de Cadastro

### Cadastrar Paciente (Crianca ou Adulto)
1. Admin ou Profissional: **Denver > Criancas > Novo**
2. Preenche: nome, data de nascimento, sexo, etnia, responsavel (se crianca)
3. Sistema classifica automaticamente como crianca ou adulto com base na data de nascimento
4. Sistema vincula o profissional logado via `kid_professional`

---

## Prontuarios

Todos os prontuarios usam `patient_type = App\Models\Kid`:

```php
$record->patient_type = 'App\Models\Kid';
$record->patient_id   = $kid->id;
```

**Dropdown no formulario de criacao:**
- Toggle "Crianca" mostra pacientes com idade < 13 atribuidos ao profissional
- Toggle "Adulto" mostra pacientes com idade >= 13 atribuidos ao profissional
- Admin ve todos os pacientes de cada tipo

---

## Historico

Antes de abril/2026, pacientes adultos eram registrados como Users na tabela `users` com `allow = true`. A tabela `professional_user_patient` foi criada para vincular profissionais a esses pacientes, mas nunca foi populada. Na pratica, todos os pacientes (inclusive adultos) ja estavam na tabela `kids`.

A migration `2026_04_13_180000_add_is_adult_to_kids_table` adicionou o campo `is_adult` (boolean). Em 2026-04-15, a regra de negocio mudou para classificacao automatica por data de nascimento (idade >= 13 = adulto). A migration `2026_04_15_142715_drop_is_adult_from_kids_table` removeu a coluna `is_adult`.
