# PRD - Maiêutica
## Product Requirements Document

**Versão:** 1.0
**Data:** Janeiro 2025
**Status:** Em Produção
**URL:** maieuticavaliacom.br

---

## 1. Visão Geral do Produto

### 1.1 Propósito
Maiêutica é uma plataforma web especializada em **avaliação cognitiva infantil** para profissionais de psicologia clínica. O sistema permite o acompanhamento longitudinal do desenvolvimento cognitivo de crianças através de checklists estruturados, gerando análises quantitativas e planos de intervenção personalizados.

### 1.2 Objetivos do Produto
- Padronizar o processo de avaliação cognitiva infantil
- Facilitar o acompanhamento da evolução do paciente ao longo do tempo
- Automatizar a geração de relatórios e análises visuais
- Centralizar a gestão de pacientes, avaliações e profissionais
- Gerar planos de desenvolvimento baseados em evidências (dados coletados)

### 1.3 Público-Alvo
- **Primário:** Psicólogos clínicos especializados em atendimento infantil
- **Secundário:** Coordenadores de clínicas, supervisores, gestores de equipes
- **Beneficiários:** Crianças em processo de avaliação e seus responsáveis

---

## 2. Problemas a Resolver

### 2.1 Dores Identificadas
1. **Falta de padronização:** Avaliações manuais inconsistentes entre profissionais
2. **Dificuldade de tracking:** Complexo comparar evolução entre sessões
3. **Tempo de análise:** Cálculos manuais de percentuais e estatísticas
4. **Relatórios demorados:** Geração manual de documentos extensos
5. **Organização dispersa:** Dados espalhados em planilhas e documentos físicos

### 2.2 Solução Proposta
Sistema centralizado que:
- Oferece checklists estruturados com escalas padronizadas (0-3)
- Calcula automaticamente percentuais de desenvolvimento
- Gera gráficos comparativos entre avaliações
- Produz relatórios PDF profissionais instantaneamente
- Armazena histórico completo de cada paciente

---

## 3. Funcionalidades Principais

### 3.1 Gestão de Pacientes (Kids)

#### FR-001: Cadastro de Crianças
**Prioridade:** Alta
**Status:** Implementado

**Descrição:**
Sistema de registro completo de pacientes pediátricos com informações demográficas e clínicas.

**Requisitos Funcionais:**
- Cadastro com nome, data de nascimento, CPF
- Vínculo com responsável/guardião (nome, telefone, email)
- Atribuição a profissional responsável
- Upload de foto do paciente (opcional)
- Campo de observações clínicas
- Soft delete (exclusão lógica)

**Regras de Negócio:**
- CPF deve ser válido (validação brasileira)
- Data de nascimento não pode ser futura
- Um paciente pode ter múltiplos profissionais atribuídos
- Apenas profissionais autorizados visualizam dados do paciente

**Tela Relacionada:** `resources/views/kids/index.blade.php`, `KidsController.php`

---

#### FR-002: Dashboard do Paciente
**Prioridade:** Alta
**Status:** Implementado

**Descrição:**
Visão consolidada do histórico de avaliações e evolução do paciente.

**Requisitos Funcionais:**
- Timeline de checklists realizados
- Gráficos de evolução por domínio cognitivo
- Comparação entre avaliações (primeira vs. última)
- Listagem de planos de desenvolvimento ativos
- Exportação de relatório geral em PDF

**Métricas Exibidas:**
- Percentual de desenvolvimento geral
- Evolução por domínio (gráfico radar)
- Competências emergentes vs. consolidadas
- Taxa de progresso entre avaliações

**Tela Relacionada:** `KidsController@overview`

---

### 3.2 Sistema de Avaliação (Checklists)

#### FR-003: Criação de Checklist
**Prioridade:** Alta
**Status:** Implementado

**Descrição:**
Interface para profissionais realizarem avaliações cognitivas estruturadas.

**Requisitos Funcionais:**
- Seleção de paciente (Kid)
- Data da avaliação
- Listagem de todas as competências disponíveis
- Filtros por domínio e nível de dificuldade
- Sistema de notas: 0 (não testado), 1 (emergente), 2 (inconsistente), 3 (consistente)
- Campo de observações gerais
- Salvamento parcial (rascunho)
- Clonagem de checklist anterior para reavaliação

**Regras de Negócio:**
- Um checklist deve ter ao menos uma competência avaliada
- Notas devem estar no range 0-3
- Data não pode ser futura
- Profissional só edita seus próprios checklists (ou se tiver permissão admin)

**Cálculos Automáticos:**
- Percentual de desenvolvimento por domínio: `(soma das notas / (total de competências * 3)) * 100`
- Percentual geral: média dos percentuais por domínio

