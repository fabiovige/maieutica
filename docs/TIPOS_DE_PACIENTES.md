# Tipos de Pacientes no Maieutica

> Atualizado: 2026-04-15

Todos os pacientes do sistema sao registrados na tabela `kids`. O campo `is_adult` (boolean) distingue criancas de adultos.

---

## Modelo Unificado: Kid

**Tabela:** `kids`

| Campo | Tipo | Descricao |
|-------|------|-----------|
| `name` | varchar | Nome completo |
| `birth_date` | date | Data de nascimento |
| `is_adult` | boolean | `false` = crianca, `true` = adulto |
| `gender` | char(1) | M/F |
| `ethnicity` | varchar | Etnia |
| `responsible_id` | FK users | Responsavel legal (criancas) |
| `photo` | varchar | Foto do paciente |

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
Kid::adults()->get();           // Pacientes adultos (is_adult = true)
Kid::children()->get();         // Criancas (is_adult = false)
Kid::denverEligible()->get();   // Criancas elegiveis ao Denver (ate 60 meses)
```

**Atribuicao a Profissional:** Pivot `kid_professional` com campo `is_primary` — funciona para criancas e adultos.

---

## Crianca vs. Adulto

| Aspecto | Crianca (`is_adult=false`) | Adulto (`is_adult=true`) |
|---------|---------------------------|--------------------------|
| Tabela | `kids` | `kids` |
| Responsavel legal | Sim (`responsible_id`) | Nao |
| Checklists Denver | Sim (ate 60 meses) | Nao |
| Planos de desenvolvimento | Sim | Nao |
| Prontuarios | Sim | Sim |
| Documentos gerados | Sim | Sim |
| Pivot de atribuicao | `kid_professional` | `kid_professional` |
| `patient_type` nos prontuarios | `App\Models\Kid` | `App\Models\Kid` |

---

## Fluxo de Cadastro

### Cadastrar Crianca
1. Admin ou Profissional: **Pacientes > Criancas > Novo**
2. Preenche: nome, data de nascimento, sexo, etnia, responsavel
3. Deixa o toggle "Paciente adulto" desligado
4. Sistema cria o Kid e vincula o profissional logado via `kid_professional`

### Cadastrar Paciente Adulto
1. Admin ou Profissional: **Pacientes > Criancas > Novo**
2. Preenche: nome, data de nascimento, sexo, etnia
3. Ativa o toggle **"Paciente adulto"**
4. Sistema cria o Kid com `is_adult = true`

### Marcar paciente existente como adulto
1. **Pacientes > [Paciente] > Editar**
2. Ativa o toggle **"Paciente adulto"**
3. Salvar

---

## Prontuarios

Todos os prontuarios usam `patient_type = App\Models\Kid`:

```php
$record->patient_type = 'App\Models\Kid';
$record->patient_id   = $kid->id;
```

**Dropdown no formulario de criacao:**
- Toggle "Crianca" mostra Kids com `is_adult = false` atribuidos ao profissional
- Toggle "Adulto" mostra Kids com `is_adult = true` atribuidos ao profissional
- Admin ve todos os pacientes de cada tipo

---

## Historico

Antes de abril/2026, pacientes adultos eram registrados como Users na tabela `users` com `allow = true`. A tabela `professional_user_patient` foi criada para vincular profissionais a esses pacientes, mas nunca foi populada. Na pratica, todos os pacientes (inclusive adultos) ja estavam na tabela `kids`.

A migration `2026_04_13_180000_add_is_adult_to_kids_table` adicionou o campo `is_adult` e marcou automaticamente 25 pacientes com 18+ anos como adultos.
