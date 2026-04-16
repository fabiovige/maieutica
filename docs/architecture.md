# Arquitetura do Maiêutica

## Domínio Central

O sistema gira em torno de **checklists de avaliação cognitiva** para crianças (Kids) e **prontuários** para crianças e adultos:

```
Kid (Criança) ─────────────────────────────────────────────────────────────
 ├── has many Checklists (avaliações com Competências notas 0-3)
 ├── belongs to Professionals (via kid_professional pivot)
 ├── has many Planes (planos de desenvolvimento)
 ├── has many MedicalRecords (morphMany)
 └── has many GeneratedDocuments (morphMany)

User (pode ser Paciente Adulto quando allow=true E sem role profissional)
 ├── has many MedicalRecords (morphMany)
 └── has many GeneratedDocuments (morphMany)

Checklist → checklist_competence (pivot com nota 0-3) → Competence → Domain/Level
```

**Escala de notas:** 0=Não testado, 1=Emergente, 2=Inconsistente, 3=Consistente

---

## Modelos (24 total + BaseModel)

| Categoria | Modelos |
|-----------|---------|
| **Usuários & Auth** | `User`, `Professional`, `ProfessionalProfile`, `Specialty`, `Address` |
| **Pacientes** | `Kid`, `Responsible` |
| **Avaliação** | `Checklist`, `ChecklistCompetence`, `Competence`, `Level`, `Domain`, `DomainLevel` |
| **Planos** | `Plane`, `CompetencePlane` |
| **Registros** | `MedicalRecord` (polimórfico), `GeneratedDocument` (polimórfico) |
| **Permissões** | `Role`, `Ability`, `AbilityRole`, `Resource` |
| **Logging** | `Log` |
| **Sistema** | `Release` (versões/changelog do sistema) |

> `BaseModel` é classe base abstrata (inclui SoftDeletes + audit fields).

---

## Controllers

### Web Controllers (16) — `app/Http/Controllers/`

| Controller | Responsabilidade |
|------------|-----------------|
| `KidsController` | CRUD kids, overview, PDF, gráficos, análise level/domain |
| `ChecklistController` | CRUD checklists, formulário de preenchimento, clone, visualização de gráfico |
| `ProfessionalController` | CRUD profissionais, atribuir pacientes, ativar/desativar |
| `UserController` | CRUD usuários, lixeira, PDF |
| `RoleController` | CRUD roles, lixeira |
| `CompetencesController` | CRUD competências, filtro por level/domain |
| `MedicalRecordsController` | CRUD prontuários (polimórfico), PDF, lixeira |
| `DocumentsController` | 6 modelos de documentos, geração de PDF, histórico |
| `PlaneAutomaticController` | Geração automática de plano de desenvolvimento |
| `ProfileController` | Editar perfil, trocar senha, upload de avatar |
| `TutorialController` | Páginas de tutorial |
| `HomeController` | Dashboard |
| `AddressController` | Gestão de endereço/CEP |
| `DocumentationController` | Documentação dinâmica |
| `ReleaseController` | Changelog/release notes do sistema |

### API Controllers (8) — `app/Http/Controllers/Api/`

| Controller | Endpoints |
|------------|-----------|
| `LevelController` | Resource API para levels |
| `DomainController` | Resource API + getInitials |
| `CompetenceController` | Resource API para competências |
| `ChecklistController` | Resource API + getCompetencesByNote |
| `KidController` | Resource API + byuser |
| `PlaneController` | new, delete, store, showCompetences, showByKids |
| `ChecklistRegisterController` | storeSingle, progressbar |
| `ChartController` | cálculos de percentagem |

---

## Services

- `ChecklistService` — Lógica de avaliação, cálculos
- `OverviewService` — Resumo de progresso e overview

---

## Observers (6)

| Observer | Propósito |
|----------|-----------|
| `KidObserver` | Eventos de criação/exclusão/restauração de kid |
| `ChecklistObserver` | Mudanças em checklist |
| `UserObserver` | Notificações/emails de usuário |
| `ProfessionalObserver` | Logging de profissional |
| `RoleObserver` | Logging de mudanças de permissão |
| `ResponsibleObserver` | Logging de mudanças em responsável |

---

## Jobs & Notifications

**Jobs:** `SendKidUpdateJob` — Job de fila para notificações de atualização de kid

**Notifications:**
- `KidUpdateNotification` — Notificação de atualização
- `WelcomeNotification` — Notificação de boas-vindas

