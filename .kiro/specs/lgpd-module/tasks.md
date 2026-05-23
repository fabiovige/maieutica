# Implementation Plan: Módulo LGPD

## Overview

Implementação do módulo LGPD como módulo autocontido em `app/Modules/Lgpd/`, seguindo arquitetura em camadas (Domain, Application, Infrastructure, Http). O plano está organizado em 6 entregas incrementais, cada uma construindo sobre a anterior, finalizando com a integração completa de todos os componentes.

## Tasks

- [x] 1. Estrutura do módulo, migrations e configuração base
  - [x] 1.1 Criar estrutura de diretórios do módulo e LgpdServiceProvider
    - Criar toda a árvore de diretórios em `app/Modules/Lgpd/` (Domain/, Application/, Infrastructure/, Http/, Jobs/, Providers/, Config/)
    - Criar `LgpdServiceProvider.php` que carrega migrations, rotas (web e api) e config `lgpd.php`
    - Criar `Config/lgpd.php` com configurações de prazos, mínimos legais e feriados
    - Registrar o provider em `config/app.php`
    - _Requisitos: 1.1, 1.2, 1.3, 1.5_

  - [x] 1.2 Criar migrations das 5 tabelas do módulo
    - `create_lgpd_consent_records_table` com todos os campos, índices e FKs conforme design
    - `create_lgpd_access_logs_table` sem updated_at/deleted_at (imutável)
    - `create_lgpd_data_requests_table` com todos os campos e índices
    - `create_lgpd_retention_policies_table` com UNIQUE em category
    - `create_lgpd_consent_legal_basis_history_table` para histórico de alterações
    - _Requisitos: 2.1, 3.1, 3.3, 4.1, 6.1, 9.5_

  - [x] 1.3 Criar Value Objects e Enums do domínio
    - `LegalBasis.php` — enum com as 8 bases legais do Art. 7 e Art. 11
    - `DataRequestType.php` — enum: acesso, retificacao, eliminacao, portabilidade, revogacao
    - `DataRequestStatus.php` — enum: aberta, em_andamento, concluida, vencida
    - `ConsentStatus.php` — enum: ativo, revogado
    - `OperationType.php` — enum: view, download_pdf, edit, delete, restore
    - `DataCategory.php` — enum: prontuarios, consentimentos, access_logs, dados_cadastrais
    - _Requisitos: 2.9, 4.6, 4.8, 9.1_

  - [x] 1.4 Criar Domain Exceptions
    - `DuplicateActiveConsentException.php`
    - `InvalidLegalBasisException.php`
    - `RetentionPeriodViolationException.php`
    - `InvalidDataRequestTransitionException.php`
    - `ImmutableRecordException.php`
    - _Requisitos: 2.6, 2.7, 6.5, 4.8_

  - [x] 1.5 Criar LgpdPermissionSeeder e registrar permissões
    - Criar seeder com as 10 permissões: `lgpd-consent-manage`, `lgpd-consent-list`, `lgpd-consent-show`, `lgpd-access-log-view`, `lgpd-request-manage`, `lgpd-request-list`, `lgpd-request-show`, `lgpd-report-generate`, `lgpd-retention-manage`, `lgpd-retention-list`
    - Registrar no `DatabaseSeeder` principal
    - _Requisitos: 1.4_

  - [x] 1.6 Criar Eloquent Models da camada Infrastructure
    - `ConsentRecordModel.php` com fillable, casts, relationships e scopes
    - `AccessLogModel.php` sem SoftDeletes, com boot() bloqueando update/delete
    - `DataRequestModel.php` com fillable, casts e scopes por status
    - `RetentionPolicyModel.php` com fillable e casts
    - _Requisitos: 2.1, 3.3, 4.1, 6.1_

