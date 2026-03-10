# Dicionario de Dados - Maieutica

> Gerado em: 2026-02-06
> Banco de dados: `maieutica` (MySQL/MariaDB)
> Total de tabelas: **31**

---

## Sumario

| # | Tabela | Descricao | Registros (aprox.) |
|---|--------|-----------|-------------------|
| 1 | [users](#users) | Usuarios do sistema | 12 |
| 2 | [kids](#kids) | Criancas (pacientes infantis) | 27 |
| 3 | [professionals](#professionals) | Profissionais de saude | 3 |
| 4 | [professional_profiles](#professional_profiles) | Perfis profissionais (legado) | 0 |
| 5 | [specialties](#specialties) | Especialidades profissionais | 14 |
| 6 | [checklists](#checklists) | Avaliacoes/checklists cognitivos | 9 |
| 7 | [competences](#competences) | Competencias avaliadas | 446 |
| 8 | [checklist_competence](#checklist_competence) | Pivot: checklist x competencia (notas) | 4.014 |
| 9 | [levels](#levels) | Niveis de avaliacao | 4 |
| 10 | [domains](#domains) | Dominios cognitivos | 19 |
| 11 | [domain_level](#domain_level) | Pivot: dominio x nivel | 46 |
| 12 | [planes](#planes) | Planos de desenvolvimento | 7 |
| 13 | [competence_plane](#competence_plane) | Pivot: competencia x plano | 159 |
| 14 | [medical_records](#medical_records) | Prontuarios medicos (polimorficos) | 5 |
| 15 | [generated_documents](#generated_documents) | Documentos gerados (polimorficos) | 3 |
| 16 | [document_templates](#document_templates) | Templates de documentos | 5 |
| 17 | [kid_professional](#kid_professional) | Pivot: crianca x profissional | 12 |
| 18 | [user_professional](#user_professional) | Pivot: usuario x profissional (1:1) | 2 |
| 19 | [professional_user_patient](#professional_user_patient) | Pivot: profissional x paciente adulto | 0 |
| 20 | [roles](#roles) | Papeis (Spatie Permission) | 3 |
| 21 | [permissions](#permissions) | Permissoes (Spatie Permission) | 93 |
| 22 | [role_has_permissions](#role_has_permissions) | Pivot: papel x permissao | 122 |
| 23 | [model_has_roles](#model_has_roles) | Pivot: model x papel | 12 |
| 24 | [model_has_permissions](#model_has_permissions) | Pivot: model x permissao direta | 0 |
| 25 | [logs](#logs) | Logs de auditoria | 0 |
| 26 | [sessions](#sessions) | Sessoes de usuario (driver database) | 0 |
| 27 | [jobs](#jobs) | Fila de jobs (queue) | 17 |
| 28 | [failed_jobs](#failed_jobs) | Jobs que falharam | 7 |
| 29 | [password_resets](#password_resets) | Tokens de reset de senha | 0 |
| 30 | [personal_access_tokens](#personal_access_tokens) | Tokens de acesso pessoal (Sanctum) | 0 |
| 31 | [migrations](#migrations) | Controle de migrations do Laravel | 36 |

---

## Diagrama de Relacionamentos

```
users ─────────────────────────────────────────────────────────
 ├──< user_professional >── professionals
 ├──< professional_user_patient >── professionals
 ├──< model_has_roles >── roles
 ├──< model_has_permissions >── permissions
 ├──< kids (user_id, responsible_id)
 ├──< medical_records (created_by, updated_by, deleted_by)
 ├──< generated_documents (generated_by, created_by, updated_by, deleted_by)
 ├──< professionals (created_by, updated_by, deleted_by)
 ├──< specialties (created_by, updated_by, deleted_by)
 └──< sessions (user_id)

professionals ─────────────────────────────────────────────────
 ├──< kid_professional >── kids
 ├──< professional_user_patient >── users
 ├──< generated_documents (professional_id)
 └──→ specialties (specialty_id)

kids ──────────────────────────────────────────────────────────
 ├──< checklists (kid_id)
 ├──< planes (kid_id)
 ├──< kid_professional >── professionals
 ├──→ users (user_id)
 └──→ users (responsible_id)

checklists ────────────────────────────────────────────────────
 ├──< checklist_competence >── competences
 ├──< planes (checklist_id)
 └──→ kids (kid_id)

competences ───────────────────────────────────────────────────
 ├──< checklist_competence >── checklists
 ├──< competence_plane >── planes
 ├──→ levels (level_id)
 └──→ domains (domain_id)

levels ◄──── domain_level ────► domains

medical_records (polimorficos: patient_type + patient_id)
 ├──→ kids (quando patient_type = App\Models\Kid)
 ├──→ users (quando patient_type = App\Models\User)
 └──→ medical_records (parent_id - auto-referencia para versionamento)

generated_documents (polimorficos: documentable_type + documentable_id)
 ├──→ kids (quando documentable_type = App\Models\Kid)
 └──→ users (quando documentable_type = App\Models\User)

roles ◄──── role_has_permissions ────► permissions
```

---

## Tabelas de Dominio Principal

### users

> Usuarios do sistema (administradores, profissionais, responsaveis, pacientes adultos)

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `name` | varchar(191) | NAO | | | Nome completo |
| `email` | varchar(191) | NAO | UNI | | E-mail (login) |
| `avatar` | varchar(191) | SIM | | NULL | Caminho da foto de perfil |
| `email_verified_at` | timestamp | SIM | | NULL | Data de verificacao do e-mail |
| `password` | varchar(191) | NAO | | | Senha (hash bcrypt) |
| `type` | enum('i','e') | NAO | | 'i' | Tipo: i=interno, e=externo |
| `allow` | tinyint(1) | NAO | | 1 | Ativo/permitido no sistema |
| `remember_token` | varchar(100) | SIM | | NULL | Token "lembrar-me" |
| `phone` | varchar(191) | SIM | | NULL | Telefone |
| `postal_code` | varchar(191) | SIM | | NULL | CEP |
| `street` | varchar(191) | SIM | | NULL | Logradouro |
| `number` | varchar(191) | SIM | | NULL | Numero |
| `complement` | varchar(191) | SIM | | NULL | Complemento |
| `neighborhood` | varchar(191) | SIM | | NULL | Bairro |
| `city` | varchar(191) | SIM | | NULL | Cidade |
| `state` | varchar(191) | SIM | | NULL | Estado (UF) |
| `provider_id` | varchar(191) | SIM | | NULL | ID do provider OAuth (Socialite) |
| `provider_email` | varchar(191) | SIM | | NULL | E-mail do provider OAuth |
| `provider_avatar` | varchar(191) | SIM | | NULL | Avatar do provider OAuth |
| `created_by` | bigint(20) unsigned | SIM | | NULL | ID do usuario que criou |
| `updated_by` | bigint(20) unsigned | SIM | | NULL | ID do usuario que atualizou |
| `deleted_by` | bigint(20) unsigned | SIM | | NULL | ID do usuario que excluiu |
| `created_at` | timestamp | SIM | | NULL | Data de criacao |
| `updated_at` | timestamp | SIM | | NULL | Data de atualizacao |
| `deleted_at` | timestamp | SIM | | NULL | Soft delete |

**Indices:** `PRIMARY (id)`, `UNIQUE (email)`

**Observacoes:**
- Paciente adulto: `allow=true` + sem role de profissional
- Soft delete via `deleted_at`
- Campos de auditoria: `created_by`, `updated_by`, `deleted_by`

---

### kids

> Criancas - pacientes infantis cadastrados para avaliacao cognitiva

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `user_id` | bigint(20) unsigned | SIM | FK | NULL | Usuario que cadastrou |
| `responsible_id` | bigint(20) unsigned | SIM | FK | NULL | Responsavel pela crianca |
| `name` | varchar(191) | NAO | | | Nome da crianca |
| `gender` | enum('M','F') | SIM | | NULL | Sexo: M=Masculino, F=Feminino |
| `ethnicity` | enum('branco','pardo','negro','indigena','amarelo','multiracial','nao_declarado','outro') | SIM | | NULL | Etnia |
| `birth_date` | date | NAO | | | Data de nascimento |
| `photo` | varchar(191) | SIM | | NULL | Caminho da foto |
| `created_by` | bigint(20) unsigned | SIM | | NULL | ID do usuario que criou |
| `updated_by` | bigint(20) unsigned | SIM | | NULL | ID do usuario que atualizou |
| `deleted_by` | bigint(20) unsigned | SIM | | NULL | ID do usuario que excluiu |
| `created_at` | timestamp | SIM | | NULL | Data de criacao |
| `updated_at` | timestamp | SIM | | NULL | Data de atualizacao |
| `deleted_at` | timestamp | SIM | | NULL | Soft delete |

**Indices:** `PRIMARY (id)`, `FK (user_id)`, `FK (responsible_id)`

**Foreign Keys:**
| Coluna | Referencia | Descricao |
|--------|-----------|-----------|
| `user_id` | `users.id` | Usuario que cadastrou |
| `responsible_id` | `users.id` | Responsavel legal |

---

### professionals

> Profissionais de saude vinculados ao sistema

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `registration_number` | varchar(191) | SIM | | NULL | Numero de registro profissional (CRP, CRM, etc.) |
| `bio` | text | SIM | | NULL | Biografia/descricao profissional |
| `is_intern` | tinyint(1) | NAO | | 0 | Indica se e estagiario |
| `specialty_id` | bigint(20) unsigned | NAO | FK | | Especialidade do profissional |
| `created_by` | bigint(20) unsigned | SIM | FK | NULL | ID do usuario que criou |
| `updated_by` | bigint(20) unsigned | SIM | FK | NULL | ID do usuario que atualizou |
| `deleted_by` | bigint(20) unsigned | SIM | FK | NULL | ID do usuario que excluiu |
| `created_at` | timestamp | SIM | | NULL | Data de criacao |
| `updated_at` | timestamp | SIM | | NULL | Data de atualizacao |
| `deleted_at` | timestamp | SIM | | NULL | Soft delete |

**Indices:** `PRIMARY (id)`, `FK (specialty_id)`, `FK (created_by)`, `FK (updated_by)`, `FK (deleted_by)`

**Foreign Keys:**
| Coluna | Referencia | Descricao |
|--------|-----------|-----------|
| `specialty_id` | `specialties.id` | Especialidade |
| `created_by` | `users.id` | Criado por |
| `updated_by` | `users.id` | Atualizado por |
| `deleted_by` | `users.id` | Excluido por |

---

### professional_profiles

> Perfis profissionais (tabela legada, sem registros)

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `user_id` | bigint(20) unsigned | NAO | UNI/FK | | Usuario vinculado (1:1) |
| `specialty_id` | bigint(20) unsigned | NAO | FK | | Especialidade |
| `registration_number` | varchar(191) | SIM | | NULL | Numero de registro |
| `bio` | text | SIM | | NULL | Biografia |
| `created_by` | bigint(20) unsigned | SIM | FK | NULL | Criado por |
| `updated_by` | bigint(20) unsigned | SIM | FK | NULL | Atualizado por |
| `deleted_by` | bigint(20) unsigned | SIM | FK | NULL | Excluido por |
| `created_at` | timestamp | SIM | | NULL | Data de criacao |
| `updated_at` | timestamp | SIM | | NULL | Data de atualizacao |
| `deleted_at` | timestamp | SIM | | NULL | Soft delete |

**Foreign Keys:**
| Coluna | Referencia |
|--------|-----------|
| `user_id` | `users.id` |
| `specialty_id` | `specialties.id` |
| `created_by` | `users.id` |
| `updated_by` | `users.id` |
| `deleted_by` | `users.id` |

---

### specialties

> Especialidades dos profissionais (ex: Psicologia, Fonoaudiologia, etc.)

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `name` | varchar(191) | NAO | | | Nome da especialidade |
| `description` | text | SIM | | NULL | Descricao da especialidade |
| `created_by` | bigint(20) unsigned | SIM | FK | NULL | Criado por |
| `updated_by` | bigint(20) unsigned | SIM | FK | NULL | Atualizado por |
| `deleted_by` | bigint(20) unsigned | SIM | FK | NULL | Excluido por |
| `created_at` | timestamp | SIM | | NULL | Data de criacao |
| `updated_at` | timestamp | SIM | | NULL | Data de atualizacao |
| `deleted_at` | timestamp | SIM | | NULL | Soft delete |

**Foreign Keys:**
| Coluna | Referencia |
|--------|-----------|
| `created_by` | `users.id` |
| `updated_by` | `users.id` |
| `deleted_by` | `users.id` |

---

## Tabelas de Avaliacao Cognitiva

### checklists

> Avaliacoes cognitivas aplicadas a uma crianca

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `kid_id` | bigint(20) unsigned | SIM | FK | NULL | Crianca avaliada |
| `level` | enum('1','2','3','4') | NAO | | '1' | Nivel da avaliacao |
| `situation` | char(191) | NAO | | 'a' | Situacao: a=aberto, f=finalizado |
| `description` | text | SIM | | NULL | Descricao/observacoes |
| `created_by` | bigint(20) unsigned | SIM | | NULL | Profissional que criou |
| `updated_by` | bigint(20) unsigned | SIM | | NULL | Profissional que atualizou |
| `deleted_by` | bigint(20) unsigned | SIM | | NULL | Profissional que excluiu |
| `created_at` | timestamp | SIM | | NULL | Data de criacao |
| `updated_at` | timestamp | SIM | | NULL | Data de atualizacao |
| `deleted_at` | timestamp | SIM | | NULL | Soft delete |

**Indices:** `PRIMARY (id)`, `FK (kid_id)`

**Foreign Keys:**
| Coluna | Referencia |
|--------|-----------|
| `kid_id` | `kids.id` |

---

### competences

> Competencias cognitivas avaliadas no checklist

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `level_id` | bigint(20) unsigned | SIM | FK | NULL | Nivel ao qual pertence |
| `domain_id` | bigint(20) unsigned | SIM | FK | NULL | Dominio ao qual pertence |
| `code` | int(11) | NAO | | | Codigo numerico da competencia |
| `description` | text | NAO | | | Descricao da competencia |
| `description_detail` | text | NAO | | | Descricao detalhada |
| `percentil_25` | int(11) | SIM | | NULL | Idade em meses - percentil 25 |
| `percentil_50` | int(11) | SIM | | NULL | Idade em meses - percentil 50 |
| `percentil_75` | int(11) | SIM | | NULL | Idade em meses - percentil 75 |
| `percentil_90` | int(11) | SIM | | NULL | Idade em meses - percentil 90 |
| `created_at` | timestamp | SIM | | NULL | Data de criacao |
| `updated_at` | timestamp | SIM | | NULL | Data de atualizacao |

**Indices:** `PRIMARY (id)`, `FK (level_id)`, `FK (domain_id)`

**Foreign Keys:**
| Coluna | Referencia |
|--------|-----------|
| `level_id` | `levels.id` |
| `domain_id` | `domains.id` |

---

### checklist_competence

> Tabela pivot: notas atribuidas a cada competencia em um checklist

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `checklist_id` | bigint(20) unsigned | NAO | FK | | Checklist avaliado |
| `competence_id` | bigint(20) unsigned | NAO | FK | | Competencia avaliada |
| `note` | int(11) | NAO | | | Nota: 0=Nao testado, 1=Emergente, 2=Inconsistente, 3=Consistente |

**Indices:** `FK (checklist_id)`, `FK (competence_id)`

**Foreign Keys:**
| Coluna | Referencia |
|--------|-----------|
| `checklist_id` | `checklists.id` |
| `competence_id` | `competences.id` |

**Escala de Notas:**
| Valor | Significado |
|-------|-------------|
| 0 | Nao testado |
| 1 | Emergente |
| 2 | Inconsistente |
| 3 | Consistente |

---

### levels

> Niveis de avaliacao cognitiva

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `level` | enum('1','2','3','4') | NAO | | | Numero do nivel |
| `name` | varchar(191) | NAO | | | Nome descritivo do nivel |
| `created_at` | timestamp | SIM | | NULL | Data de criacao |
| `updated_at` | timestamp | SIM | | NULL | Data de atualizacao |

**Indices:** `PRIMARY (id)`

---

### domains

> Dominios cognitivos (ex: Linguagem, Motor, Social, etc.)

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `name` | varchar(191) | NAO | UNI | | Nome do dominio |
| `initial` | varchar(191) | NAO | UNI | | Sigla/inicial do dominio |
| `color` | char(7) | NAO | | | Cor hexadecimal (ex: #FF5733) |
| `created_at` | timestamp | SIM | | NULL | Data de criacao |
| `updated_at` | timestamp | SIM | | NULL | Data de atualizacao |

**Indices:** `PRIMARY (id)`, `UNIQUE (name)`, `UNIQUE (initial)`

---

### domain_level

> Tabela pivot: quais dominios pertencem a quais niveis

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `domain_id` | bigint(20) unsigned | NAO | FK | | Dominio |
| `level_id` | bigint(20) unsigned | NAO | FK | | Nivel |

**Indices:** `FK (domain_id)`, `FK (level_id)`

**Foreign Keys:**
| Coluna | Referencia |
|--------|-----------|
| `domain_id` | `domains.id` |
| `level_id` | `levels.id` |

---

## Tabelas de Planos de Desenvolvimento

### planes

> Planos de desenvolvimento para criancas baseados em checklists

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `kid_id` | bigint(20) unsigned | SIM | FK | NULL | Crianca do plano |
| `checklist_id` | bigint(20) unsigned | NAO | FK | | Checklist base do plano |
| `name` | varchar(191) | SIM | | NULL | Nome do plano |
| `is_active` | tinyint(1) | NAO | | 1 | Se o plano esta ativo |
| `created_by` | bigint(20) unsigned | SIM | | NULL | Criado por |
| `updated_by` | bigint(20) unsigned | SIM | | NULL | Atualizado por |
| `deleted_by` | bigint(20) unsigned | SIM | | NULL | Excluido por |
| `created_at` | timestamp | SIM | | NULL | Data de criacao |
| `updated_at` | timestamp | SIM | | NULL | Data de atualizacao |
| `deleted_at` | timestamp | SIM | | NULL | Soft delete |

**Indices:** `PRIMARY (id)`, `FK (kid_id)`, `FK (checklist_id)`

**Foreign Keys:**
| Coluna | Referencia |
|--------|-----------|
| `kid_id` | `kids.id` |
| `checklist_id` | `checklists.id` |

---

### competence_plane

> Tabela pivot: competencias incluidas em cada plano

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `plane_id` | bigint(20) unsigned | NAO | FK | | Plano de desenvolvimento |
| `competence_id` | bigint(20) unsigned | NAO | FK | | Competencia incluida |

**Foreign Keys:**
| Coluna | Referencia |
|--------|-----------|
| `plane_id` | `planes.id` |
| `competence_id` | `competences.id` |

---

## Tabelas de Prontuarios e Documentos

### medical_records

> Prontuarios medicos com suporte polimorficos (criancas e adultos) e versionamento

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `parent_id` | bigint(20) unsigned | SIM | FK/IDX | NULL | ID do registro pai (versionamento) |
| `version` | int(11) | NAO | | 1 | Numero da versao |
| `is_current_version` | tinyint(1) | NAO | IDX | 1 | Se e a versao atual |
| `patient_type` | varchar(191) | NAO | IDX | | Tipo polimorficos: `App\Models\Kid` ou `App\Models\User` |
| `patient_id` | bigint(20) unsigned | NAO | IDX | | ID do paciente (kid ou user) |
| `session_date` | date | NAO | IDX | | Data da sessao/atendimento |
| `complaint` | text | NAO | | | Queixa do paciente |
| `objective_technique` | text | NAO | | | Objetivo e tecnica utilizada |
| `evolution_notes` | text | NAO | | | Notas de evolucao |
| `referral_closure` | text | SIM | | NULL | Encaminhamento/encerramento |
| `html_content` | longtext | SIM | | NULL | Conteudo HTML renderizado para PDF |
| `created_by` | bigint(20) unsigned | NAO | FK/IDX | | Profissional que criou |
| `updated_by` | bigint(20) unsigned | SIM | FK | NULL | Profissional que atualizou |
| `deleted_by` | bigint(20) unsigned | SIM | FK | NULL | Profissional que excluiu |
| `created_at` | timestamp | SIM | | NULL | Data de criacao |
| `updated_at` | timestamp | SIM | | NULL | Data de atualizacao |
| `deleted_at` | timestamp | SIM | | NULL | Soft delete |

**Indices:**
- `PRIMARY (id)`
- `IDX (parent_id)`
- `IDX (parent_id, version)` - Composto para versionamento
- `IDX (patient_type, patient_id)` - Polimorficos
- `IDX (patient_id, patient_type, session_date)` - Composto para busca
- `IDX (session_date)`
- `IDX (is_current_version)`
- `FK (created_by)`, `FK (updated_by)`, `FK (deleted_by)`

**Foreign Keys:**
| Coluna | Referencia | Descricao |
|--------|-----------|-----------|
| `parent_id` | `medical_records.id` | Auto-referencia (versionamento) |
| `created_by` | `users.id` | Profissional autor |
| `updated_by` | `users.id` | Profissional que editou |
| `deleted_by` | `users.id` | Profissional que excluiu |

---

### generated_documents

> Documentos gerados (declaracoes, laudos, etc.) com suporte polimorficos

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `model_type` | tinyint(4) | NAO | IDX | | Tipo de documento (numerico) |
| `documentable_type` | varchar(191) | NAO | IDX | | Tipo polimorficos: `App\Models\Kid` ou `App\Models\User` |
| `documentable_id` | bigint(20) unsigned | NAO | IDX | | ID do paciente |
| `professional_id` | bigint(20) unsigned | SIM | FK/IDX | NULL | Profissional responsavel |
| `generated_by` | bigint(20) unsigned | NAO | FK/IDX | | Usuario que gerou o documento |
| `html_content` | longtext | NAO | | | Conteudo HTML do documento |
| `form_data` | longtext | SIM | | NULL | Dados do formulario (JSON) |
| `metadata` | longtext | SIM | | NULL | Metadados adicionais (JSON) |
| `generated_at` | timestamp | NAO | IDX | current_timestamp() | Data/hora de geracao |
| `created_by` | bigint(20) unsigned | SIM | FK | NULL | Criado por |
| `updated_by` | bigint(20) unsigned | SIM | FK | NULL | Atualizado por |
| `deleted_by` | bigint(20) unsigned | SIM | FK | NULL | Excluido por |
| `created_at` | timestamp | SIM | | NULL | Data de criacao |
| `updated_at` | timestamp | SIM | | NULL | Data de atualizacao |
| `deleted_at` | timestamp | SIM | | NULL | Soft delete |

**Indices:**
- `PRIMARY (id)`
- `IDX (documentable_type, documentable_id)` - Polimorficos
- `IDX (documentable_id, documentable_type)` - Polimorficos (invertido)
- `IDX (model_type)`, `IDX (professional_id)`, `IDX (generated_by)`, `IDX (generated_at)`
- `FK (created_by)`, `FK (updated_by)`, `FK (deleted_by)`

**Foreign Keys:**
| Coluna | Referencia |
|--------|-----------|
| `professional_id` | `professionals.id` |
| `generated_by` | `users.id` |
| `created_by` | `users.id` |
| `updated_by` | `users.id` |
| `deleted_by` | `users.id` |

---

### document_templates

> Templates para geracao de documentos

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `name` | varchar(191) | NAO | | | Nome do template |
| `type` | varchar(191) | NAO | | | Tipo do documento |
| `html_content` | longtext | NAO | | | Conteudo HTML do template |
| `description` | text | SIM | | NULL | Descricao do template |
| `available_placeholders` | longtext | NAO | | | Placeholders disponiveis (JSON) |
| `version` | varchar(191) | NAO | | '1.0' | Versao do template |
| `is_active` | tinyint(1) | NAO | | 1 | Se o template esta ativo |
| `created_at` | timestamp | SIM | | NULL | Data de criacao |
| `updated_at` | timestamp | SIM | | NULL | Data de atualizacao |
| `deleted_at` | timestamp | SIM | | NULL | Soft delete |

**Indices:** `PRIMARY (id)`

---

## Tabelas Pivot de Relacionamento

### kid_professional

> Vinculo entre criancas e profissionais responsaveis

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `kid_id` | bigint(20) unsigned | NAO | FK/UNI | | Crianca |
| `professional_id` | bigint(20) unsigned | NAO | FK/UNI | | Profissional |
| `is_primary` | tinyint(1) | NAO | | 0 | Se e o profissional primario |
| `created_at` | timestamp | SIM | | NULL | Data de criacao |
| `updated_at` | timestamp | SIM | | NULL | Data de atualizacao |

**Indices:** `PRIMARY (id)`, `UNIQUE (kid_id, professional_id)`

**Foreign Keys:**
| Coluna | Referencia |
|--------|-----------|
| `kid_id` | `kids.id` |
| `professional_id` | `professionals.id` |

---

### user_professional

> Vinculo 1:1 entre usuario e profissional

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `user_id` | bigint(20) unsigned | NAO | UNI/FK | | Usuario |
| `professional_id` | bigint(20) unsigned | NAO | FK | | Profissional |
| `created_at` | timestamp | SIM | | NULL | Data de criacao |
| `updated_at` | timestamp | SIM | | NULL | Data de atualizacao |

**Indices:** `PRIMARY (id)`, `UNIQUE (user_id)`

**Foreign Keys:**
| Coluna | Referencia |
|--------|-----------|
| `user_id` | `users.id` |
| `professional_id` | `professionals.id` |

---

### professional_user_patient

> Vinculo entre profissionais e pacientes adultos (usuarios)

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `professional_id` | bigint(20) unsigned | NAO | FK/UNI | | Profissional |
| `user_id` | bigint(20) unsigned | NAO | FK/UNI | | Paciente adulto (usuario) |
| `is_primary` | tinyint(1) | NAO | | 0 | Se e o profissional primario |
| `created_at` | timestamp | SIM | | NULL | Data de criacao |
| `updated_at` | timestamp | SIM | | NULL | Data de atualizacao |

**Indices:** `PRIMARY (id)`, `UNIQUE (professional_id, user_id)`

**Foreign Keys:**
| Coluna | Referencia |
|--------|-----------|
| `professional_id` | `professionals.id` |
| `user_id` | `users.id` |

---

## Tabelas de Autorizacao (Spatie Permission)

### roles

> Papeis do sistema (agrupadores de permissoes)

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `name` | varchar(191) | NAO | UNI* | | Nome do papel |
| `guard_name` | varchar(191) | NAO | UNI* | | Guard (web, api) |
| `created_by` | bigint(20) unsigned | SIM | | NULL | Criado por |
| `updated_by` | bigint(20) unsigned | SIM | | NULL | Atualizado por |
| `deleted_by` | bigint(20) unsigned | SIM | | NULL | Excluido por |
| `created_at` | timestamp | SIM | | NULL | Data de criacao |
| `updated_at` | timestamp | SIM | | NULL | Data de atualizacao |
| `deleted_at` | timestamp | SIM | | NULL | Soft delete |

**Indices:** `PRIMARY (id)`, `UNIQUE (name, guard_name)`

---

### permissions

> Permissoes individuais do sistema

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `name` | varchar(191) | NAO | UNI* | | Nome da permissao (ex: `user-edit`) |
| `guard_name` | varchar(191) | NAO | UNI* | | Guard (web, api) |
| `created_at` | timestamp | SIM | | NULL | Data de criacao |
| `updated_at` | timestamp | SIM | | NULL | Data de atualizacao |

**Indices:** `PRIMARY (id)`, `UNIQUE (name, guard_name)`

**Padrao de nomenclatura:** `{entidade}-{acao}[-all]`
- Exemplo: `user-list`, `user-edit`, `user-list-all`, `kid-create`

---

### role_has_permissions

> Pivot: permissoes atribuidas a cada papel

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `permission_id` | bigint(20) unsigned | NAO | PK/FK | | Permissao |
| `role_id` | bigint(20) unsigned | NAO | PK/FK | | Papel |

**Indices:** `PRIMARY (permission_id, role_id)`

**Foreign Keys:**
| Coluna | Referencia |
|--------|-----------|
| `permission_id` | `permissions.id` |
| `role_id` | `roles.id` |

---

### model_has_roles

> Pivot polimorficos: papeis atribuidos a modelos (usuarios)

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `role_id` | bigint(20) unsigned | NAO | PK/FK | | Papel |
| `model_type` | varchar(191) | NAO | PK | | Tipo do model (ex: `App\Models\User`) |
| `model_id` | bigint(20) unsigned | NAO | PK | | ID do model |

**Indices:** `PRIMARY (role_id, model_id, model_type)`, `IDX (model_id, model_type)`

**Foreign Keys:**
| Coluna | Referencia |
|--------|-----------|
| `role_id` | `roles.id` |

---

### model_has_permissions

> Pivot polimorficos: permissoes diretas atribuidas a modelos

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `permission_id` | bigint(20) unsigned | NAO | PK/FK | | Permissao |
| `model_type` | varchar(191) | NAO | PK | | Tipo do model |
| `model_id` | bigint(20) unsigned | NAO | PK | | ID do model |

**Indices:** `PRIMARY (permission_id, model_id, model_type)`, `IDX (model_id, model_type)`

**Foreign Keys:**
| Coluna | Referencia |
|--------|-----------|
| `permission_id` | `permissions.id` |

---

## Tabelas de Auditoria e Logs

### logs

> Logs de auditoria de acoes no sistema

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `object` | varchar(191) | SIM | | NULL | Entidade/objeto afetado |
| `object_id` | int(11) | SIM | | NULL | ID do objeto afetado |
| `action` | enum('insert','update','remove','info') | NAO | | | Tipo de acao |
| `description` | text | NAO | | | Descricao da acao |
| `creation_date` | datetime | NAO | | | Data de criacao do registro |
| `created_by` | int(11) | SIM | | NULL | Usuario que executou a acao |
| `modification_date` | datetime | SIM | | NULL | Data de modificacao |
| `modified_by` | int(11) | SIM | | NULL | Usuario que modificou |
| `removal_date` | datetime | SIM | | NULL | Data de remocao |
| `removed_by` | int(11) | SIM | | NULL | Usuario que removeu |
| `created_at` | timestamp | SIM | | NULL | Timestamp Laravel |
| `updated_at` | timestamp | SIM | | NULL | Timestamp Laravel |

**Indices:** `PRIMARY (id)`

---

## Tabelas de Infraestrutura Laravel

### sessions

> Sessoes de usuario (driver database)

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | varchar(191) | NAO | PK | | ID da sessao |
| `user_id` | bigint(20) unsigned | SIM | IDX | NULL | Usuario logado |
| `ip_address` | varchar(45) | SIM | | NULL | Endereco IP |
| `user_agent` | text | SIM | | NULL | User agent do navegador |
| `payload` | longtext | NAO | | | Dados serializados da sessao |
| `last_activity` | int(11) | NAO | IDX | | Timestamp da ultima atividade |

**Indices:** `PRIMARY (id)`, `IDX (user_id)`, `IDX (last_activity)`

---

### jobs

> Fila de jobs para processamento assincrono

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `queue` | varchar(191) | NAO | IDX | | Nome da fila |
| `payload` | longtext | NAO | | | Dados do job (serializado) |
| `attempts` | tinyint(3) unsigned | NAO | | | Numero de tentativas |
| `reserved_at` | int(10) unsigned | SIM | | NULL | Timestamp de reserva |
| `available_at` | int(10) unsigned | NAO | | | Timestamp de disponibilidade |
| `created_at` | int(10) unsigned | NAO | | | Timestamp de criacao |

**Indices:** `PRIMARY (id)`, `IDX (queue)`

---

### failed_jobs

> Jobs que falharam durante o processamento

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `uuid` | varchar(191) | NAO | UNI | | UUID unico do job |
| `connection` | text | NAO | | | Conexao utilizada |
| `queue` | text | NAO | | | Fila do job |
| `payload` | longtext | NAO | | | Dados do job |
| `exception` | longtext | NAO | | | Stack trace da excecao |
| `failed_at` | timestamp | NAO | | current_timestamp() | Data/hora da falha |

**Indices:** `PRIMARY (id)`, `UNIQUE (uuid)`

---

### password_resets

> Tokens para redefinicao de senha

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `email` | varchar(191) | NAO | IDX | | E-mail do usuario |
| `token` | varchar(191) | NAO | | | Token de reset (hash) |
| `created_at` | timestamp | SIM | | NULL | Data de criacao |

**Indices:** `IDX (email)`

---

### personal_access_tokens

> Tokens de acesso pessoal (Laravel Sanctum)

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | bigint(20) unsigned | NAO | PK | auto_increment | Identificador unico |
| `tokenable_type` | varchar(191) | NAO | IDX | | Tipo polimorficos do model |
| `tokenable_id` | bigint(20) unsigned | NAO | IDX | | ID do model |
| `name` | varchar(191) | NAO | | | Nome descritivo do token |
| `token` | varchar(64) | NAO | UNI | | Hash SHA-256 do token |
| `abilities` | text | SIM | | NULL | Habilidades/escopos (JSON) |
| `last_used_at` | timestamp | SIM | | NULL | Ultimo uso |
| `created_at` | timestamp | SIM | | NULL | Data de criacao |
| `updated_at` | timestamp | SIM | | NULL | Data de atualizacao |

**Indices:** `PRIMARY (id)`, `UNIQUE (token)`, `IDX (tokenable_type, tokenable_id)`

---

### migrations

> Controle de migrations executadas pelo Laravel

| Coluna | Tipo | Nulo | Chave | Default | Descricao |
|--------|------|------|-------|---------|-----------|
| `id` | int(10) unsigned | NAO | PK | auto_increment | Identificador unico |
| `migration` | varchar(191) | NAO | | | Nome do arquivo de migration |
| `batch` | int(11) | NAO | | | Numero do lote de execucao |

**Indices:** `PRIMARY (id)`

---

## Resumo de Foreign Keys

| Tabela Origem | Coluna | Tabela Destino | Coluna Destino |
|---------------|--------|----------------|----------------|
| `checklist_competence` | `checklist_id` | `checklists` | `id` |
| `checklist_competence` | `competence_id` | `competences` | `id` |
| `checklists` | `kid_id` | `kids` | `id` |
| `competences` | `domain_id` | `domains` | `id` |
| `competences` | `level_id` | `levels` | `id` |
| `competence_plane` | `competence_id` | `competences` | `id` |
| `competence_plane` | `plane_id` | `planes` | `id` |
| `domain_level` | `domain_id` | `domains` | `id` |
| `domain_level` | `level_id` | `levels` | `id` |
| `generated_documents` | `created_by` | `users` | `id` |
| `generated_documents` | `deleted_by` | `users` | `id` |
| `generated_documents` | `generated_by` | `users` | `id` |
| `generated_documents` | `professional_id` | `professionals` | `id` |
| `generated_documents` | `updated_by` | `users` | `id` |
| `kids` | `responsible_id` | `users` | `id` |
| `kids` | `user_id` | `users` | `id` |
| `kid_professional` | `kid_id` | `kids` | `id` |
| `kid_professional` | `professional_id` | `professionals` | `id` |
| `medical_records` | `created_by` | `users` | `id` |
| `medical_records` | `deleted_by` | `users` | `id` |
| `medical_records` | `parent_id` | `medical_records` | `id` |
| `medical_records` | `updated_by` | `users` | `id` |
| `model_has_permissions` | `permission_id` | `permissions` | `id` |
| `model_has_roles` | `role_id` | `roles` | `id` |
| `planes` | `checklist_id` | `checklists` | `id` |
| `planes` | `kid_id` | `kids` | `id` |
| `professional_profiles` | `created_by` | `users` | `id` |
| `professional_profiles` | `deleted_by` | `users` | `id` |
| `professional_profiles` | `specialty_id` | `specialties` | `id` |
| `professional_profiles` | `updated_by` | `users` | `id` |
| `professional_profiles` | `user_id` | `users` | `id` |
| `professional_user_patient` | `professional_id` | `professionals` | `id` |
| `professional_user_patient` | `user_id` | `users` | `id` |
| `professionals` | `created_by` | `users` | `id` |
| `professionals` | `deleted_by` | `users` | `id` |
| `professionals` | `specialty_id` | `specialties` | `id` |
| `professionals` | `updated_by` | `users` | `id` |
| `role_has_permissions` | `permission_id` | `permissions` | `id` |
| `role_has_permissions` | `role_id` | `roles` | `id` |
| `specialties` | `created_by` | `users` | `id` |
| `specialties` | `deleted_by` | `users` | `id` |
| `specialties` | `updated_by` | `users` | `id` |
| `user_professional` | `professional_id` | `professionals` | `id` |
| `user_professional` | `user_id` | `users` | `id` |

---

## Padroes Utilizados

### Soft Delete
Tabelas com `deleted_at`: `users`, `kids`, `checklists`, `planes`, `professionals`, `professional_profiles`, `specialties`, `roles`, `medical_records`, `generated_documents`, `document_templates`

### Auditoria (created_by / updated_by / deleted_by)
Tabelas com campos de auditoria: `users`, `kids`, `checklists`, `planes`, `professionals`, `professional_profiles`, `specialties`, `roles`, `medical_records`, `generated_documents`

### Relacionamentos Polimorficos
- **medical_records**: `patient_type` + `patient_id` → `Kid` ou `User`
- **generated_documents**: `documentable_type` + `documentable_id` → `Kid` ou `User`
- **model_has_roles**: `model_type` + `model_id` → `User` (Spatie)
- **model_has_permissions**: `model_type` + `model_id` → `User` (Spatie)
- **personal_access_tokens**: `tokenable_type` + `tokenable_id` → `User` (Sanctum)

### Timestamps Laravel
Todas as tabelas de dominio possuem `created_at` e `updated_at`.

### Enums
| Tabela | Coluna | Valores |
|--------|--------|---------|
| `users` | `type` | `i` (interno), `e` (externo) |
| `kids` | `gender` | `M` (Masculino), `F` (Feminino) |
| `kids` | `ethnicity` | `branco`, `pardo`, `negro`, `indigena`, `amarelo`, `multiracial`, `nao_declarado`, `outro` |
| `checklists` | `level` | `1`, `2`, `3`, `4` |
| `levels` | `level` | `1`, `2`, `3`, `4` |
| `logs` | `action` | `insert`, `update`, `remove`, `info` |
