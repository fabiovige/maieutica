# Rotas — Mapa Completo

> Referência de todas as rotas web e API do sistema Maieutica.

---

## Visao Geral

| Tipo | Arquivo | Middleware | Total aprox. |
|------|---------|------------|--------------|
| Web | `routes/web.php` | `auth`, `acl` | ~60 rotas |
| API | `routes/api.php` | `auth:sanctum` (definido, nao aplicado) | ~30 rotas |

---

## Rotas Web (`routes/web.php`)

### Autenticacao (middleware: `guest`)

| Metodo | URI | Controller | Descricao |
|--------|-----|------------|-----------|
| GET | `/login` | Auth\LoginController@showLoginForm | Formulario de login |
| POST | `/login` | Auth\LoginController@login | Processar login |
| POST | `/logout` | Auth\LoginController@logout | Logout |
| GET | `/password/reset` | Auth\ForgotPasswordController@showLinkRequestForm | Solicitar reset |
| POST | `/password/email` | Auth\ForgotPasswordController@sendResetLinkEmail | Enviar link reset |
| GET | `/password/reset/{token}` | Auth\ResetPasswordController@showResetForm | Formulario reset |
| POST | `/password/reset` | Auth\ResetPasswordController@reset | Processar reset |

### Root

| Metodo | URI | Descricao |
|--------|-----|-----------|
| GET | `/` | Redirect: autenticado -> `/home`, senao -> `/login` |

### Dashboard (middleware: `auth`)

| Metodo | URI | Controller | Descricao |
|--------|-----|------------|-----------|
| GET | `/home` | HomeController@index | Dashboard principal |

### CRUD Padrao (middleware: `auth`)

Cada resource segue o padrao: `resource CRUD` + `GET trash` + `POST {id}/restore`

#### Checklists
- `resource('checklists', ChecklistController)` — CRUD completo
- `GET checklists/trash` — Lixeira
- `POST checklists/{id}/restore` — Restaurar
- `POST checklists/{checklist}/register` — Registrar avaliacao
- `GET checklists/{checklist}/fill` — Preencher avaliacao
- `GET checklists/{checklist}/chart` — Grafico de resultados
- `POST checklists/{checklist}/clonar` — Clonar checklist

#### Kids (Pacientes)
- `resource('kids', KidController)` — CRUD completo
- `GET kids/trash` — Lixeira
- `POST kids/{id}/restore` — Restaurar
- `GET kids/{kid}/overview/{level?}` — Visao geral por nivel
- `GET kids/{kid}/pdf` — Exportar PDF
- `POST kids/{kid}/photo` — Upload de foto
- `GET kids/{kid}/radar-chart` — Grafico radar
- `GET kids/{kid}/domain/{domain}` — Detalhe por dominio

#### Roles
- `resource('roles', RoleController)` — CRUD completo
- `GET roles/trash` — Lixeira
- `POST roles/{id}/restore` — Restaurar

#### Users
- `resource('users', UserController)` — CRUD completo
- `GET users/trash` — Lixeira
- `POST users/{id}/restore` — Restaurar
- `GET users/{user}/pdf` — Exportar PDF

#### Competences
- `resource('competences', CompetenceController)` — CRUD completo
- `GET competences/domain/{domain}` — Filtrar por dominio
- `GET competences/clear-filters` — Limpar filtros

#### Professionals
- `resource('professionals', ProfessionalController)` — CRUD completo
- `GET professionals/trash` — Lixeira
- `POST professionals/{id}/restore` — Restaurar
- `POST professionals/{professional}/activate` — Ativar
- `POST professionals/{professional}/deactivate` — Desativar
- `GET professionals/{professional}/assign` — Formulario de atribuicao de pacientes
- `POST professionals/{professional}/sync-patients` — Sincronizar pacientes