- [~] 2. Checkpoint — Executar migrations e verificar estrutura
  - Executar `php artisan migrate` e verificar que as 5 tabelas foram criadas
  - Executar `php artisan db:seed --class=LgpdPermissionSeeder` e verificar permissões
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 3. ConsentRecord — Aggregate, Service e handlers
  - [~] 3.1 Criar DTOs da camada Application
    - `CreateConsentDTO.php` com propriedades readonly tipadas
    - `CreateDataRequestDTO.php`
    - `CreateRetentionPolicyDTO.php`
    - `ComplianceReportFilterDTO.php`
    - _Requisitos: 2.1, 4.1, 6.1, 7.3_

  - [~] 3.2 Implementar ConsentService
    - Método `create()` — valida unicidade ativo por titular+finalidade, valida base legal, cria registro
    - Método `revoke()` — altera status para revogado, preenche revoked_at/revoked_by, dispara ConsentRevoked
    - Método `findActiveForSubject()` — busca consentimento ativo por titular+finalidade
    - Método `listBySubject()` — lista todos os consentimentos de um titular
    - Método `listByLegalBasis()` — agrupa por base legal
    - Método `changeLegalBasis()` — altera base legal com registro no histórico
    - _Requisitos: 2.1, 2.2, 2.3, 2.4, 2.6, 2.7, 9.2, 9.3, 9.4, 9.5_

  - [~] 3.3 Criar Domain Events
    - `ConsentRevoked.php` com payload: consentRecordId, subjectId, purpose, revokedAt
    - `DataDeletionCompleted.php` com payload: dataRequestId, subjectId, deletedCategories
    - `DataRequestDeadlineAlert.php` com payload: dataRequestId, requestType, deadline, businessDaysRemaining
    - `MedicalRecordAccessed.php` com payload: operatorId, recordId, operationType, accessedAt
    - _Requisitos: 10.1, 10.2, 10.3, 10.4, 10.5_

  - [ ]* 3.4 Escrever testes de propriedade para ConsentRecord
    - **Property 1: Round-trip de ConsentRecord**
    - **Property 2: Revogação preserva dados originais**
    - **Property 3: Invariante de unicidade de consentimento ativo**
    - **Property 4: Validação de criação rejeita inputs inválidos**
    - **Property 5: Imutabilidade de versão de termo**
    - **Property 16: Histórico de alteração de base legal**
    - **Valida: Requisitos 2.1, 2.2, 2.3, 2.4, 2.6, 2.7, 2.8, 9.5**

  - [ ]* 3.5 Escrever testes unitários para ConsentService
    - Testar criação com dados válidos
    - Testar rejeição de duplicata ativa
    - Testar revogação com preservação de campos
    - Testar changeLegalBasis com registro no histórico
    - _Requisitos: 2.1, 2.2, 2.4, 2.7, 9.5_

- [ ] 4. AccessLog — Observer, Listener e Service
  - [~] 4.1 Implementar AccessLogService
    - Método `create()` — cria registro imutável com IP, user-agent, timestamp
    - Método `listFiltered()` — filtragem por titular, operador, período, tipo; paginação máx 50
    - Tratamento de contexto ausente (IP/user-agent = "system" quando fora de HTTP)
    - _Requisitos: 3.1, 3.4, 3.5_

  - [~] 4.2 Implementar MedicalRecordLgpdObserver
    - Registrar no LgpdServiceProvider observando o model MedicalRecord
    - Capturar eventos `updated`, `deleted`, `restored` e criar AccessLog correspondente
    - Usar try/catch para não propagar exceções ao módulo de prontuários
    - _Requisitos: 3.2, 10.8_

  - [~] 4.3 Implementar MedicalRecordAccessListener e MedicalRecordWriteListener
    - `MedicalRecordAccessListener` — escuta evento `MedicalRecordAccessed` e cria AccessLog
    - `MedicalRecordWriteListener` — escuta eventos de escrita do Observer
    - Registrar listeners no LgpdServiceProvider via `$listen`
    - Padrão try/catch com Log::error sem propagação
    - _Requisitos: 3.2, 10.1, 10.6, 10.8_

  - [ ]* 4.4 Escrever testes de propriedade para AccessLog
    - **Property 6: Round-trip de AccessLog**
    - **Property 7: Imutabilidade de AccessLog**
    - **Property 8: Filtragem respeita critérios**
    - **Valida: Requisitos 3.1, 3.3, 3.5**

  - [ ]* 4.5 Escrever testes unitários para AccessLogService e Observer
    - Testar criação com dados válidos
    - Testar bloqueio de update/delete no model
    - Testar fallback para "system" sem contexto HTTP
    - Testar Observer capturando operações de escrita
    - _Requisitos: 3.1, 3.3, 3.4_

