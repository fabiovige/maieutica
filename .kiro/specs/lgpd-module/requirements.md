# Requirements Document

## Introduction

O Módulo LGPD implementa conformidade com a Lei Geral de Proteção de Dados (Lei nº 13.709/2018) na plataforma Maiêutica. O módulo gerencia consentimento de titulares, registra acessos a prontuários, processa requisições de direitos dos titulares (acesso, retificação, eliminação, portabilidade), controla prazos legais e gera relatórios de conformidade em PDF. O módulo segue a arquitetura modular do sistema, comunicando-se com outros módulos exclusivamente via Events.

## Glossary

- **Módulo_LGPD**: Módulo da aplicação responsável por todas as funcionalidades de conformidade com a LGPD, localizado em `app/Modules/Lgpd/`.
- **Titular**: Pessoa física (paciente ou responsável) cujos dados pessoais são tratados pela plataforma. Armazenado na tabela `kids` ou como responsável vinculado.
- **Consentimento**: Manifestação livre, informada e inequívoca do Titular autorizando o tratamento de dados pessoais para uma finalidade específica.
- **ConsentRecord**: Agregado de domínio que representa um registro de consentimento, incluindo finalidade, base legal, versão do termo e status.
- **Base_Legal**: Fundamento jurídico que autoriza o tratamento de dados (ex.: consentimento, execução de contrato, obrigação legal, tutela da saúde).
- **AccessLog**: Registro de auditoria que documenta cada acesso a prontuários médicos, incluindo quem acessou, quando e qual registro.
- **DataRequest**: Requisição formal de um Titular exercendo seus direitos previstos na LGPD (acesso, retificação, eliminação, portabilidade).
- **Prazo_Legal**: Período máximo de 15 dias úteis para resposta a uma DataRequest, conforme Art. 18 §5º da LGPD.
- **Relatório_Conformidade**: Documento PDF que consolida o estado de conformidade da plataforma com a LGPD.
- **Operador**: Usuário do sistema (profissional ou administrador) que realiza tratamento de dados pessoais.
- **Controlador**: A clínica que utiliza a plataforma Maiêutica, responsável pelas decisões sobre o tratamento de dados.
- **Política_Retenção**: Regra que define o período máximo de armazenamento de dados pessoais por categoria.

## Requirements

### Requisito 1: Estrutura Modular

**User Story:** Como desenvolvedor, quero que o módulo LGPD siga a arquitetura modular padrão, para que o código seja isolado e manutenível.

#### Critérios de Aceitação

1. THE Módulo_LGPD SHALL organizar seu código dentro de `app/Modules/Lgpd/` nas seguintes camadas: Domain (Entities, Value Objects, Events, Exceptions), Application (Services, DTOs, Listeners), Infrastructure (Repositories, Eloquent Models, Migrations, Seeders) e Http (Controllers, Requests, Resources, Routes).
2. THE Módulo_LGPD SHALL comunicar-se com outros módulos da aplicação exclusivamente por meio de Events do Laravel, sem importar classes internas de outros módulos nem permitir que outros módulos importem classes internas do Módulo_LGPD.
3. THE Módulo_LGPD SHALL registrar um ServiceProvider próprio em `app/Modules/Lgpd/Providers/LgpdServiceProvider.php`, declarado no array `providers` de `config/app.php`, que carregue as migrations do módulo, as rotas (web e api) do módulo e um arquivo de configuração `lgpd.php`.
4. THE Módulo_LGPD SHALL definir suas permissões seguindo o padrão `lgpd-{ação}` compatível com Spatie Laravel Permission, registradas via Seeder próprio do módulo, incluindo no mínimo as permissões: `lgpd-consent-manage`, `lgpd-access-log-view`, `lgpd-request-manage`, `lgpd-report-generate` e `lgpd-retention-manage`.
5. THE Módulo_LGPD SHALL respeitar a direção de dependência entre camadas: Http depende de Application, Application depende de Domain, Infrastructure depende de Domain; nenhuma camada pode depender de Http e Domain não depende de nenhuma outra camada do módulo.

### Requisito 2: Gestão de Consentimento

**User Story:** Como operador da clínica, quero registrar e gerenciar o consentimento dos titulares, para que o tratamento de dados tenha base legal documentada.

#### Critérios de Aceitação

