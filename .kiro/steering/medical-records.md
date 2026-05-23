---
inclusion: manual
---

# Prontuários Médicos (MedicalRecord)

## Visão Geral

O prontuário é o registro clínico de uma sessão de atendimento. Cada prontuário pertence a um paciente (polimórfico: `Kid` ou `User`) e é criado por um profissional. O sistema suporta **versionamento imutável**: edições criam novas versões, preservando o histórico completo.

---

## Schema da Tabela `medical_records`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint PK | |
| `parent_id` | bigint FK nullable | Aponta para o registro original (v1) da cadeia de versões |
| `version` | int default 1 | Número da versão (1, 2, 3...) |
| `is_current_version` | boolean default true | Apenas um registro por cadeia pode ser `true` |
| `patient_id` | bigint | ID do paciente (polimórfico) |
| `patient_type` | string | Classe do paciente — atualmente sempre `App\Models\Kid` |
| `session_date` | date | Data da sessão (não pode ser futura) |
| `complaint` | text | Demanda / queixa — mínimo 10 chars |
| `objective_technique` | text | Objetivo e técnica utilizada — mínimo 10 chars |
| `evolution_notes` | text | Registro de evolução — mínimo 10 chars, máximo 10.000 |
| `referral_closure` | text nullable | Encaminhamento ou encerramento |
| `html_content` | longText nullable | HTML gerado para PDF (snapshot no momento da criação/edição) |
| `created_by` | FK users | Profissional que criou o registro |
| `updated_by` | FK users nullable | Quem atualizou |
| `deleted_by` | FK users nullable | Quem enviou para lixeira |
| `deleted_at` | timestamp | SoftDeletes |

**Índices de performance:** `created_by`, `session_date`, `(patient_id, patient_type)`, `(patient_id, patient_type, session_date)`, `parent_id`, `is_current_version`, `(parent_id, version)`.

---

## Modelo: Relacionamentos e Scopes

### Relacionamentos

```php
$record->patient()       // morphTo → Kid ou User
$record->creator()       // belongsTo User (created_by)
$record->updater()       // belongsTo User (updated_by)
$record->deleter()       // belongsTo User (deleted_by)
$record->parent()        // belongsTo MedicalRecord (parent_id) — versão original
$record->versions()      // hasMany MedicalRecord (parent_id) — versões filhas
```

### Scopes

```php
// Profissional autenticado: apenas registros que ele criou, de kids vinculados a ele
MedicalRecord::forAuthProfessional()

// Paciente autenticado: apenas seus próprios registros (patient_type = User)
MedicalRecord::forAuthPatient()

// Apenas versão atual (is_current_version = true)
MedicalRecord::currentVersion()

// Apenas versões antigas (is_current_version = false)
MedicalRecord::history()

// Por paciente específico
MedicalRecord::forPatient($patientId, $patientType)
```

### Accessors úteis

```php
$record->session_date_formatted   // Data no formato d/m/Y
$record->patient_name             // Nome do paciente (via morphTo)
$record->patient_type_name        // 'Criança', 'Adulto' ou 'Desconhecido'
```

### Métodos de versionamento

```php
$record->getAllVersions()    // Todas as versões da cadeia (incluindo a atual)
$record->getLatestVersion()  // Versão com is_current_version = true
$record->getOriginalVersion() // Versão v1 (sem parent_id)
```

---

## Fluxo Completo

### 1. Criação

**Rota:** `POST /medical-records` → `MedicalRecordsController@store`

- Profissional cria para seus próprios pacientes vinculados.
- Admin (`medical-record-create-all`) pode criar em nome de qualquer profissional — campo `professional_id` aparece no formulário apenas para admin.
- Ao criar:
  1. `version = 1`, `is_current_version = true`, `parent_id = null`.
  2. HTML do prontuário é gerado imediatamente via `generateHtml()` e salvo em `html_content`.
  3. O HTML inclui: dados do paciente, campos clínicos, assinatura do profissional (nome em maiúsculas + número de registro CRP), cidade e data.
- `session_date` aceita formato `d/m/Y` (brasileiro) — o setter converte para `Y-m-d` internamente.
- Data da sessão não pode ser futura (`before_or_equal:today`).

### 2. Listagem