**Tela Relacionada:** `ChecklistController.php`, `components/Competences.vue`

---

#### FR-004: Visualização de Checklist
**Prioridade:** Alta
**Status:** Implementado

**Descrição:**
Exibição detalhada de uma avaliação com análises visuais.

**Requisitos Funcionais:**
- Tabela com todas as competências avaliadas e suas notas
- Gráfico radar com percentuais por domínio
- Percentual de desenvolvimento geral destacado
- Observações do profissional
- Opção de exportar em PDF
- Comparação com checklist anterior (se existir)

**Visualizações:**
- Gráfico radar Chart.js com 6+ domínios cognitivos
- Código de cores: vermelho (0-40%), amarelo (41-70%), verde (71-100%)
- Gráfico de linha para evolução temporal

**Tela Relacionada:** `ChecklistController@show`, `components/Charts.vue`

---

### 3.3 Biblioteca de Competências

#### FR-005: Gestão de Competências
**Prioridade:** Alta
**Status:** Implementado

**Descrição:**
CRUD completo de habilidades cognitivas que compõem as avaliações.

**Requisitos Funcionais:**
- Cadastro de competência com descrição detalhada
- Classificação por domínio (área cognitiva)
- Classificação por nível de dificuldade/complexidade
- Busca e filtros (por domínio, nível, texto)
- Edição e exclusão (soft delete)
- Ordenação customizada

**Estrutura:**
```
Competência
  ├── Descrição (texto detalhado da habilidade)
  ├── Domínio (ex: Linguagem, Memória, Atenção, Raciocínio)
  └── Nível (ex: 1-Básico, 2-Intermediário, 3-Avançado)
```

**Regras de Negócio:**
- Competências em uso (em checklists) não podem ser deletadas fisicamente
- Descrição deve ser única
- Todo domínio deve ter ao menos uma competência

**Tela Relacionada:** `CompetencesController.php`, `components/Competences.vue`

---

#### FR-006: Domínios Cognitivos
**Prioridade:** Média
**Status:** Implementado

**Descrição:**
Gestão de agrupamentos de competências por áreas cognitivas.

**Requisitos Funcionais:**
- CRUD de domínios
- Listagem de competências por domínio
- Contagem de competências ativas

**Exemplos de Domínios:**
- Linguagem Receptiva
- Linguagem Expressiva
- Memória de Trabalho
- Atenção Seletiva
- Raciocínio Lógico
- Funções Executivas
- Habilidades Sociais

**Tela Relacionada:** `DomainsController.php`

---

#### FR-007: Níveis de Complexidade
**Prioridade:** Média
**Status:** Implementado

**Descrição:**
Sistema de classificação de dificuldade das competências.

**Requisitos Funcionais:**
- CRUD de níveis
- Ordenação por complexidade crescente
- Vinculação com competências

**Uso:** Permite análise de quais níveis de dificuldade a criança já domina.

**Tela Relacionada:** `LevelsController.php`

---

### 3.4 Planos de Desenvolvimento

#### FR-008: Criação de Planos
**Prioridade:** Alta
**Status:** Implementado

**Descrição:**
Geração de planos de intervenção baseados em competências identificadas como frágeis.

**Requisitos Funcionais:**
- Vinculação a um paciente (Kid)
- Título e descrição do plano
- Seleção de competências-alvo (múltiplas)
- Data de início e previsão de término
- Status (ativo, em andamento, concluído)
- Estratégias de intervenção (campo texto)

**Sugestão Automática:**
- Sistema sugere competências com nota ≤ 1 no último checklist
- Profissional pode adicionar/remover manualmente

**Regras de Negócio:**
- Um plano deve ter ao menos uma competência-alvo
- Planos inativos não aparecem no dashboard principal
- Apenas o profissional responsável pode editar

**Tela Relacionada:** `PlanesController.php`, `components/Planes.vue`

---

#### FR-009: Acompanhamento de Planos
**Prioridade:** Média
**Status:** Implementado

**Descrição:**
Tracking de progresso dos planos de desenvolvimento.

**Requisitos Funcionais:**
- Listagem de todas as competências do plano
- Comparação com avaliações subsequentes
- Marcação de competências atingidas
- Observações de evolução
- Notificação de prazos próximos (futuro)

**Tela Relacionada:** `PlanesController@show`

---

### 3.5 Relatórios e Exportações

#### FR-010: Geração de PDF
**Prioridade:** Alta
**Status:** Implementado

**Descrição:**
Exportação de avaliações e visão geral em formato PDF profissional.