1. WHEN um novo consentimento é coletado, THE Módulo_LGPD SHALL criar um ConsentRecord com finalidade (máximo 255 caracteres), base legal, versão do termo, data de coleta e identificação do Titular (referência ao registro na tabela `kids` ou ao responsável vinculado).
2. WHEN um Titular revoga seu consentimento, THE Módulo_LGPD SHALL alterar o status do ConsentRecord de "ativo" para "revogado", registrar a data da revogação e preservar todos os campos originais do registro para fins de histórico e auditoria.
3. WHILE um ConsentRecord está com status "ativo", THE Módulo_LGPD SHALL responder a eventos de consulta de consentimento emitidos por outros módulos, retornando a finalidade, base legal e data de coleta do consentimento válido para o Titular e finalidade consultados.
4. THE Módulo_LGPD SHALL armazenar múltiplos ConsentRecords por Titular, um para cada finalidade de tratamento, garantindo que exista no máximo um ConsentRecord com status "ativo" por combinação de Titular e finalidade.
5. THE Módulo_LGPD SHALL versionar os termos de consentimento, vinculando cada ConsentRecord à versão do termo aceita pelo Titular, utilizando versionamento sequencial numérico (1, 2, 3...).
6. IF um ConsentRecord é criado sem finalidade ou sem base legal, THEN THE Módulo_LGPD SHALL rejeitar a operação e retornar mensagem de erro indicando qual campo obrigatório está ausente.
7. IF um operador tenta criar um ConsentRecord para uma combinação de Titular e finalidade que já possui um ConsentRecord ativo, THEN THE Módulo_LGPD SHALL rejeitar a operação e retornar mensagem de erro indicando que já existe consentimento ativo para aquela finalidade.
8. WHEN uma nova versão de termo de consentimento é publicada, THE Módulo_LGPD SHALL manter os ConsentRecords existentes vinculados à versão do termo originalmente aceita, sem alteração automática de status.
9. THE Módulo_LGPD SHALL reconhecer os seguintes status para um ConsentRecord: "ativo" (consentimento vigente) e "revogado" (consentimento retirado pelo Titular).

### Requisito 3: Registro de Acesso a Prontuários

**User Story:** Como responsável pela conformidade, quero que todos os acessos a prontuários sejam registrados automaticamente, para que exista trilha de auditoria completa.

#### Critérios de Aceitação

1. WHEN um Operador realiza uma operação sobre um prontuário médico (visualização, download de PDF, edição, exclusão ou restauração), THE Módulo_LGPD SHALL criar um AccessLog contendo identificação do Operador, identificação do prontuário, data/hora do acesso com precisão de segundos, tipo de operação realizada, endereço IP e user-agent do Operador.
2. THE Módulo_LGPD SHALL capturar acessos de escrita (edição, exclusão, restauração) por meio de um Observer no modelo MedicalRecord, e acessos de leitura (visualização, download de PDF) por meio de escuta ao evento `MedicalRecordAccessed` disparado pelo controller.
3. THE Módulo_LGPD SHALL armazenar AccessLogs de forma imutável, impedindo edição ou exclusão dos registros de auditoria tanto pela aplicação quanto por operações de soft-delete.
4. IF a criação de um AccessLog falha por indisponibilidade de contexto de requisição (ex.: execução via Job ou CLI), THEN THE Módulo_LGPD SHALL registrar o AccessLog com os campos IP e user-agent preenchidos como "system" e registrar o erro no log da aplicação.
5. WHEN um Operador com permissão `lgpd-access-log-view` consulta AccessLogs, THE Módulo_LGPD SHALL permitir filtragem por Titular, Operador, período (data inicial e data final) e tipo de operação, retornando resultados paginados com no máximo 50 registros por página.

### Requisito 4: Requisições de Direitos dos Titulares

**User Story:** Como operador da clínica, quero processar requisições de titulares exercendo seus direitos LGPD, para que a clínica cumpra as obrigações legais dentro do prazo.

#### Critérios de Aceitação

