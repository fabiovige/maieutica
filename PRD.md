# PRD - Product Requirements Document
## Sistema Maiêutica - Plataforma de Avaliação Cognitiva

---

## 1. Visão Geral do Produto

### 1.1 Resumo Executivo
Maiêutica é uma plataforma web especializada para clínicas psicológicas, focada na avaliação cognitiva de crianças, acompanhamento de progresso terapêutico e gestão integrada de profissionais e responsáveis. O sistema oferece ferramentas avançadas para aplicação de protocolos de avaliação, geração de relatórios detalhados e visualização de evolução através de gráficos interativos.

### 1.2 Proposta de Valor
- **Para Clínicas**: Digitalização completa do processo de avaliação cognitiva com padronização de protocolos
- **Para Profissionais**: Ferramentas eficientes para aplicação de avaliações e acompanhamento de múltiplos pacientes
- **Para Responsáveis**: Transparência no acompanhamento do desenvolvimento da criança
- **Para Crianças**: Avaliações estruturadas que permitem identificar necessidades e acompanhar evolução

### 1.3 Objetivos do Produto
1. Padronizar e digitalizar avaliações cognitivas em clínicas psicológicas
2. Facilitar o acompanhamento longitudinal do desenvolvimento infantil
3. Otimizar o tempo dos profissionais com ferramentas automatizadas
4. Gerar relatórios detalhados e insights baseados em dados
5. Promover colaboração entre equipe multidisciplinar

---

## 2. Personas e Usuários

### 2.1 Administrador da Clínica
**Características:**
- Responsável pela gestão geral do sistema
- Controla acessos e permissões
- Monitora utilização e performance

**Necessidades:**
- Gestão centralizada de usuários e permissões
- Visualização de métricas gerais da clínica
- Controle de profissionais e suas atribuições
- Configuração de parâmetros do sistema

### 2.2 Profissional de Saúde (Psicólogo, Terapeuta)
**Características:**
- Aplica avaliações e acompanha crianças
- Precisa de ferramentas ágeis e precisas
- Gera relatórios para responsáveis

**Necessidades:**
- Acesso rápido às fichas dos pacientes
- Aplicação eficiente de checklists de avaliação
- Visualização de progresso ao longo do tempo
- Geração automatizada de relatórios
- Colaboração com outros profissionais

### 2.3 Responsável Legal
**Características:**
- Pai, mãe ou tutor da criança
- Busca acompanhar o desenvolvimento
- Precisa de informações claras e acessíveis

**Necessidades:**
- Visualização do progresso da criança
- Acesso a relatórios e avaliações
- Comunicação com profissionais
- Histórico de evolução

### 2.4 Criança/Paciente
**Características:**
- Sujeito da avaliação cognitiva
- Idade variável (foco em desenvolvimento infantil)
- Diferentes níveis de desenvolvimento

**Necessidades:**
- Avaliações apropriadas para idade e nível
- Acompanhamento personalizado
- Registro de evolução e conquistas

---

## 3. Requisitos Funcionais

### 3.1 Gestão de Usuários e Autenticação

#### RF001 - Sistema de Login
- Login seguro com email e senha
- Recuperação de senha via email
- Proteção contra bots (reCAPTCHA)
- Sessões com timeout configurável

#### RF002 - Gestão de Papéis e Permissões
- Três papéis principais: Admin, Professional, Responsible
- Permissões granulares por funcionalidade
- Herança de permissões baseada em hierarquia
- Políticas de autorização customizadas

#### RF003 - Gestão de Perfis
- Cadastro completo com dados pessoais
- Upload de foto de perfil
- Edição de informações pessoais
- Histórico de atividades

### 3.2 Gestão de Crianças/Pacientes

#### RF004 - Cadastro de Crianças
- Informações completas (nome, data nascimento, diagnóstico)
- Upload de foto
- Vínculo com responsáveis legais
- Associação com profissionais

#### RF005 - Ficha do Paciente
- Visualização consolidada de informações
- Histórico de avaliações
- Documentos anexados
- Observações e anotações

#### RF006 - Busca e Filtros
- Busca por nome, idade, responsável
- Filtros por profissional associado
- Filtros por status de avaliação
- Ordenação por diferentes critérios

### 3.3 Sistema de Avaliação Cognitiva

#### RF007 - Checklists de Avaliação
- Criação de checklists personalizados
- Agrupamento por competências
- Aplicação de avaliações
- Salvamento automático de progresso

#### RF008 - Competências e Domínios
- Cadastro de competências cognitivas
- Organização por domínios (áreas cognitivas)
- Níveis de dificuldade/desenvolvimento
- Critérios de avaliação padronizados