#### Medical Records (Prontuarios)
- `resource('medical-records', MedicalRecordController)` — CRUD completo
- `GET medical-records/trash` — Lixeira
- `POST medical-records/{id}/restore` — Restaurar
- `GET medical-records/{medicalRecord}/pdf` — Exportar PDF
- `GET medical-records/patient/{patient}/history` — Historico do paciente
- `GET medical-records/{medicalRecord}/versions` — Historico de versoes

### Perfil do Usuario
- `GET profile/edit` — Editar perfil
- `PUT profile/update-password` — Atualizar senha
- `POST profile/avatar` — Upload de avatar

### Tutorial
- `GET tutorial` — Indice
- `GET tutorial/users` — Tutorial de usuarios
- `GET tutorial/checklists` — Tutorial de checklists

### Documentos Gerados (6 modelos)
- `GET documents/{type}/form/{patientId}` — Formulario de geracao
- `POST documents/{type}/generate/{patientId}` — Gerar documento
- `GET documents/{type}/history/{patientId}` — Historico
- `GET documents/{id}/pdf` — Download PDF

### Analise (Charts)
- `GET analysis/radar/{checklist1}/{checklist2}` — Comparar 2 checklists
- `GET analysis/{kid}/domain/{domain}` — Detalhe dominio
- `GET analysis/{kid}/pdf` — PDF de analise

### Releases
- `GET releases` — Lista de versoes
- `GET releases/{release}` — Detalhe da versao

### Health Check (sem auth)
- `GET /health` — Status JSON (database, cache, disk, queue)

### DataTable Ajax
- `GET */datatable/index` — Endpoints AJAX para DataTables (checklists, kids, roles, users)

### Documentacao
- `GET /docs/{page}` — Paginas de documentacao dinamica

---

## Rotas API (`routes/api.php`)

Todas as rotas API retornam JSON. Usadas pelos componentes Vue dentro das views Blade.

### Resources API

| Resource | Endpoints | Extras |
|----------|-----------|--------|
| Levels | apiResource completo | — |
| Domains | apiResource completo | `GET domains/initials` |
| Competences | apiResource completo | — |
| Checklists | apiResource completo | — |
| Kids | apiResource completo | `GET kids/byuser` |
| Planes | apiResource completo | `POST newPlane`, `DELETE deletePlane`, `POST storePlane`, `GET showCompetences/{plane}`, `GET showByKids/{kid}` |
| ChecklistRegisters | apiResource completo | `POST storeSingle`, `GET progressbar/{checklist}` |

### Endpoints Especiais API

| Metodo | URI | Descricao |
|--------|-----|-----------|
| GET | `/api/charts/percentage/{checklist}` | Percentual por checklist |
| GET | `/api/checklists/{checklist}/competences/{note}` | Competencias por nota |

---

## Padrao de Nomenclatura

### Rotas Web
- **Formato:** `{resource}.{action}` (ex: `kids.index`, `kids.show`, `kids.trash`)
- **Trash/Restore:** Sempre `{resource}/trash` e `{resource}/{id}/restore`
- **PDF:** `{resource}/{id}/pdf`

### Rotas API
- **Formato:** `api/{resource}` (RESTful padrao)
- **Customizadas:** Verbos descritivos (ex: `newPlane`, `storeSingle`, `progressbar`)

---

## Middleware Stack

### Web
1. `web` (sessao, CSRF, cookies)
2. `auth` (autenticacao — maioria das rotas)
3. `acl` (AclMiddleware — verificacao de `allow`)

### API
1. `api` (throttle, stateless)
2. `auth:sanctum` (definido no grupo, mas rotas atuais nao aplicam)

---

## Convencoes Importantes

- **Nao existe versionamento de API** — todas as rotas sob `/api/` diretamente
- **DataTables** usam rotas AJAX separadas (`*/datatable/index`)
- **Documentos** usam `{type}` dinamico para os 6 modelos de documento
- **Overview** aceita `{level?}` opcional para filtrar por nivel
- **Health check** e a unica rota publica (sem auth)