1. WHEN um Titular solicita exercício de direito, THE Módulo_LGPD SHALL criar uma DataRequest com tipo (acesso aos dados, retificação, eliminação, portabilidade ou revogação de consentimento), nome do solicitante, documento de identificação (CPF), meio de contato, data de abertura e prazo de resposta calculado, atribuindo o status inicial "aberta".
2. THE Módulo_LGPD SHALL calcular o Prazo_Legal como 15 dias úteis a partir da data de abertura da DataRequest, excluindo finais de semana e feriados nacionais.
3. WHEN uma DataRequest se aproxima do vencimento (5 dias úteis restantes), THE Módulo_LGPD SHALL disparar uma notificação de alerta para os Operadores com permissão `lgpd-request-manage`.
4. WHEN o Prazo_Legal de uma DataRequest expira sem resposta, THE Módulo_LGPD SHALL marcar a requisição como "vencida" e disparar notificação de urgência para os Operadores com permissão `lgpd-request-manage`.
5. WHEN uma DataRequest é concluída, THE Módulo_LGPD SHALL registrar a data de conclusão, o Operador responsável e a resposta fornecida ao Titular (até 5000 caracteres), alterando o status para "concluída".
6. THE Módulo_LGPD SHALL suportar os seguintes tipos de DataRequest: acesso aos dados, retificação, eliminação, portabilidade e revogação de consentimento.
7. IF uma DataRequest de eliminação é recebida e existem obrigações legais de retenção, THEN THE Módulo_LGPD SHALL registrar a justificativa de retenção (até 2000 caracteres) e registrar na resposta da DataRequest a indicação ao Titular sobre a impossibilidade parcial ou total de eliminação.
8. THE Módulo_LGPD SHALL manter cada DataRequest em um dos seguintes estados: "aberta", "em_andamento", "concluída" ou "vencida", permitindo apenas as transições: aberta → em_andamento, em_andamento → concluída, e qualquer estado exceto concluída → vencida (por expiração de prazo).
9. IF uma DataRequest é criada sem tipo, sem identificação do solicitante (CPF) ou sem meio de contato, THEN THE Módulo_LGPD SHALL rejeitar a operação e retornar mensagem de erro indicando os campos obrigatórios ausentes.
10. WHEN um Operador assume o processamento de uma DataRequest, THE Módulo_LGPD SHALL alterar o status para "em_andamento" e registrar o Operador responsável e a data de início do processamento.

### Requisito 5: Controle de Prazos e Jobs

**User Story:** Como administrador do sistema, quero que prazos legais sejam monitorados automaticamente, para que nenhuma requisição expire sem tratamento.

#### Critérios de Aceitação

1. THE Módulo_LGPD SHALL executar um Job agendado diariamente às 06:00 (horário do servidor) que verifica todas as DataRequests com status "pendente" ou "em andamento" cujo Prazo_Legal esteja a 5 dias úteis ou menos do vencimento ou já tenha expirado.
2. WHEN o Job de verificação identifica DataRequests com 5 dias úteis ou menos para o vencimento, THE Módulo_LGPD SHALL disparar evento de alerta contendo a lista de requisições com prazo crítico, sem gerar alertas duplicados para requisições já alertadas na mesma faixa de prazo.
3. WHEN o Job de verificação identifica DataRequests cujo Prazo_Legal expirou sem resposta, THE Módulo_LGPD SHALL atualizar o status para "vencida" e disparar evento de urgência.
4. THE Módulo_LGPD SHALL registrar a execução de cada Job no log do sistema, incluindo data/hora de execução, quantidade de requisições verificadas, quantidade de alertas gerados e quantidade de requisições marcadas como vencidas.
5. IF o Job de verificação de prazos falha durante a execução, THEN THE Módulo_LGPD SHALL registrar o erro no log do sistema e disparar notificação de falha para os Operadores com permissão `lgpd-request-manage`.

### Requisito 6: Políticas de Retenção de Dados

**User Story:** Como responsável pela conformidade, quero definir políticas de retenção por categoria de dados, para que dados pessoais não sejam mantidos além do necessário.

#### Critérios de Aceitação

