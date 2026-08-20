# Plano: Agendamentos (WhatsApp -> N8N -> Google Calendar -> Maieutica)

## Contexto

A clinica quer captar e confirmar agendamentos sem depender de atendimento manual por telefone/WhatsApp. O fluxo escolhido usa o Google Calendar como intermediario: um agente N8N conversa com o paciente no WhatsApp, cria o evento no Calendar, e o Maieutica espelha esses eventos para dentro do sistema, onde a recepcao confirma e vincula ao profissional correto.

**Divisao de responsabilidade, deliberada desde o desenho:**
- **Google Calendar** e dono da existencia e do horario do evento (criado pelo N8N, ou manualmente pela clinica).
- **Maieutica** e dono do estado de confirmacao (`situation`) e do vinculo com `professionals` — o comando de sincronizacao nunca sobrescreve esses dois campos.

Esta spec cobre o fluxo completo em etapas. As etapas sao implementadas e documentadas conforme avancam — a lista abaixo cresce a cada nova etapa combinada com o usuario.

---

## Resumo das Etapas

| # | Etapa | Status |
|---|-------|--------|
| 1 | Agendamento via WhatsApp (N8N cria o evento no Google Calendar) | Implementado |
| 2 | Listagem de agendamentos no sistema (sincronizacao + tela de confirmacao) | Implementado |
| 3 | Webhook de confirmacao Maieutica -> N8N | Parcial: emissor implementado; workflow N8N pendente |
| 4+ | A definir | Pendente |

---

## Etapa 1 — Agendamento via WhatsApp (Implementado)

Um agente N8N conduz a conversa no WhatsApp, coleta os dados do paciente (nome, contato, motivo, especialidade/profissional desejado) e cria o evento no Google Calendar da clinica. Para decidir horarios e profissionais disponiveis, o fluxo consome o endpoint restrito `GET /api/integrations/professionals` (`App\Http\Controllers\Api\IntegrationController`), autenticado por token Sanctum com ability propria emitido para a conta de servico do N8N (ver `App\Console\Commands\CreateN8nIntegrationToken`).

O evento e criado com:
- **summary**: `Agendamento: <nome do paciente>`
- **description**: texto livre com linhas rotuladas (`Motivo da consulta:`, `Especialidade:`, `Profissional:`, `E-mail do cliente:`, `Telefone:`) — formato consumido pelo parser da Etapa 2.

Exportado em `n8n/fluxo-atendimento.json`.

---

## Etapa 2 — Listagem de agendamentos no sistema (Implementado)

**Objetivo:** trazer os eventos criados na Etapa 1 para dentro do Maieutica, numa tela onde a recepcao confirma o agendamento e vincula o profissional real (o Calendar so tem o nome em texto livre).

### Modelo de dados

Tabela `appointments` (migration `2026_08_18_170508_create_appointments_table.php`), model `App\Models\Appointment` (extends `BaseModel` — `SoftDeletes` + audit fields):

- `google_event_id` (unique) — chave de correlacao com o evento no Calendar
- `starts_at`, `ends_at` — espelhados do Calendar
- `patient_name`, `patient_email`, `patient_phone`, `reason` — extraidos da descricao/titulo do evento pelo `AppointmentMapper`
- `specialty_raw`, `professional_raw` — texto livre do Calendar, apenas referencia para a recepcao conferir
- `professional_id` (nullable, FK `professionals`) — preenchido so na confirmacao, quando o texto livre vira vinculo real
- `situation` (char(1)) — `p` pendente, `c` confirmado, `r` recusado, `x` cancelado (ver `Appointment::SITUATION`)
- `confirmed_by` (FK `users`), `confirmed_at`

### Sincronizacao — `php artisan agenda:sync`

`App\Console\Commands\SyncGoogleCalendarAppointments`, agendado no scheduler a cada 5 minutos (`app/Console/Kernel.php`), **sem fila** — roda direto no scheduler porque `QUEUE_CONNECTION=sync` em producao e ha `failed_jobs` acumulados a investigar antes de reativar workers (ver `/testing`).

Fluxo por execucao:
1. `App\Services\Calendar\GoogleCalendarClient` autentica via Service Account (JWT assinado com `openssl`, sem SDK oficial — Guzzle/`Http` facade ja disponiveis, e Hostinger e sensivel a dependencias pesadas) e lista os eventos da janela `hoje` a `hoje + GOOGLE_CALENDAR_SYNC_DAYS` dias (default 60). Escopo **somente leitura** (`calendar.readonly`) — este cliente nunca altera a agenda.
2. `App\Services\Calendar\AppointmentMapper` traduz cada evento (parsing best-effort da descricao — eventos criados manualmente pela clinica, fora do formato do N8N, ainda entram na fila de confirmacao com os campos vazios).
3. Evento novo -> `Appointment::create(...)` com `situation = p`.
4. Evento cancelado no Calendar -> marca `situation = x`, nunca apaga a linha.
5. Evento confirmado (`situation = c`) que teve o horario alterado no Calendar depois da confirmacao -> volta para `p` (fila) e limpa `confirmed_by`/`confirmed_at`, em vez de divergir em silencio. Registra `Log::notice`.
6. Sem `GOOGLE_CALENDAR_ID` / `GOOGLE_CALENDAR_CREDENTIALS` no `.env` -> comando sai limpo (`warn` + `SUCCESS`), sem falhar a cada 5 minutos no scheduler. Suporta `--dry-run`.

Credencial da Service Account (JSON) fica **fora do repositorio**, caminho apontado por `GOOGLE_CALENDAR_CREDENTIALS` — ver `.env.example`.

### Autorizacao