#### RF009 - Planos de Avaliação
- Geração automática baseada em resultados
- Protocolos pré-definidos
- Personalização por necessidade
- Sequenciamento de avaliações

#### RF010 - Registro de Progresso
- Percentual de conclusão
- Cálculo de percentis
- Comparação com referências
- Marcos de desenvolvimento

### 3.4 Relatórios e Analytics

#### RF011 - Dashboard Principal
- Métricas gerais da clínica
- Atividades recentes
- Alertas e notificações
- Gráficos de tendências

#### RF012 - Relatórios de Avaliação
- Geração de PDF detalhado
- Gráficos de evolução
- Comparativos temporais
- Exportação em múltiplos formatos

#### RF013 - Visualização de Progresso
- Gráficos interativos (Chart.js)
- Linha do tempo de evolução
- Comparação entre competências
- Análise de tendências

### 3.5 Gestão de Profissionais

#### RF014 - Cadastro de Profissionais
- Informações profissionais completas
- Especialidades e credenciais
- Agenda e disponibilidade
- Foto e documentos

#### RF015 - Atribuição de Pacientes
- Associação profissional-criança
- Transferência de casos
- Colaboração em equipe
- Histórico de atendimentos

### 3.6 Gestão de Responsáveis

#### RF016 - Cadastro de Responsáveis
- Dados pessoais e contato
- Grau de parentesco
- Múltiplos responsáveis por criança
- Permissões de acesso

#### RF017 - Portal do Responsável
- Acesso a relatórios
- Visualização de progresso
- Agendamentos
- Comunicação com profissionais

---

## 4. Requisitos Não-Funcionais

### 4.1 Performance
- Tempo de carregamento < 3 segundos
- Suporte a 100+ usuários simultâneos
- Otimização de queries (eager loading)
- Cache de dados frequentes

### 4.2 Segurança
- Criptografia de dados sensíveis
- Autenticação robusta
- Autorização baseada em papéis
- Logs de auditoria
- Proteção contra SQL Injection e XSS
- HTTPS obrigatório em produção

### 4.3 Usabilidade
- Interface responsiva (mobile-first)
- Compatibilidade com principais navegadores
- Acessibilidade (WCAG 2.1)
- Feedback visual para ações
- Navegação intuitiva

### 4.4 Escalabilidade
- Arquitetura modular
- Separação de camadas (MVC)
- API RESTful para integrações
- Suporte a múltiplas clínicas (multi-tenancy)

### 4.5 Confiabilidade
- Backup automático diário
- Recuperação de desastres
- Uptime 99.9%
- Tratamento de erros robusto
- Logs detalhados para debugging

---

## 5. Arquitetura Técnica

### 5.1 Stack Tecnológico

#### Backend
- **Framework**: Laravel 9 (PHP 8.1)
- **Banco de Dados**: MySQL 8.0
- **Cache**: Laravel Cache (File/Redis)
- **Queue**: Laravel Queue
- **Autenticação**: Laravel Sanctum + Spatie Permissions

#### Frontend
- **Framework**: Vue 3
- **CSS Framework**: Bootstrap 5
- **Gráficos**: Chart.js + vue-chart-3
- **Validação**: vee-validate
- **Notificações**: SweetAlert2
- **Build**: Laravel Mix (Webpack)

#### Infraestrutura
- **Containerização**: Docker + Docker Compose
- **Servidor Web**: Nginx
- **Email**: MailHog (desenvolvimento) / SMTP (produção)
- **Monitoramento**: Laravel Log Viewer

### 5.2 Estrutura de Dados Principal

#### Entidades Centrais
1. **User**: Usuários do sistema com autenticação
2. **Kid**: Crianças/pacientes sendo avaliados
3. **Professional**: Profissionais de saúde
4. **Responsible**: Responsáveis legais
5. **Checklist**: Protocolos de avaliação
6. **Competence**: Habilidades sendo avaliadas
7. **Domain**: Áreas cognitivas
8. **Level**: Níveis de desenvolvimento
9. **Plane**: Planos de avaliação

#### Relacionamentos Chave
- Kids **pertencem a** Responsibles (1:N)
- Kids **são atendidos por** Professionals (N:N)
- Checklists **contêm** Competences (N:N)
- Competences **pertencem a** Domains e Levels
- Users **têm** Roles e Permissions

---

## 6. Fluxos Principais

### 6.1 Fluxo de Avaliação
1. Profissional seleciona criança
2. Escolhe ou cria checklist de avaliação
3. Aplica avaliação item por item
4. Sistema calcula scores automaticamente
5. Gera relatório com resultados
6. Compartilha com responsáveis