- [ ] 5. DataRequest — Service, BusinessDayCalculator e Jobs de prazo
  - [~] 5.1 Implementar BusinessDayCalculator
    - Método `addBusinessDays()` — soma N dias úteis excluindo sábados, domingos e feriados nacionais
    - Método `businessDaysRemaining()` — calcula dias úteis restantes até deadline
    - Método `isBusinessDay()` — verifica se data é dia útil
    - Lista de feriados nacionais brasileiros configurável em `config/lgpd.php`
    - _Requisitos: 4.2, 5.1_

  - [~] 5.2 Implementar DataRequestService
    - Método `create()` — valida campos obrigatórios, calcula deadline via BusinessDayCalculator, status inicial "aberta"
    - Método `assignOperator()` — transição aberta → em_andamento, registra operador e started_at
    - Método `complete()` — transição em_andamento → concluída, registra resposta e retention_justification
    - Método `markAsExpired()` — transição para vencida (apenas de aberta/em_andamento)
    - Método `listFiltered()` — filtragem por tipo, status, prazo
    - Validação de máquina de estados com InvalidDataRequestTransitionException
    - _Requisitos: 4.1, 4.2, 4.5, 4.7, 4.8, 4.9, 4.10_

  - [~] 5.3 Implementar CheckDataRequestDeadlinesJob
    - Buscar DataRequests com status aberta/em_andamento
    - Identificar requisições com ≤ 5 dias úteis restantes → disparar DataRequestDeadlineAlert
    - Identificar requisições com prazo expirado → marcar como vencida
    - Controle de idempotência via campo `alerted_at` (não alertar novamente na mesma faixa)
    - Registrar execução no log: data/hora, qtd verificadas, alertas, vencidas
    - _Requisitos: 5.1, 5.2, 5.3, 5.4, 5.5_

  - [~] 5.4 Registrar Job no Kernel (schedule)
    - Agendar `CheckDataRequestDeadlinesJob` para execução diária às 06:00
    - _Requisitos: 5.1_

  - [ ]* 5.5 Escrever testes de propriedade para DataRequest
    - **Property 9: Cálculo de prazo legal**
    - **Property 10: Transições de estado**
    - **Property 11: Validação de criação**
    - **Property 12: Idempotência de alertas**
    - **Valida: Requisitos 4.1, 4.2, 4.8, 4.9, 5.2**

  - [ ]* 5.6 Escrever testes unitários para BusinessDayCalculator e DataRequestService
    - Testar cálculo de 15 dias úteis com feriados
    - Testar transições válidas e inválidas
    - Testar rejeição de campos obrigatórios ausentes
    - Testar Job com cenários de alerta e expiração
    - _Requisitos: 4.2, 4.8, 4.9, 5.1, 5.2_

- [~] 6. Checkpoint — Verificar serviços e jobs
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 7. RetentionPolicy — Service e Job de verificação
  - [~] 7.1 Implementar RetentionPolicyService
    - Método `create()` — valida período contra mínimo legal, cria política
    - Método `update()` — valida período contra mínimo legal, atualiza política
    - Método `validateAgainstLegalMinimum()` — compara período com mínimo da categoria
    - Método `getMinimumRetentionDays()` — retorna mínimo legal por categoria (prontuários: 7300, demais: 1825)
    - Lançar `RetentionPeriodViolationException` quando período < mínimo legal
    - _Requisitos: 6.1, 6.3, 6.4, 6.5_

  - [~] 7.2 Implementar CheckRetentionPoliciesJob
    - Verificar dados cujo período de retenção expirou por categoria
    - Sinalizar registros para revisão
    - Disparar notificação para operadores com permissão `lgpd-retention-manage`
    - Registrar execução no log do sistema
    - _Requisitos: 6.2_

  - [ ]* 7.3 Escrever testes de propriedade para RetentionPolicy
    - **Property 13: Enforcement de mínimo legal de retenção**
    - **Valida: Requisitos 6.3, 6.4, 6.5**

  - [ ]* 7.4 Escrever testes unitários para RetentionPolicyService
    - Testar criação com período válido
    - Testar rejeição de período inferior ao mínimo legal
    - Testar getMinimumRetentionDays para cada categoria
    - _Requisitos: 6.1, 6.3, 6.5_