**Tipos de Relatório:**
1. **Checklist Individual:** Avaliação completa com gráficos
2. **Overview do Paciente:** Histórico completo com evolução
3. **Plano de Desenvolvimento:** Documento para compartilhar com equipe

**Conteúdo Padrão:**
- Cabeçalho com logo e dados da clínica
- Dados do paciente e profissional
- Tabelas de competências e notas
- Gráficos radar (Chart.js convertido para imagem)
- Observações clínicas
- Rodapé com data de geração

**Tecnologia:** DomPDF e TCPDF

**Tela Relacionada:** `KidsController@exportPdf`, `ChecklistController@exportPdf`

---

### 3.6 Gestão de Usuários e Permissões

#### FR-011: Sistema de Autenticação
**Prioridade:** Crítica
**Status:** Implementado

**Descrição:**
Login seguro com suporte a múltiplos papéis.

**Requisitos Funcionais:**
- Login com email e senha
- Opção "Lembrar-me" (30 dias)
- Recuperação de senha via email
- ReCAPTCHA v3 para proteção contra bots
- Logout com invalidação de sessão

**Regras de Segurança:**
- Senhas com hash bcrypt
- Sessões expiram após 120 minutos de inatividade
- Tentativas de login limitadas (throttling)

**Tela Relacionada:** `AuthController.php`, `resources/views/auth/`

---

#### FR-012: Controle de Acesso (RBAC)
**Prioridade:** Alta
**Status:** Implementado

**Descrição:**
Sistema de roles e permissões usando Spatie Laravel Permission.

**Papéis Padrão:**
- **Super Admin:** Acesso total ao sistema
- **Admin:** Gestão de usuários e configurações
- **Profissional:** Cria/edita pacientes e avaliações próprias
- **Visualizador:** Apenas leitura (para supervisores)

**Permissões por Recurso:**
- `kids.view`, `kids.create`, `kids.edit`, `kids.delete`
- `checklists.view`, `checklists.create`, `checklists.edit`, `checklists.delete`
- `competences.manage` (CRUD completo)
- `users.manage` (gestão de equipe)

**Regras:**
- Profissionais só veem seus próprios pacientes (a menos que tenham permissão especial)
- Admins veem todos os dados
- Logs de auditoria para ações sensíveis (futuro)

**Tela Relacionada:** `Policies/`, middleware auth

---

### 3.7 Dashboard e Analytics

#### FR-013: Dashboard Principal
**Prioridade:** Alta
**Status:** Implementado

**Descrição:**
Visão geral das atividades e métricas do profissional/clínica.

**Widgets:**
- Total de pacientes ativos
- Avaliações realizadas no mês
- Planos de desenvolvimento ativos
- Últimas avaliações (timeline)
- Gráfico de avaliações por mês (últimos 6 meses)
- Alertas: pacientes sem reavaliação há mais de 6 meses

**Filtros:**
- Por profissional (se admin)
- Por período
- Por status de paciente (ativo/inativo)

**Tela Relacionada:** `DashboardController.php`

---

## 4. Requisitos Não-Funcionais

### 4.1 Performance
- **Tempo de resposta:** Páginas devem carregar em < 2 segundos
- **Geração de PDF:** < 5 segundos para relatórios de até 20 páginas
- **Suporte:** Até 100 usuários simultâneos
- **Otimização:** Laravel Page Speed middleware (minify HTML/CSS/JS)

### 4.2 Segurança
- **Autenticação:** Laravel Sanctum
- **Criptografia:** Senhas com bcrypt (cost 10)
- **HTTPS:** Obrigatório em produção
- **CSRF:** Proteção em todos os formulários
- **XSS:** Sanitização de inputs com validação Laravel
- **SQL Injection:** Eloquent ORM com prepared statements
- **ReCAPTCHA:** v3 para proteção de formulários

### 4.3 Usabilidade
- **Responsividade:** Bootstrap 5, mobile-first
- **Acessibilidade:** WCAG 2.1 nível AA
- **Internacionalização:** PT-BR completo (datas, moeda, validações)
- **Feedback:** SweetAlert2 para mensagens de sucesso/erro
- **Ajuda contextual:** Tooltips e placeholders descritivos

### 4.4 Compatibilidade
- **Navegadores:** Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **Dispositivos:** Desktop (primário), Tablet (secundário), Mobile (básico)
- **Resolução mínima:** 1366x768

### 4.5 Manutenibilidade
- **Código:** PSR-12 (PHP), ESLint (JavaScript)
- **Documentação:** PHPDoc em classes/métodos críticos
- **Logs:** Laravel Log (daily rotation)
- **Versionamento:** Git com conventional commits
- **Testes:** PHPUnit (cobertura alvo: 70%)

