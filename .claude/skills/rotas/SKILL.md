---
description: Mapa completo de rotas web e API, middleware stack, padrões de URL
---

Leia `docs/rotas.md` na íntegra. Use-o para responder perguntas sobre rotas, endpoints, URLs e navegação do sistema.

## Mapa Rápido de Rotas

**Web (middleware: auth):**
- Resources com CRUD + trash + restore: `checklists`, `kids`, `roles`, `users`, `competences`, `professionals`, `medical-records`
- Perfil: `profile/edit`, `profile/update-password`, `profile/avatar`
- Documentos: `documents/{type}/form/{patientId}`, `documents/{type}/generate/{patientId}`
- Análise: `analysis/radar/{c1}/{c2}`, `analysis/{kid}/domain/{domain}`
- Tutorial: `tutorial`, `tutorial/users`, `tutorial/checklists`
- Releases: `releases`, `releases/{release}`
- Health: `GET /health` (sem auth)

**API (JSON para Vue):**
- apiResource: `levels`, `domains`, `competences`, `checklists`, `kids`, `planes`, `checklist-registers`
- Extras: `charts/percentage/{checklist}`, `checklists/{id}/competences/{note}`
- Planes: `newPlane`, `deletePlane`, `storePlane`, `showCompetences/{plane}`, `showByKids/{kid}`
- Registers: `storeSingle`, `progressbar/{checklist}`

**Padrão recorrente:** `resource CRUD` + `GET trash` + `POST {id}/restore` + rotas especializadas (PDF, chart, overview)

## Rotas Especiais por Entidade

- **Kids:** `overview/{level?}`, `pdf`, `photo`, `radar-chart`, `domain/{domain}`
- **Checklists:** `register`, `fill`, `chart`, `clonar`
- **Professionals:** `activate`, `deactivate`, `assign`, `sync-patients`
- **Medical Records:** `pdf`, `patient/{patient}/history`, `{id}/versions`
- **DataTables:** `*/datatable/index` (AJAX para listagens)

## Middleware Stack

- Web: `web` → `auth` → `acl` (AclMiddleware)
- API: `api` → `auth:sanctum` (definido mas não aplicado nas rotas atuais)
- Sem auth: apenas `GET /health` e rotas de login/reset