- [ ] 8. Controllers, rotas e views Blade com DataTables
  - [~] 8.1 Criar Form Requests de validação
    - `StoreConsentRequest.php` — valida subject_id, subject_type, purpose, legal_basis, term_version
    - `StoreDataRequestRequest.php` — valida type, requester_name, requester_document (CPF), contact_method
    - `StoreRetentionPolicyRequest.php` — valida category, retention_days, expiration_action
    - `UpdateDataRequestRequest.php` — valida response, retention_justification
    - `GenerateReportRequest.php` — valida start_date, end_date, intervalo ≤ 365 dias
    - Mensagens de erro em pt-BR
    - _Requisitos: 2.6, 4.9, 6.5, 7.3, 7.6_

  - [~] 8.2 Implementar ConsentController
    - `index()` — retorna view Blade com DataTable
    - `datatable()` — endpoint server-side com filtros por titular, finalidade, status
    - `show()` — detalhes do consentimento
    - `store()` — cria consentimento via ConsentService
    - `revoke()` — revoga consentimento via ConsentService
    - Middleware de permissões: `lgpd-consent-list`, `lgpd-consent-show`, `lgpd-consent-manage`
    - _Requisitos: 2.1, 2.2, 8.1, 8.9_

  - [~] 8.3 Implementar AccessLogController
    - `index()` — retorna view Blade com DataTable
    - `datatable()` — endpoint server-side com filtros por titular, operador, período, tipo
    - Middleware de permissão: `lgpd-access-log-view`
    - _Requisitos: 3.5, 8.3_

  - [~] 8.4 Implementar DataRequestController
    - `index()` — retorna view Blade com DataTable
    - `datatable()` — endpoint server-side com filtros por tipo, status, prazo
    - `show()` — detalhes da requisição
    - `store()` — cria requisição via DataRequestService
    - `assign()` — atribui operador
    - `complete()` — conclui requisição
    - Middleware de permissões: `lgpd-request-list`, `lgpd-request-show`, `lgpd-request-manage`
    - _Requisitos: 4.1, 4.5, 4.10, 8.2, 8.9_

  - [~] 8.5 Implementar RetentionPolicyController
    - `index()` — retorna view Blade com listagem de políticas
    - `store()` — cria política via RetentionPolicyService
    - `update()` — atualiza política via RetentionPolicyService
    - Middleware de permissões: `lgpd-retention-list`, `lgpd-retention-manage`
    - _Requisitos: 6.1, 6.5_

  - [~] 8.6 Criar arquivo de rotas web.php do módulo
    - Definir todas as rotas conforme design (prefixo `/lgpd`)
    - Aplicar middleware `auth` + permissões específicas por rota
    - Registrar rotas no LgpdServiceProvider
    - _Requisitos: 1.3, 8.7_

  - [~] 8.7 Criar views Blade para listagens e detalhes
    - `resources/views/modules/lgpd/consents/index.blade.php` — listagem com DataTable
    - `resources/views/modules/lgpd/consents/show.blade.php` — detalhes
    - `resources/views/modules/lgpd/access-logs/index.blade.php` — listagem com DataTable
    - `resources/views/modules/lgpd/requests/index.blade.php` — listagem com DataTable
    - `resources/views/modules/lgpd/requests/show.blade.php` — detalhes
    - `resources/views/modules/lgpd/retention/index.blade.php` — listagem e formulário
    - Usar layout `app.blade.php`, Bootstrap 5.3, Bootstrap Icons 1.11
    - Textos em pt-BR, badges de status com cores semânticas
    - _Requisitos: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 8.8_

  - [~] 8.8 Criar componentes Vue 3 (Options API) para formulários interativos
    - `ConsentForm.vue` — formulário de registro de consentimento (select2 para titular, select para finalidade/base legal)
    - `DataRequestForm.vue` — formulário de nova requisição (máscara CPF, select tipo)
    - `RetentionPolicyForm.vue` — formulário de política de retenção (validação de mínimo legal client-side)
    - Registrar componentes em `resources/js/app.js`
    - Montar como ilhas em templates Blade
    - _Requisitos: 8.4, 8.6_

  - [ ]* 8.9 Escrever testes de feature para controllers HTTP
    - Testar status codes (200, 403, 422) para cada endpoint
    - Testar acesso negado sem permissão adequada
    - Testar DataTables retornando JSON correto
    - Testar validação de Form Requests
    - _Requisitos: 8.7, 8.9_

- [~] 9. Checkpoint — Verificar rotas, views e permissões
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 10. Relatório de conformidade em PDF
  - [~] 10.1 Implementar ComplianceReportService
    - Método `generate()` — coleta métricas do período, renderiza PDF via DomPDF
    - Calcular: total consentimentos ativos, DataRequests por status, total acessos, tempo médio resposta
    - Tratar período sem dados (texto de ausência por seção)
    - Validar intervalo ≤ 365 dias e data_final >= data_inicial
    - _Requisitos: 7.1, 7.3, 7.4, 7.5, 7.6_

  - [~] 10.2 Criar template Blade do relatório PDF
    - `resources/views/modules/lgpd/reports/compliance-pdf.blade.php`
    - Estender `documents.layouts.pdf-base`
    - Fonte DejaVu Sans, formato A4 retrato
    - Seções: cabeçalho com período, consentimentos, requisições, acessos, retenção
    - Nome do arquivo: `relatorio-conformidade-lgpd_{data-inicial}_{data-final}_{YmdHis}.pdf`
    - _Requisitos: 7.1, 7.2_

  - [~] 10.3 Implementar ComplianceReportController
    - `form()` — retorna view com formulário de período
    - `generate()` — valida período via GenerateReportRequest, gera PDF via service, força download
    - Middleware de permissão: `lgpd-report-generate`
    - Content-Disposition: attachment
    - _Requisitos: 7.1, 7.3_

  - [~] 10.4 Criar view Blade do formulário de relatório
    - `resources/views/modules/lgpd/reports/form.blade.php`
    - Campos: data inicial, data final (datepicker pt-BR)
    - Validação client-side de intervalo máximo 365 dias
    - _Requisitos: 7.3_

  - [ ]* 10.5 Escrever testes de propriedade para relatório
    - **Property 14: Validação de período do relatório**
    - **Property 15: Métricas refletem dados reais**
    - **Valida: Requisitos 7.3, 7.4, 7.6**

  - [ ]* 10.6 Escrever testes unitários para ComplianceReportService
    - Testar geração com dados no período
    - Testar geração com período sem dados (texto de ausência)
    - Testar rejeição de período inválido
    - _Requisitos: 7.1, 7.4, 7.5, 7.6_