### 4.6 Escalabilidade
- **Arquitetura:** Monolito modular (preparado para microserviços)
- **Banco de dados:** Índices em chaves estrangeiras e campos de busca
- **Cache:** Laravel Cache (Redis em produção)
- **Filas:** Laravel Queues para emails e relatórios pesados (futuro)

### 4.7 Disponibilidade
- **Uptime:** 99.5% (objetivo)
- **Backup:** Diário (banco de dados + arquivos)
- **Manutenção:** Janelas comunicadas com 48h de antecedência

---

## 5. Fluxos de Usuário

### 5.1 Fluxo: Primeira Avaliação de Paciente

```
1. Profissional faz login
2. Acessa "Pacientes" → "Novo Paciente"
3. Preenche formulário (nome, CPF, nascimento, responsável)
4. Salva paciente
5. Sistema redireciona para "Novo Checklist"
6. Seleciona competências por domínio
7. Atribui notas (0-3) para cada competência testada
8. Adiciona observações gerais
9. Salva checklist
10. Sistema calcula percentuais automaticamente
11. Exibe dashboard com gráfico radar
12. Profissional exporta PDF para compartilhar
```

### 5.2 Fluxo: Reavaliação após 3 Meses

```
1. Profissional acessa ficha do paciente
2. Clica em "Novo Checklist"
3. Sistema oferece opção: "Clonar checklist anterior?"
4. Profissional confirma
5. Checklist pré-preenchido com competências anteriores (notas zeradas)
6. Profissional reavalia cada competência
7. Sistema compara automaticamente com avaliação anterior
8. Exibe gráfico de evolução (linha temporal)
9. Destaca competências com maior/menor progresso
10. Profissional gera relatório comparativo em PDF
```

### 5.3 Fluxo: Criação de Plano de Desenvolvimento

```
1. Profissional analisa checklist do paciente
2. Sistema sugere competências com nota ≤ 1
3. Profissional acessa "Novo Plano"
4. Seleciona competências-alvo (aceita sugestões ou customiza)
5. Define título: "Plano de Intervenção - Linguagem Expressiva"
6. Descreve estratégias de intervenção
7. Define prazo (ex: 6 meses)
8. Salva plano
9. Plano aparece no dashboard do paciente
10. Em reavaliações futuras, sistema compara evolução das competências-alvo
```

---

## 6. Métricas de Sucesso

### 6.1 KPIs do Produto
- **Adoção:** Número de profissionais ativos mensalmente
- **Engajamento:** Média de checklists criados por profissional/mês
- **Retenção:** % de profissionais que retornam após 30 dias
- **Eficiência:** Tempo médio para completar uma avaliação
- **Qualidade:** % de avaliações com observações preenchidas

### 6.2 KPIs Clínicos
- **Cobertura:** % de pacientes com reavaliação nos últimos 6 meses
- **Progresso:** Taxa média de evolução entre primeira e última avaliação
- **Planos:** % de pacientes com plano de desenvolvimento ativo
- **Domínios:** Distribuição de competências fracas por domínio (insights para treinamento)

---

## 7. Roadmap e Funcionalidades Futuras

### 7.1 Curto Prazo (3 meses)
- [ ] Notificações por email (lembrete de reavaliação)
- [ ] Filtros avançados no dashboard
- [ ] Exportação em Excel/CSV
- [ ] Modo escuro (dark mode)

### 7.2 Médio Prazo (6 meses)
- [ ] Sistema de agendamento de sessões
- [ ] Portal para pais/responsáveis (visualização de relatórios)
- [ ] Integração com prontuário eletrônico (PEP)
- [ ] Biblioteca de atividades terapêuticas por competência
- [ ] Comparação entre pacientes (análises agregadas e anônimas)

### 7.3 Longo Prazo (12 meses)
- [ ] Mobile app (iOS/Android)
- [ ] IA para sugestão de competências e planos
- [ ] Gamificação (badges para engajamento)
- [ ] Integração com calendários (Google/Outlook)
- [ ] Marketplace de templates de checklist (especialidades diferentes)

---

## 8. Restrições e Limitações

### 8.1 Técnicas
- **Navegador:** Requer JavaScript habilitado
- **Resolução:** Experiência otimizada para desktop (mobile secundário)
- **Upload:** Limite de 2MB para fotos de pacientes
- **Relatórios:** PDFs limitados a 50 páginas (performance)