**Rota:** `GET /medical-records` → `MedicalRecordsController@index`

- **Sempre** filtra por `currentVersion()` — versões antigas nunca aparecem na listagem.
- Visibilidade por perfil:
  - `medical-record-list-all` → vê todos os prontuários, com filtro por profissional.
  - `medical-record-view-own` → paciente vê apenas seus próprios (scope `forAuthPatient`).
  - Profissional → vê apenas prontuários que **ele criou** de kids vinculados a ele (scope `forAuthProfessional`).
- Filtros disponíveis: profissional (admin), paciente, intervalo de datas, busca em `complaint` e `evolution_notes`.
- Filtros de select2 submetem o formulário automaticamente via `change`.

### 3. Visualização

**Rota:** `GET /medical-records/{id}` → `MedicalRecordsController@show`

- Exibe os campos clínicos com `white-space: pre-wrap` para preservar quebras de linha.
- Seção "Outros Registros deste Paciente" visível apenas para admin (`medical-record-list-all`).
- Ações disponíveis no rodapé do card: Download PDF, Editar, Excluir — cada uma protegida por `@can`.

### 4. Edição (atualização direta)

**Rota:** `PUT /medical-records/{id}` → `MedicalRecordsController@update`

- Atualiza os campos do registro **sem criar nova versão**.
- Após salvar, regenera o `html_content` via `generateHtmlFromRecord()`.
- Apenas o criador do registro ou admin (`medical-record-edit-all`) pode editar.
- O campo profissional é exibido como `readonly` no formulário de edição — não pode ser alterado.

### 5. Versionamento (nova versão)

**Rotas:**
- `GET /medical-records/{id}/new-version` → formulário
- `POST /medical-records/{id}/new-version` → `MedicalRecordsController@createNewVersion`

- Só é possível criar nova versão a partir da **versão atual** (`is_current_version = true`).
- Ao criar nova versão:
  1. Versão atual tem `is_current_version` setado para `false`.
  2. Nova versão criada com `parent_id = (parent_id do atual ?? id do atual)`, `version = atual + 1`, `is_current_version = true`.
  3. HTML gerado e salvo na nova versão.
- Versões antigas ficam acessíveis via histórico mas não aparecem na listagem principal.

### 6. Histórico de versões

**Rota:** `GET /medical-records/{id}/history` → `MedicalRecordsController@history`

- Exibe todas as versões da cadeia via `$record->getAllVersions()`.
- Ordenadas por `version DESC`.

### 7. Download PDF

**Rota:** `GET /medical-records/{id}/pdf` → `MedicalRecordsController@downloadPdf`

- Gera PDF via DomPDF a partir da view `medical-records.pdf-template`.
- Template estende `documents.layouts.pdf-base`.
- Inclui: identificação do paciente, 4 seções clínicas numeradas, assinatura do profissional.
- Nome do arquivo: `prontuario_{NomePaciente}_{data-sessao}.pdf`.
- Campos sensíveis são escapados com `e()` + `nl2br()` no template.

### 8. Lixeira e restauração

- **Lixeira:** `GET /medical-records/trash` — apenas admin (`medical-record-list-all`).
- **Restaurar:** `POST /medical-records/{id}/restore` — admin ou criador.
- Soft delete registra `deleted_by` antes de deletar.

---

## Geração do HTML (`html_content`)

O HTML é gerado em dois contextos:

1. **Criação/nova versão** → `generateHtml(array $data, $creatorId)` — recebe array de dados ainda não persistidos.
2. **Edição** → `generateHtmlFromRecord(MedicalRecord $record)` — recebe o model já atualizado.

Ambos carregam:
- Imagem de marca d'água (`public/images/bg-doc.png`) em base64.
- Logotipo (`public/images/logotipo.png`) em base64.
- Nome do profissional em maiúsculas (`strtoupper`).
- Número de registro (`registration_number` do `Professional`).
- Cidade do criador (fallback: `'Santana de Parnaíba'`).

**Importante:** o `html_content` é um snapshot — não é atualizado automaticamente se os dados do profissional mudarem depois. Ele reflete o estado no momento da criação/edição.

---

## Autorização

### Permissões