- [ ] 11. Integração final — Events, wiring e API Resources
  - [~] 11.1 Implementar API Resources para respostas JSON
    - `ConsentRecordResource.php` — formata resposta de consentimento
    - `AccessLogResource.php` — formata resposta de log de acesso
    - `DataRequestResource.php` — formata resposta de requisição
    - _Requisitos: 8.9_

  - [~] 11.2 Criar rotas api.php do módulo (se necessário para Vue)
    - Endpoints JSON consumidos pelos componentes Vue
    - Registrar no LgpdServiceProvider
    - _Requisitos: 1.3_

  - [~] 11.3 Registrar todos os listeners e observers no LgpdServiceProvider
    - Mapear `MedicalRecordAccessed` → `MedicalRecordAccessListener`
    - Registrar `MedicalRecordLgpdObserver` no model MedicalRecord
    - Registrar schedule do `CheckDataRequestDeadlinesJob`
    - Verificar que dispatch de ConsentRevoked e DataDeletionCompleted funciona
    - _Requisitos: 10.1, 10.2, 10.3, 10.6_

  - [ ]* 11.4 Escrever testes de integração para eventos
    - **Property 17: Eventos disparados com payload correto**
    - Testar que ConsentRevoked é disparado ao revogar
    - Testar que DataDeletionCompleted é disparado ao concluir eliminação
    - Testar que listener cria AccessLog ao receber MedicalRecordAccessed
    - Testar que falha no listener não propaga exceção
    - Testar que falha no dispatch não interrompe operação principal
    - **Valida: Requisitos 10.1, 10.2, 10.3, 10.7, 10.8**

- [~] 12. Checkpoint final — Validação completa
  - Executar suite completa de testes: `php artisan test tests/Feature/Modules/Lgpd/ tests/Unit/Modules/Lgpd/`
  - Verificar que todas as rotas respondem corretamente
  - Verificar que permissões bloqueiam acesso não autorizado
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tarefas marcadas com `*` são opcionais e podem ser puladas para um MVP mais rápido
- Cada tarefa referencia requisitos específicos para rastreabilidade
- Checkpoints garantem validação incremental
- Testes de propriedade validam propriedades universais de corretude (mínimo 100 iterações)
- Testes unitários e de feature validam exemplos específicos e edge cases
- O módulo usa `QUEUE_CONNECTION=sync` — Jobs executam inline mas estão preparados para async
- Comunicação inter-módulos é exclusivamente via Events do Laravel

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.3", "1.4"] },
    { "id": 1, "tasks": ["1.2", "1.5"] },
    { "id": 2, "tasks": ["1.6"] },
    { "id": 3, "tasks": ["3.1", "3.3"] },
    { "id": 4, "tasks": ["3.2", "4.1", "5.1"] },
    { "id": 5, "tasks": ["3.4", "3.5", "4.2", "4.3", "5.2"] },
    { "id": 6, "tasks": ["4.4", "4.5", "5.3", "5.4", "7.1"] },
    { "id": 7, "tasks": ["5.5", "5.6", "7.2", "7.3", "7.4"] },
    { "id": 8, "tasks": ["8.1"] },
    { "id": 9, "tasks": ["8.2", "8.3", "8.4", "8.5"] },
    { "id": 10, "tasks": ["8.6", "8.7"] },
    { "id": 11, "tasks": ["8.8", "8.9"] },
    { "id": 12, "tasks": ["10.1", "10.2"] },
    { "id": 13, "tasks": ["10.3", "10.4", "10.5", "10.6"] },
    { "id": 14, "tasks": ["11.1", "11.2"] },
    { "id": 15, "tasks": ["11.3"] },
    { "id": 16, "tasks": ["11.4"] }
  ]
}
```