### 8.2 Regulatórias
- **LGPD:** Dados de saúde com consentimento explícito
- **Sigilo:** Logs de acesso a dados sensíveis
- **Retenção:** Dados mantidos por 20 anos (CFP - Conselho Federal de Psicologia)
- **Anonimização:** Opção de exportar dados sem identificação

### 8.3 Negócio
- **Licenciamento:** Modelo SaaS com assinatura mensal/anual
- **Suporte:** Apenas via email/chat (sem telefone)
- **Uptime:** Sem SLA garantido (plano básico)

---

## 9. Riscos e Mitigações

| Risco | Probabilidade | Impacto | Mitigação |
|-------|---------------|---------|-----------|
| Vazamento de dados de pacientes | Baixa | Crítico | Criptografia, auditorias de segurança trimestrais, backup off-site |
| Indisponibilidade do sistema | Média | Alto | Monitoramento 24/7, failover automático, comunicação proativa |
| Perda de dados por bug | Baixa | Crítico | Backups diários, testes automatizados, code review obrigatório |
| Baixa adoção pelos profissionais | Média | Alto | Onboarding guiado, tutoriais em vídeo, suporte dedicado |
| Performance degradada com crescimento | Média | Médio | Otimização de queries, cache agressivo, CDN para assets |

---

## 10. Glossário

- **Checklist:** Formulário de avaliação cognitiva aplicado a um paciente em uma data específica
- **Competência:** Habilidade cognitiva específica avaliada (ex: "Identifica cores primárias")
- **Domínio:** Agrupamento de competências por área cognitiva (ex: Linguagem, Memória)
- **Kid:** Paciente/criança sendo avaliada
- **Nível:** Grau de dificuldade de uma competência (básico, intermediário, avançado)
- **Nota:** Classificação de 0-3 atribuída a uma competência em uma avaliação
- **Plano:** Documento de intervenção com competências-alvo e estratégias
- **Overview:** Dashboard consolidado do histórico de um paciente
- **Soft Delete:** Exclusão lógica (registro marcado como deletado mas não removido do banco)

---

## 11. Anexos

### 11.1 Stack Técnico Completo

**Backend:**
- Laravel 9.x (PHP 8.1+)
- MySQL 8.0 / MariaDB 10.6
- Composer 2.x

**Frontend:**
- Vue 3.5 (Options API)
- Bootstrap 5.3
- Chart.js 3.9
- SweetAlert2
- jQuery 3.x + DataTables
- Vee-Validate

**DevOps:**
- Git (GitHub/GitLab)
- Laravel Mix (Webpack)
- npm / Yarn

**Pacotes-Chave:**
- spatie/laravel-permission (RBAC)
- yajra/laravel-datatables-oracle
- barryvdh/laravel-dompdf
- biscolab/laravel-recaptcha
- arcanedev/log-viewer
- lucascudo/laravel-pt-br-localization

### 11.2 Estrutura de Diretórios

```
maieutica.test/
├── app/
│   ├── Http/Controllers/   # Lógica de requisições
│   ├── Models/             # Eloquent models
│   ├── Services/           # Business logic
│   ├── Policies/           # Autorização
│   └── helpers.php         # Funções globais
├── resources/
│   ├── views/              # Blade templates
│   ├── js/
│   │   └── components/     # Vue 3 SFCs
│   └── scss/               # Estilos customizados
├── database/
│   ├── migrations/         # Schema definitions
│   └── seeders/            # Dados iniciais
├── routes/
│   └── web.php             # Rotas da aplicação
├── tests/                  # PHPUnit tests
├── webpack.mix.js          # Build configuration
└── .env                    # Configurações ambiente
```

### 11.3 Modelo de Dados Simplificado

```sql
-- Principais tabelas e relações

kids (id, name, birth_date, cpf, guardian_name, professional_id)
  ├── checklists (id, kid_id, professional_id, date, observations)
  │     └── checklist_competence (checklist_id, competence_id, note[0-3])
  └── planes (id, kid_id, title, description, start_date, end_date)
        └── competence_planes (plane_id, competence_id, strategies)

competences (id, description, domain_id, level_id)
  ├── domains (id, name)
  └── levels (id, name, order)

users (id, name, email, password)
  ├── roles (id, name) [via model_has_roles]
  └── permissions (id, name) [via model_has_permissions]
```

---

## 12. Aprovações

| Stakeholder | Papel | Data | Assinatura |
|-------------|-------|------|------------|
| [Nome] | Product Owner | ___/___/___ | __________ |
| [Nome] | Tech Lead | ___/___/___ | __________ |
| [Nome] | UX Lead | ___/___/___ | __________ |

---

**Última atualização:** Janeiro 2025
**Próxima revisão:** Trimestral ou sob demanda para funcionalidades críticas
