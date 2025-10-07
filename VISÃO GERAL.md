# VISÃO GERAL - Sistema Maiêutica

## 📋 Índice

1. [O Que é o Maiêutica?](#o-que-é-o-maiêutica)
2. [Conceitos Fundamentais](#conceitos-fundamentais)
3. [Arquitetura Visual](#arquitetura-visual)
4. [Fluxo Completo do Sistema](#fluxo-completo-do-sistema)
5. [Jornada do Usuário](#jornada-do-usuário)
6. [Modelo de Dados](#modelo-de-dados)
7. [Componentes Principais](#componentes-principais)
8. [Integrações e Dependências](#integrações-e-dependências)

---

## O Que é o Maiêutica?

**Maiêutica** é um sistema web para psicólogos clínicos realizarem **avaliações cognitivas padronizadas** de crianças e acompanharem sua evolução ao longo do tempo.

### Problema que Resolve

❌ **Antes:**
- Avaliações em papel ou planilhas Excel
- Cálculos manuais de percentuais
- Dificuldade de comparar evolução entre sessões
- Relatórios demorados para gerar
- Dados dispersos e desorganizados

✅ **Depois (com Maiêutica):**
- Avaliações digitais padronizadas
- Cálculos automáticos em tempo real
- Gráficos comparativos instantâneos
- Relatórios PDF profissionais em 1 clique
- Histórico completo centralizado

---

## Conceitos Fundamentais

### 1. **Kid (Criança/Paciente)**
A entidade central do sistema. Representa o paciente que será avaliado.

**Contém:**
- Dados pessoais (nome, CPF, data de nascimento)
- Responsável/guardião (nome, telefone, email)
- Profissional(is) responsável(is)
- Histórico completo de avaliações

### 2. **Checklist (Avaliação)**
Um formulário preenchido em uma data específica para avaliar o desenvolvimento cognitivo da criança.

**Contém:**
- Data da avaliação
- Lista de competências avaliadas
- Notas atribuídas (0-3) para cada competência
- Observações do profissional
- Percentuais calculados automaticamente

### 3. **Competência (Habilidade Cognitiva)**
Uma habilidade específica que pode ser avaliada.

**Exemplo:** "Identifica cores primárias", "Mantém atenção por 5 minutos", "Reconhece números de 1 a 10"

**Características:**
- Pertence a um **Domínio** (área cognitiva)
- Tem um **Nível** de dificuldade
- É avaliada com notas de 0 a 3

### 4. **Domínio (Área Cognitiva)**
Agrupamento de competências relacionadas.

**Exemplos:**
- Linguagem Receptiva
- Linguagem Expressiva
- Memória de Trabalho
- Atenção Seletiva
- Raciocínio Lógico
- Funções Executivas
- Habilidades Sociais

### 5. **Nível (Complexidade)**
Grau de dificuldade de uma competência.

**Exemplos:**
- Nível 1: Básico
- Nível 2: Intermediário
- Nível 3: Avançado

### 6. **Escala de Notas**
Sistema de pontuação usado nas avaliações:

- **0** = Não testado (competência não foi avaliada)
- **1** = Emergente (criança está começando a desenvolver)
- **2** = Inconsistente (às vezes demonstra a habilidade)
- **3** = Consistente (habilidade totalmente desenvolvida)

### 7. **Plano de Desenvolvimento**
Documento de intervenção que define quais competências serão trabalhadas e como.

**Contém:**
- Competências-alvo (geralmente as mais fracas)
- Estratégias de intervenção
- Prazo (data início e fim)
- Status (ativo, em andamento, concluído)

---

## Arquitetura Visual

```
┌─────────────────────────────────────────────────────────────────┐
│                         MAIÊUTICA SYSTEM                         │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────┐
│   PROFISSIONAL  │  (Psicólogo Clínico)
└────────┬────────┘
         │ faz login
         ▼
┌─────────────────────────────────────────────────────────────────┐
│                         DASHBOARD                                │
│  • Total de pacientes                                            │
│  • Avaliações do mês                                             │
│  • Planos ativos                                                 │
│  • Alertas (pacientes sem reavaliação)                           │
└───────────┬─────────────────────────────────────────────────────┘
            │
            ├──────────────┬──────────────┬──────────────┐
            ▼              ▼              ▼              ▼
    ┌──────────┐   ┌──────────┐   ┌──────────┐   ┌──────────┐
    │   KIDS   │   │CHECKLISTS│   │COMPETÊNCIAS│  │  PLANOS  │
    │(Pacientes│   │(Avaliações│   │(Biblioteca)│  │(Intervenção)│
    └────┬─────┘   └────┬─────┘   └────┬─────┘   └────┬─────┘
         │              │              │              │
         │              │              │              │
    ┌────▼─────────────▼──────────────▼──────────────▼─────┐
    │              BANCO DE DADOS MySQL                     │
    │  kids | checklists | competences | domains | levels  │
    │  checklist_competence | planes | competence_planes   │
    └────────────────────────────────────────────────────────┘
                           │
                           ▼
                  ┌─────────────────┐
                  │  RELATÓRIOS PDF  │
                  │  • Checklist     │
                  │  • Overview      │
                  │  • Plano         │
                  └─────────────────┘
```

---

## Fluxo Completo do Sistema

### 🔄 Ciclo de Avaliação Cognitiva

```
1. CADASTRO DO PACIENTE
   ↓
   Profissional cadastra criança com dados básicos
   (nome, CPF, nascimento, responsável)

2. PRIMEIRA AVALIAÇÃO
   ↓
   Cria Checklist → Seleciona competências → Atribui notas (0-3)
   ↓
   Sistema calcula percentuais automaticamente:
   • Por domínio: (soma das notas / (total competências × 3)) × 100
   • Geral: média dos percentuais de todos os domínios

3. VISUALIZAÇÃO E ANÁLISE
   ↓
   • Gráfico radar mostra perfil cognitivo
   • Tabelas mostram detalhamento por competência
   • Código de cores: vermelho (0-40%), amarelo (41-70%), verde (71-100%)

4. GERAÇÃO DE PLANO
   ↓
   Sistema sugere competências com nota ≤ 1
   ↓
   Profissional seleciona competências-alvo e define estratégias

5. INTERVENÇÃO
   ↓
   Trabalho terapêutico durante 3-6 meses

6. REAVALIAÇÃO
   ↓
   Profissional clona checklist anterior
   ↓
   Reavalia as mesmas competências
   ↓
   Sistema compara automaticamente com avaliação anterior
   ↓
   Gera gráfico de evolução temporal

7. RELATÓRIO FINAL
   ↓
   Exporta PDF com análise comparativa
   ↓
   Compartilha com equipe/responsáveis
```

---

## Jornada do Usuário

### 👤 Persona: Dra. Ana (Psicóloga Clínica)

#### **Cenário 1: Novo Paciente**

```
08:00 - Dra. Ana faz login no Maiêutica
        └─> Dashboard mostra: 12 pacientes, 3 avaliações pendentes

08:15 - Chega novo paciente: João, 5 anos
        └─> Acessa "Pacientes" → "Novo Paciente"
        └─> Preenche: nome, CPF, nascimento, dados do responsável
        └─> Salva (redireciona para "Novo Checklist")

08:20 - Inicia primeira avaliação
        └─> Filtra competências por "Linguagem Receptiva"
        └─> Testa 8 competências, atribui notas
        └─> Filtra "Memória de Trabalho"
        └─> Testa 6 competências
        └─> Adiciona observação: "Criança cooperativa, mas dispersa"
        └─> Salva checklist

08:45 - Visualiza resultado
        └─> Gráfico radar mostra:
            • Linguagem Receptiva: 45% (amarelo)
            • Memória de Trabalho: 30% (vermelho)
            • Atenção: 60% (amarelo)
        └─> Percentual geral: 45%

09:00 - Cria plano de desenvolvimento
        └─> Sistema sugere 5 competências com nota ≤ 1
        └─> Dra. Ana seleciona 3 competências-alvo
        └─> Escreve estratégias de intervenção
        └─> Define prazo: 6 meses
        └─> Salva plano

09:15 - Exporta PDF
        └─> Gera relatório completo com gráficos
        └─> Compartilha com escola do João
```

#### **Cenário 2: Reavaliação após 3 Meses**

```
3 MESES DEPOIS...

10:00 - Dra. Ana acessa ficha do João
        └─> Vê histórico: 1 checklist anterior (3 meses atrás)
        └─> Clica em "Novo Checklist"

10:05 - Sistema oferece: "Clonar checklist anterior?"
        └─> Dra. Ana confirma
        └─> Checklist pré-preenchido com mesmas competências
        └─> Notas zeradas (para nova avaliação)

10:10 - Reavalia competências
        └─> Linguagem Receptiva: melhora significativa
        └─> Memória de Trabalho: progresso moderado
        └─> Salva checklist

10:30 - Sistema compara automaticamente
        └─> Gráfico de linha mostra evolução:
            • Linguagem Receptiva: 45% → 72% (+27%)
            • Memória de Trabalho: 30% → 55% (+25%)
            • Atenção: 60% → 75% (+15%)
        └─> Percentual geral: 45% → 67% (+22%)

10:45 - Gera relatório comparativo em PDF
        └─> Mostra lado a lado: primeira vs. segunda avaliação
        └─> Destaca competências com maior progresso
        └─> Compartilha com responsáveis
```

---

## Modelo de Dados

### 🗄️ Estrutura de Tabelas

```sql
-- PACIENTES
kids
├── id
├── name (nome da criança)
├── birth_date (data de nascimento)
├── cpf
├── guardian_name (responsável)
├── guardian_phone
├── guardian_email
├── professional_id (psicólogo responsável)
└── observations

-- AVALIAÇÕES
checklists
├── id
├── kid_id (FK → kids)
├── professional_id (FK → users)
├── date (data da avaliação)
└── observations

-- PIVÔ: AVALIAÇÃO ↔ COMPETÊNCIA
checklist_competence
├── checklist_id (FK → checklists)
├── competence_id (FK → competences)
└── note (0, 1, 2 ou 3) ⭐ NOTA ATRIBUÍDA

-- BIBLIOTECA DE HABILIDADES
competences
├── id
├── description (ex: "Identifica cores primárias")
├── domain_id (FK → domains)
└── level_id (FK → levels)

-- ÁREAS COGNITIVAS
domains
├── id
└── name (ex: "Linguagem Receptiva")

-- NÍVEIS DE DIFICULDADE
levels
├── id
├── name (ex: "Básico", "Intermediário", "Avançado")
└── order (ordenação)

-- PLANOS DE DESENVOLVIMENTO
planes
├── id
├── kid_id (FK → kids)
├── title
├── description
├── start_date
├── end_date
└── status

-- PIVÔ: PLANO ↔ COMPETÊNCIA
competence_planes
├── plane_id (FK → planes)
├── competence_id (FK → competences)
└── strategies (texto com estratégias)

-- USUÁRIOS E PERMISSÕES (Spatie)
users
├── id
├── name
├── email
└── password

roles (Super Admin, Admin, Profissional, Visualizador)
permissions (kids.view, kids.create, checklists.edit, etc.)
model_has_roles (pivô: user ↔ role)
model_has_permissions (pivô: user ↔ permission)
```

### 📊 Relacionamentos

```
Kid (1) ─────── (N) Checklists
              cada criança pode ter múltiplas avaliações

Checklist (N) ─────── (N) Competences
              através de checklist_competence (nota 0-3)

Competence (N) ─────── (1) Domain
              cada competência pertence a um domínio

Competence (N) ─────── (1) Level
              cada competência tem um nível

Kid (1) ─────── (N) Planes
              cada criança pode ter múltiplos planos

Plane (N) ─────── (N) Competences
              através de competence_planes

User (N) ─────── (N) Kids
              profissional responsável por pacientes
```

---

## Componentes Principais

### 🎯 Backend (Laravel)

```
app/Http/Controllers/
├── KidsController.php
│   ├── index() → lista todos os pacientes
│   ├── create() → formulário de cadastro
│   ├── store() → salva novo paciente
│   ├── show() → visualiza ficha do paciente
│   ├── overview() → dashboard com gráficos de evolução
│   └── exportPdf() → gera relatório PDF

├── ChecklistController.php
│   ├── index() → lista avaliações
│   ├── create() → formulário de nova avaliação
│   ├── store() → salva checklist com notas
│   ├── show() → visualiza checklist com gráficos
│   ├── clone() → duplica checklist anterior
│   └── exportPdf() → gera PDF da avaliação

├── CompetencesController.php
│   ├── index() → lista competências (filtros por domínio/nível)
│   ├── create/store/update/destroy() → CRUD
│   └── getByDomain() → API para filtros

├── PlanesController.php
│   ├── create() → formulário de novo plano
│   ├── store() → salva plano com competências-alvo
│   ├── show() → visualiza plano e progresso
│   └── suggestCompetences() → API que sugere competências fracas

└── DashboardController.php
    └── index() → métricas gerais do profissional
```

```
app/Services/
├── ChecklistService.php
│   ├── calculateDomainPercentages() → calcula % por domínio
│   └── calculateOverallPercentage() → calcula % geral

└── OverviewService.php
    ├── getEvolutionData() → dados para gráfico de linha
    └── getComparisonData() → primeira vs. última avaliação
```

```
app/Models/
├── Kid.php → Eloquent model com relationships
├── Checklist.php
├── Competence.php
├── Domain.php
├── Level.php
├── Plane.php
└── User.php
```

### 🎨 Frontend (Vue 3)

```
resources/js/components/
├── Competences.vue
│   ├── Lista competências com filtros
│   ├── Interface para atribuir notas (0-3)
│   └── Atualização em tempo real dos percentuais

├── Charts.vue
│   ├── Wrapper para Chart.js
│   ├── Gráfico radar (perfil cognitivo)
│   └── Gráfico de linha (evolução temporal)

├── Checklists.vue
│   ├── Listagem de avaliações
│   ├── Filtros por data, paciente
│   └── Ações: visualizar, editar, clonar, exportar PDF

└── Planes.vue
    ├── Formulário de criação de plano
    ├── Seleção de competências-alvo
    └── Acompanhamento de progresso
```

### 📄 Views (Blade)

```
resources/views/
├── dashboard.blade.php → página inicial com métricas
├── kids/
│   ├── index.blade.php → lista de pacientes (DataTables)
│   ├── create.blade.php → formulário de cadastro
│   ├── show.blade.php → ficha completa do paciente
│   └── overview.blade.php → gráficos de evolução
├── checklists/
│   ├── create.blade.php → formulário de avaliação (Vue)
│   └── show.blade.php → visualização com gráficos (Chart.js)
├── competences/
│   └── index.blade.php → biblioteca de competências
└── planes/
    ├── create.blade.php → novo plano
    └── show.blade.php → detalhes do plano
```

---

## Integrações e Dependências

### 📦 Pacotes Críticos

**Backend:**
- `spatie/laravel-permission` → Controle de acesso (roles/permissions)
- `yajra/laravel-datatables-oracle` → Tabelas com paginação server-side
- `barryvdh/laravel-dompdf` → Geração de PDFs
- `biscolab/laravel-recaptcha` → Proteção contra bots (formulários)
- `laravellegends/pt-br-validator` → Validação de CPF, telefone, datas BR

**Frontend:**
- `vue@3.5` → Framework reativo
- `chart.js@3.9` → Gráficos radar e linha
- `sweetalert2` → Alertas bonitos e responsivos
- `bootstrap@5.3` → UI framework
- `jquery-mask-plugin` → Máscaras de input (CPF, telefone)

### 🔐 Segurança

```
Camadas de Proteção:
├── Laravel Sanctum → Autenticação API
├── Spatie Permissions → Autorização RBAC
├── ReCAPTCHA v3 → Anti-bot
├── CSRF Tokens → Proteção de formulários
├── Bcrypt → Hash de senhas (cost 10)
├── Eloquent ORM → Prepared statements (anti SQL Injection)
└── Input Validation → Laravel Form Requests
```

### ⚡ Performance

```
Otimizações:
├── Laravel Page Speed → Middleware (minify HTML/CSS/JS)
├── Eager Loading → Eloquent (evita N+1 queries)
├── Redis Cache → Sessões e cache de dados (produção)
├── Asset Compilation → Laravel Mix (Webpack)
└── CDN → Bootstrap, jQuery, Chart.js (assets externos)
```

---

## Fluxo de Dados em Tempo Real

### Exemplo: Cálculo de Percentuais

```
PROFISSIONAL ATRIBUI NOTA
         ↓
┌─────────────────────┐
│ Competences.vue     │
│ (componente Vue)    │
│ @change="updateNote"│
└──────────┬──────────┘
           │ axios.post('/api/checklist/note')
           ▼
┌─────────────────────┐
│ ChecklistController │
│ updateNote()        │
└──────────┬──────────┘
           │ salva em checklist_competence
           │ chama ChecklistService
           ▼
┌─────────────────────┐
│ ChecklistService    │
│ calculatePercentage │
└──────────┬──────────┘
           │
           │ 1. Busca todas as notas do domínio
           │ 2. Soma as notas
           │ 3. Divide por (total × 3)
           │ 4. Multiplica por 100
           │
           ▼
┌─────────────────────┐
│ Retorna JSON        │
│ { "domain_id": 1,   │
│   "percentage": 67 }│
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Competences.vue     │
│ Atualiza interface  │
│ Gráfico em tempo    │
│ real (Chart.js)     │
└─────────────────────┘
```

---

## Pontos de Atenção

### ⚠️ Críticos

1. **Sistema em Produção**
   - Qualquer alteração pode impactar psicólogos ativos
   - Testar exaustivamente antes de deploy
   - Priorizar estabilidade sobre features

2. **Dados Sensíveis (LGPD)**
   - Informações de saúde de crianças
   - CPF, dados pessoais, responsáveis
   - Logs de acesso obrigatórios
   - Retenção de 20 anos (CFP)

3. **Cálculos Automáticos**
   - Percentuais devem ser precisos
   - Erro de cálculo compromete diagnóstico
   - Validar fórmulas com profissionais

### 🎯 Oportunidades de Melhoria

1. **Performance**
   - Gráficos com muitos dados podem travar
   - Considerar paginação em checklists com 100+ competências
   - Cache de relatórios PDF gerados

2. **UX/UI**
   - Interface mobile pode ser otimizada
   - Filtros de competências podem ser mais intuitivos
   - Adicionar atalhos de teclado

3. **Funcionalidades Futuras**
   - Notificações por email (lembretes de reavaliação)
   - Portal para pais visualizarem evolução
   - IA para sugestão de planos personalizados
   - App mobile nativo

---

## Comandos Rápidos

```bash
# Ambiente local
php artisan serve           # Inicia servidor em localhost:8000
npm run watch              # Recompila assets automaticamente

# Testes
php artisan test           # Roda suite de testes

# Dados
composer fresh             # Reseta banco e roda seeders
composer clear             # Limpa todos os caches

# Produção
npm run production         # Build otimizado
php artisan migrate        # Roda migrations
```

---

## Conclusão

O **Maiêutica** é um sistema robusto que resolve um problema real de psicólogos clínicos: **padronizar e automatizar avaliações cognitivas infantis**.

**Principais Diferenciais:**
✅ Cálculos automáticos precisos
✅ Visualizações gráficas intuitivas
✅ Histórico longitudinal completo
✅ Relatórios profissionais em 1 clique
✅ Conformidade LGPD

**Stack Sólido:**
- Laravel (backend confiável e seguro)
- Vue 3 (interface reativa)
- Chart.js (visualizações poderosas)
- Bootstrap 5 (UI responsiva)

**Status:** ✅ Em produção | maieuticavaliacom.br

---

_Documento criado para facilitar onboarding de desenvolvedores e análise de fluxos do sistema._
_Versão: 1.0 | Janeiro 2025_