**Mails:** `UserCreatedMail`, `UserUpdatedMail`, `UserDeletedMail`

**Templates de E-mail** (`resources/views/emails/`):
- `layout.blade.php` — Layout base (header rosa `#AD6E9B`, corpo neutro, footer minimalista)
- `user_created.blade.php` — Boas-vindas com dados de acesso e senha provisória
- `user_updated.blade.php` — Notificação de dados atualizados
- `user_deleted.blade.php` — Notificação de conta desativada

**Design dos e-mails:** Visual limpo e institucional, sem emojis, tipografia em cinzas neutros, dados em tabelas alinhadas, botão CTA rosa. Todos usam fila (`ShouldQueue`). Após alterar templates: `php artisan view:clear`.

---

## Helpers & Utilities

**Funções** (`app/helpers.php`):
```php
label_case($text)                  // "snake_case" → "Label Case"
get_progress_color($percentage)    // Retorna cor HTML por %
get_progress_gradient($percentage) // Retorna gradiente CSS
get_chart_gradient($percentage)    // Retorna gradiente para gráfico
```

**Enums:** `ProgressColors` — Lógica de cor por percentagem

---

## Middleware (10)

| Middleware | Propósito |
|------------|-----------|
| `AclMiddleware` | Controle de acesso |
| `Authenticate` | Verificação de autenticação |
| `SecurityHeaders` | Headers de segurança |
| `VerifyCsrfToken` | Proteção CSRF |
| `EncryptCookies` | Criptografia de cookies |
| `TrimStrings` | Limpeza de inputs |
| `TrustHosts` / `TrustProxies` | Confiança em proxy/host |
| `RedirectIfAuthenticated` | Redirect de visitantes |
| `PreventRequestsDuringMaintenance` | Modo de manutenção |

---

## Blade Components

| Component | Path | Propósito |
|-----------|------|-----------|
| `kid-info-card` | `resources/views/components/kid-info-card.blade.php` | Card de informações da criança |
| `table-actions` | `resources/views/components/table-actions.blade.php` | Componente reutilizável para botões de ação em tabelas |

---

## Features Principais

**Fluxo de Avaliação:** Profissional cria Checklist → avalia Competências (0-3) → sistema calcula % por Domínio → gera gráficos radar → pode clonar para rastreamento longitudinal

**Prontuários:** Polimórfico (`patient_type` = `App\Models\Kid` ou `App\Models\User`), usa scope `forAuthProfessional()` para filtrar. Ver `docs/medical-records.md`.

**Geração de Documentos:** HTML armazenado em `generated_documents` → PDF gerado sob demanda via DomPDF. Usa CSS inline, imagens base64. Ver `docs/documentos.md`.

**Pacientes Adultos:** User com `allow=true` E sem role profissional. Atribuição Profissional→Usuário parcialmente implementada. Ver `docs/analise_adulto.md`.

**Análise Level/Domain:** Gráficos radar comparativos em `/analysis/{kidId}/level/{levelId}` para rastreamento visual de progresso.

---

## Estatísticas do Projeto

| Métrica | Valor |
|---------|-------|
| Models | 23 |
| Controllers | 23 (15 web + 8 API) |
| Vue Components | 9 |
| Composables | 9 |
| Blade Templates | ~150 |
| Migrations | 19 |
| Tests | 20 |
| Policies | 10 |
| Observers | 6 |
| Arquivos de Documentação | 20+ |
| Tabelas do Banco | 31 |
| Arquivos SCSS | 7 |
| Arquivos CSS (public) | 4 (app.css, custom.css, typography.css, cep-autocomplete.css) |

---

## Schema do Banco (Resumo)

> Dicionário completo: `docs/dicionario-dados.md`

**Padrões comuns:**
- **Soft Delete** (`deleted_at`): `users`, `kids`, `checklists`, `planes`, `professionals`, `roles`, `medical_records`, `generated_documents`, `document_templates`
- **Campos de auditoria** (`created_by`, `updated_by`, `deleted_by`): `users`, `kids`, `checklists`, `planes`, `professionals`, `roles`, `medical_records`, `generated_documents`
- **Relações polimórficas**: `medical_records`, `generated_documents`, `model_has_roles/permissions`, `personal_access_tokens`
- **Enums**: `users.type` (i/e), `kids.gender` (M/F), `kids.ethnicity` (8 valores), `checklists.level` (1-4), `logs.action` (insert/update/remove/info)