### 6.2 Fluxo de Cadastro de Criança
1. Profissional/Admin acessa cadastro
2. Preenche dados da criança
3. Vincula responsáveis
4. Associa profissionais
5. Define plano inicial
6. Sistema cria ficha do paciente

### 6.3 Fluxo de Visualização de Progresso
1. Usuário acessa dashboard
2. Seleciona criança
3. Escolhe período de análise
4. Sistema gera gráficos
5. Compara com referências
6. Exporta relatório se necessário

---

## 7. Roadmap e Prioridades

### Fase 1 - MVP (Concluído)
- ✅ Sistema de autenticação e autorização
- ✅ Cadastro básico de entidades
- ✅ Aplicação de checklists
- ✅ Geração de relatórios PDF
- ✅ Dashboard com métricas básicas

### Fase 2 - Melhorias (Em Andamento)
- 🔄 Otimização de performance
- 🔄 Melhorias de UX/UI
- 🔄 Novos tipos de gráficos
- 🔄 Sistema de notificações
- 🔄 API para integrações

### Fase 3 - Expansão (Futuro)
- 📋 App mobile
- 📋 Inteligência artificial para insights
- 📋 Gamificação para crianças
- 📋 Marketplace de protocolos
- 📋 Teleconsulta integrada

---

## 8. Métricas de Sucesso

### 8.1 Métricas de Produto
- Número de avaliações realizadas/mês
- Tempo médio de aplicação de checklist
- Taxa de conclusão de avaliações
- Satisfação dos usuários (NPS)

### 8.2 Métricas de Negócio
- Número de clínicas utilizando
- Retenção de clientes
- Ticket médio por clínica
- Crescimento mensal de usuários

### 8.3 Métricas Técnicas
- Uptime do sistema
- Tempo de resposta médio
- Taxa de erros
- Cobertura de testes

---

## 9. Riscos e Mitigações

### 9.1 Riscos Técnicos
| Risco | Probabilidade | Impacto | Mitigação |
|-------|--------------|---------|-----------|
| Perda de dados | Baixa | Alto | Backups automáticos diários |
| Vulnerabilidade de segurança | Média | Alto | Auditorias regulares, updates |
| Performance degradada | Média | Médio | Monitoramento, otimização contínua |

### 9.2 Riscos de Produto
| Risco | Probabilidade | Impacto | Mitigação |
|-------|--------------|---------|-----------|
| Baixa adoção | Média | Alto | Treinamento, suporte ativo |
| Complexidade de uso | Média | Médio | UX research, iterações |
| Resistência à mudança | Alta | Médio | Change management, benefícios claros |

---

## 10. Considerações Legais e Compliance

### 10.1 Proteção de Dados
- Conformidade com LGPD (Lei Geral de Proteção de Dados)
- Consentimento para coleta de dados
- Direito ao esquecimento
- Portabilidade de dados

### 10.2 Dados de Menores
- Autorização dos responsáveis legais
- Proteção especial para dados sensíveis
- Restrições de acesso
- Anonimização quando possível

### 10.3 Ética Profissional
- Sigilo profissional
- Código de ética da psicologia
- Termo de uso e privacidade
- Responsabilidade sobre laudos

---

## 11. Suporte e Manutenção

### 11.1 Canais de Suporte
- Email de suporte
- Base de conhecimento
- Tutoriais em vídeo
- Chat ao vivo (futuro)

### 11.2 SLA (Service Level Agreement)
- Disponibilidade: 99.9%
- Tempo de resposta: < 24h
- Resolução crítica: < 4h
- Updates de segurança: < 48h

### 11.3 Manutenção
- Janela de manutenção: Domingos 2h-6h
- Updates mensais de features
- Patches de segurança imediatos
- Backup antes de cada update

---

## 12. Anexos

### 12.1 Glossário
- **Checklist**: Protocolo de avaliação com múltiplos itens
- **Competência**: Habilidade específica sendo avaliada
- **Domínio**: Área cognitiva (ex: linguagem, motor, social)
- **Nível**: Grau de desenvolvimento ou dificuldade
- **Percentil**: Posição relativa em comparação com referência
- **Plano**: Sequência estruturada de avaliações

### 12.2 Referências
- Site em produção: maieuticavaliacom.br
- Documentação técnica: /docs
- Repositório: [privado]
- Wiki do projeto: [interno]

---

*Documento atualizado em: Janeiro 2025*
*Versão: 1.0*
*Status: Em desenvolvimento ativo*