Permissions novas (`database/seeders/RoleAndPermissionSeeder.php`), seguindo o padrao `can()` do projeto (nunca `hasRole()`):

- `appointment-list` — ve apenas os proprios agendamentos, e so depois de confirmados (atribuida a `profissional`)
- `appointment-list-all` — ve todos, em qualquer situacao, incluindo a fila de pendentes (recepcao/admin)
- `appointment-confirm` — confirma ou recusa um agendamento

`App\Policies\AppointmentPolicy` implementa `viewAny`, `view` e `confirm`. `Appointment::scopeVisibleToAuthUser()` centraliza a regra de visibilidade (usada pelo controller) — usuario sem `professional` vinculado nao ve nada (`whereRaw('1 = 0')`, explicito para nao confundir com `professional_id IS NULL`, que exporia agendamentos ainda sem vinculo).

### Tela

`GET /appointments` (`AppointmentController@index`) — filtros por busca, situacao (so quem tem `appointment-list-all`), periodo (`from`/`to`); paginacao padrao (`Controller::PAGINATION_DEFAULT`). Recepcao/admin veem colunas extras (contato, situacao, acoes) e os botoes de confirmar/recusar; confirmar exige selecionar o profissional (`professional_id`) num select pre-populado com os profissionais ativos (`allow = 1`).

`POST /appointments/{appointment}/confirm` e `POST /appointments/{appointment}/refuse` — ambos passam por `AppointmentPolicy::confirm` e logam via `App\Services\Logging\AppointmentLogger` (LGPD: loga apenas identificadores/metadados — nunca nome, telefone, e-mail ou motivo da consulta).

Menu lateral: item "Agendamentos" em `resources/views/layouts/app.blade.php`, visivel para quem tem `appointment-list` ou `appointment-list-all`.

### Arquivos da Etapa 2

- `database/migrations/2026_08_18_170508_create_appointments_table.php`
- `app/Models/Appointment.php`
- `app/Policies/AppointmentPolicy.php` (+ registro em `AuthServiceProvider`)
- `app/Http/Controllers/AppointmentController.php`
- `app/Console/Commands/SyncGoogleCalendarAppointments.php` (+ agendamento em `app/Console/Kernel.php`)
- `app/Services/Calendar/GoogleCalendarClient.php`
- `app/Services/Calendar/AppointmentMapper.php`
- `app/Services/Logging/AppointmentLogger.php`
- `resources/views/appointments/index.blade.php`
- `routes/web.php` — `appointments.index` / `appointments.confirm` / `appointments.refuse`
- `database/seeders/RoleAndPermissionSeeder.php` — permissions `appointment-*`
- `config/services.php` / `.env.example` — bloco `google_calendar`

### Verificacao

- [x] Migration roda limpo (`php artisan migrate`)
- [x] `php artisan agenda:sync --dry-run` sai limpo sem credenciais configuradas
- [x] Rotas registradas (`php artisan route:list --path=appointments`)
- [x] Permissions sincronizadas (`appointment-list`, `appointment-list-all`, `appointment-confirm`)
- [x] Pint sem violacoes (`./vendor/bin/pint --test`)
- [ ] Sincronizacao real contra um Google Calendar de teste (fora do dry-run, com credenciais validas)
- [ ] Teste manual do fluxo completo: evento criado pelo N8N -> aparece pendente -> recepcao confirma com profissional -> profissional ve seu agendamento
- [ ] Teste manual: evento cancelado no Calendar -> aparece cancelado no sistema
- [ ] Teste manual: evento confirmado tem o horario alterado no Calendar -> volta para pendente

---

## Etapa 3 — Notificacao da confirmacao via N8N

Ao confirmar um agendamento na tela, o Maieutica envia um `POST` para a URL
configurada em `N8N_APPOINTMENT_CONFIRMED_WEBHOOK`. O futuro workflow do N8N
recebera esse evento e enviara mensagens de WhatsApp ao paciente e ao
profissional.

Contrato do webhook:

- evento: `appointment.confirmed`;
- autenticacao: `Authorization: Bearer <N8N_WEBHOOK_TOKEN>`;
- idempotencia: header `X-Idempotency-Key` (o workflow deve impedir envio
  duplicado para a mesma chave);
- payload: identificadores do agendamento e evento Google, data/hora, contato
  do paciente, contato do profissional e dados da confirmacao;
- timeout configuravel por `N8N_WEBHOOK_TIMEOUT` (default 10 segundos).

A confirmacao no Maieutica e a fonte de verdade e nao e desfeita quando o N8N
falha. Nesse caso, a recepcao recebe um alerta na tela para notificar os
envolvidos manualmente, e a falha fica registrada sem expor o payload nos logs.

Status: o emissor `App\Services\Integrations\N8nAppointmentNotifier` esta
implementado. O workflow receptor foi criado separadamente em
`n8n/fluxo-confirmacao-agendamento.json`, sem alterar o fluxo principal. Antes
de ativa-lo, importe o JSON e confirme a credencial `EvoGo Account`. Em
instalacoes sem suporte a variaveis N8N, configure uma credencial nativa
`Header Auth` no node do webhook (`Authorization: Bearer <token>`) e informe o
token da instancia Evolution diretamente nos dois nodes de envio. O Bearer
token deve ser igual ao `N8N_WEBHOOK_TOKEN` configurado no Maieutica.

---

## Proximas Etapas

A definir junto com o usuario, conforme o fluxo avancar (ex.: notificacao ao paciente na confirmacao, cancelamento pelo proprio sistema espelhando de volta pro Calendar, vinculo do agendamento com `Kid`/`MedicalRecord`, etc.). Esta secao e atualizada a cada nova etapa combinada.