| Permissão | Quem tem | O que permite |
|-----------|----------|---------------|
| `medical-record-list` | Profissional | Listar próprios prontuários |
| `medical-record-list-all` | Admin | Listar todos + ver lixeira + filtro por profissional |
| `medical-record-view-own` | Paciente | Ver apenas seus próprios prontuários |
| `medical-record-show` | Profissional | Ver prontuários de pacientes vinculados |
| `medical-record-show-all` | Admin | Ver qualquer prontuário |
| `medical-record-create` | Profissional | Criar para seus pacientes |
| `medical-record-create-all` | Admin | Criar em nome de qualquer profissional |
| `medical-record-edit` | Profissional | Editar apenas os que **ele criou** |
| `medical-record-edit-all` | Admin | Editar qualquer prontuário |
| `medical-record-delete` | Profissional | Deletar apenas os que **ele criou** |
| `medical-record-delete-all` | Admin | Deletar qualquer prontuário |

### Regras críticas da Policy

- **Profissional só edita/deleta o que ele criou** — verificação por `created_by === auth()->id()`.
- **Paciente** (`medical-record-view-own`) só vê registros onde `patient_type = User::class` e `patient_id = auth()->id()`. Não pode criar, editar ou deletar.
- **Scope `forAuthProfessional`** filtra por `created_by = auth()->id()` AND `patient_type = Kid::class` AND `patient_id IN (kids do profissional)`. Se o usuário não tem `professional`, retorna zero resultados (`whereRaw('1 = 0')`).

---

## Pacientes no Prontuário

Apesar do campo polimórfico suportar `Kid` e `User`, **na prática todos os pacientes são `Kid`** (tabela unificada). A validação em `MedicalRecordRequest` restringe `patient_type` a `App\Models\Kid` apenas.

- Crianças: `Kid::children()` (age < 13)
- Adultos: `Kid::adults()` (age >= 13)
- Ambos: `Kid::all()` ou `Kid::orderBy('name')->get()`

O método `getPatientsForUser()` retorna todos os kids (crianças + adultos) vinculados ao profissional autenticado.

---

## LGPD — Logging

O `MedicalRecordLogger` **nunca loga o conteúdo clínico** (`complaint`, `objective_technique`, `evolution_notes`, `referral_closure`). Ao logar mudanças, esses campos aparecem como `[CHANGED]`. O identificador do paciente nos logs usa iniciais (`$patient->initials`), não o nome completo.

---

## Rotas

```
GET    /medical-records                          index
GET    /medical-records/create                   create
POST   /medical-records                          store
GET    /medical-records/{id}                     show
GET    /medical-records/{id}/edit                edit
PUT    /medical-records/{id}                     update
DELETE /medical-records/{id}                     destroy (soft delete)
GET    /medical-records/trash                    trash
POST   /medical-records/{id}/restore             restore
GET    /medical-records/{id}/new-version         newVersion (formulário)
POST   /medical-records/{id}/new-version         createNewVersion
GET    /medical-records/{id}/history             history
GET    /medical-records/{id}/pdf                 downloadPdf
GET    /medical-records/patient-history          patientHistory (AJAX)
```

---

## Regras de Negócio Críticas

1. **Listagem sempre filtra `currentVersion()`** — nunca exibir versões antigas na listagem principal.
2. **Nova versão só pode ser criada a partir da versão atual** — verificar `is_current_version` antes de criar.
3. **Ao criar nova versão, marcar a anterior como `is_current_version = false`** antes de criar a nova — fazer em transação DB.
4. **`parent_id` da nova versão = `parent_id` da atual ?? `id` da atual** — nunca apontar para uma versão intermediária.
5. **Profissional só vê/edita/deleta o que ele criou** — nunca relaxar essa regra sem permissão `-all`.
6. **`html_content` é gerado e salvo em toda criação e edição** — nunca deixar `null` em registros ativos.
7. **`session_date` aceita `d/m/Y` no formulário** — o setter do model converte para `Y-m-d`. Usar `$record->session_date_formatted` para exibição.
8. **Admin criando em nome de profissional** — `created_by` deve ser o `user_id` do profissional selecionado, não o admin.
9. **Campos clínicos têm mínimo de 10 caracteres** — `complaint`, `objective_technique`, `evolution_notes`.
10. **Toda operação usa `DB::beginTransaction()` / `DB::commit()` / `DB::rollBack()`**.