1. THE Módulo_LGPD SHALL permitir configuração de Política_Retenção por categoria de dados (prontuários, consentimentos, logs de acesso, dados cadastrais), incluindo período de retenção em dias e ação ao expirar (sinalizar para revisão ou anonimizar), restrita a Operadores com permissão `lgpd-retention-manage`.
2. WHEN o Job diário de verificação de retenção identifica dados cujo período de retenção expirou, THE Módulo_LGPD SHALL alterar o status do dado para "pendente_revisão" e disparar notificação para os Operadores com permissão `lgpd-retention-manage` indicando a categoria, a quantidade de registros expirados e a data de expiração.
3. THE Módulo_LGPD SHALL impedir a eliminação ou anonimização de dados que estejam dentro do período de retenção legal mínima (prontuários médicos: 20 anos conforme CFM; consentimentos: 5 anos após término do tratamento; logs de acesso: 5 anos), bloqueando a operação e exibindo mensagem de erro indicando o prazo legal restante.
4. IF uma Política_Retenção conflita com obrigação legal de retenção mínima, THEN THE Módulo_LGPD SHALL aplicar o prazo mais longo e registrar a justificativa no histórico da Política_Retenção, incluindo a norma legal aplicada e a data da decisão.
5. IF um Operador tenta configurar uma Política_Retenção com período inferior ao mínimo legal da categoria, THEN THE Módulo_LGPD SHALL rejeitar a configuração e retornar mensagem de erro indicando o período mínimo legal exigido para a categoria.

### Requisito 7: Relatório de Conformidade em PDF

**User Story:** Como administrador da clínica, quero gerar um relatório PDF consolidado de conformidade LGPD, para apresentar em auditorias e demonstrar adequação à lei.

#### Critérios de Aceitação

1. WHEN um Operador com permissão `lgpd-report-generate` solicita o relatório, THE Módulo_LGPD SHALL gerar um PDF forçando download (Content-Disposition: attachment) com nome no formato `relatorio-conformidade-lgpd_{data-inicial}_{data-final}_{YmdHis}.pdf`, contendo seções de consentimentos ativos, DataRequests processadas, AccessLogs do período e status das políticas de retenção.
2. THE Módulo_LGPD SHALL gerar o PDF utilizando DomPDF com fonte DejaVu Sans, estendendo o template `documents.layouts.pdf-base` em formato A4 retrato.
3. THE Módulo_LGPD SHALL exigir filtro por período (data inicial e data final) na geração do Relatório_Conformidade, onde o intervalo máximo permitido é de 365 dias corridos.
4. THE Módulo_LGPD SHALL incluir no relatório métricas quantitativas: total de consentimentos ativos, total de DataRequests por status (pendente, em andamento, concluída, vencida), total de acessos a prontuários e tempo médio de resposta a DataRequests em dias úteis.
5. IF não existem dados para o período selecionado, THEN THE Módulo_LGPD SHALL gerar o relatório com texto indicando ausência de registros em cada seção sem dados.
6. IF a data final é anterior à data inicial ou o intervalo excede 365 dias, THEN THE Módulo_LGPD SHALL rejeitar a solicitação e retornar mensagem de erro indicando a restrição de período violada.

### Requisito 8: Interface de Usuário

**User Story:** Como operador da clínica, quero telas para gerenciar consentimentos, requisições e logs de acesso, para que eu possa operar o módulo LGPD de forma eficiente.

#### Critérios de Aceitação

1. THE Módulo_LGPD SHALL fornecer tela de listagem de ConsentRecords com filtros por Titular, finalidade e status, utilizando DataTables server-side, exibindo as colunas: Titular, finalidade, base legal, status, data de coleta e versão do termo.
2. THE Módulo_LGPD SHALL fornecer tela de listagem de DataRequests com filtros por tipo, status e prazo, utilizando DataTables server-side, exibindo as colunas: Titular, tipo de requisição, status, data de abertura, prazo de vencimento e Operador responsável.
3. THE Módulo_LGPD SHALL fornecer tela de listagem de AccessLogs com filtros por Titular, Operador e período, utilizando DataTables server-side, exibindo as colunas: Titular, Operador, data/hora do acesso, tipo de operação e identificação do prontuário.
4. THE Módulo_LGPD SHALL renderizar componentes Vue 3 (Options API) como ilhas reativas em templates Blade, seguindo o padrão da plataforma.
5. THE Módulo_LGPD SHALL apresentar toda a interface em Português Brasileiro (pt-BR), incluindo rótulos de colunas, mensagens de filtro, botões de ação e mensagens de estado vazio.
6. THE Módulo_LGPD SHALL utilizar Bootstrap 5.3 e Bootstrap Icons 1.11 para estilização, reutilizando os mesmos componentes visuais (cards, tabelas, botões, badges) já presentes nas demais telas da plataforma.
7. WHEN um Operador sem a permissão `lgpd-list` correspondente à tela tenta acessar uma tela do módulo LGPD, THE Módulo_LGPD SHALL negar o acesso com HTTP 403 e exibir mensagem de permissão insuficiente.
8. IF uma listagem DataTables não retorna registros para os filtros aplicados, THEN THE Módulo_LGPD SHALL exibir mensagem indicando ausência de registros para os critérios selecionados.
9. THE Módulo_LGPD SHALL fornecer, a partir de cada listagem, ações de visualização de detalhes do registro, acessíveis apenas a Operadores com a permissão `lgpd-show` correspondente à entidade.

### Requisito 9: Rastreamento de Base Legal

**User Story:** Como responsável pela conformidade, quero rastrear a base legal de cada tratamento de dados, para demonstrar que todo processamento tem fundamento jurídico.

#### Critérios de Aceitação

1. THE Módulo_LGPD SHALL suportar as seguintes bases legais conforme LGPD Art. 7 e Art. 11: consentimento do titular, execução de contrato, obrigação legal ou regulatória, tutela da saúde, legítimo interesse, proteção da vida, exercício regular de direitos em processo e realização de estudos por órgão de pesquisa.
2. WHEN um ConsentRecord é criado, THE Módulo_LGPD SHALL exigir a indicação de uma Base_Legal cujo valor pertença à lista enumerada no critério 1, rejeitando a operação com mensagem de erro indicando o valor inválido caso contrário.
3. THE Módulo_LGPD SHALL permitir consulta de todos os ConsentRecords agrupados por Base_Legal, retornando para cada grupo a quantidade de registros e a lista de Titulares associados.
4. IF a Base_Legal de um ConsentRecord é "consentimento" e o Titular revoga o consentimento, THEN THE Módulo_LGPD SHALL alterar o status do ConsentRecord para "pendente_revisão" e disparar notificação para os Operadores com permissão `lgpd-consent-manage` indicando a necessidade de avaliar base legal alternativa ou cessação do tratamento.
5. WHEN um Operador altera a Base_Legal de um ConsentRecord existente, THE Módulo_LGPD SHALL registrar a Base_Legal anterior, a nova Base_Legal, a data da alteração e a justificativa informada pelo Operador, mantendo histórico completo de mudanças.

### Requisito 10: Comunicação Inter-Módulos via Events

**User Story:** Como desenvolvedor, quero que o módulo LGPD se integre com o restante do sistema via eventos, para manter o desacoplamento entre módulos.

#### Critérios de Aceitação

1. WHEN um prontuário é acessado em outro módulo, THE Módulo_LGPD SHALL escutar o evento `MedicalRecordAccessed` e criar o AccessLog correspondente contendo o ID do Operador, o ID do prontuário, o timestamp do acesso e o tipo de operação recebidos no payload do evento.
2. WHEN um ConsentRecord é revogado, THE Módulo_LGPD SHALL disparar o evento `ConsentRevoked` com payload contendo o ID do ConsentRecord, o ID do Titular, a finalidade do consentimento revogado e o timestamp da revogação.
3. WHEN uma DataRequest de eliminação é concluída, THE Módulo_LGPD SHALL disparar o evento `DataDeletionCompleted` com payload contendo o ID da DataRequest, o ID do Titular e a lista de categorias de dados eliminados (ex.: prontuários, dados cadastrais, logs).
4. WHEN uma DataRequest atinge 5 dias úteis ou menos para o vencimento do Prazo_Legal, THE Módulo_LGPD SHALL disparar o evento `DataRequestDeadlineAlert` com payload contendo o ID da DataRequest, o tipo de requisição, a data de vencimento e os dias úteis restantes.
5. THE Módulo_LGPD SHALL definir todos os seus eventos em `app/Modules/Lgpd/Domain/Events/` e cada evento SHALL implementar uma interface PHP que declare como propriedades públicas tipadas todos os campos obrigatórios do payload.
6. THE Módulo_LGPD SHALL comunicar-se com outros módulos exclusivamente via Events, sem importar classes (Models, Services ou Repositories) pertencentes a outros módulos.
7. IF o dispatch de um evento do Módulo_LGPD falha, THEN THE Módulo_LGPD SHALL registrar a falha no log do sistema contendo o nome do evento, o payload e a mensagem de erro, sem interromper a operação principal que originou o disparo.
8. IF um listener do Módulo_LGPD falha ao processar um evento recebido, THEN THE Módulo_LGPD SHALL registrar a falha no log do sistema e não propagar a exceção ao módulo emissor.
