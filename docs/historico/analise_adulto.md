# Análise: Adaptação do Sistema Maiêutica para Múltiplos Tipos de Pacientes

**Data:** 27/12/2025
**Contexto:** Sistema originalmente desenvolvido para crianças 0-6 anos (Teste Denver II) precisa suportar crianças maiores e pacientes adultos
**Objetivo:** Identificar adaptações necessárias com **menor impacto possível**

---

## 📋 Sumário Executivo

### Situação Atual
O sistema Maiêutica foi arquitetado especificamente para:
- **Pacientes:** Crianças de 0 a 6 anos (modelo `Kid`)
- **Avaliação:** Teste de Denver II (checklists com competências baseadas em percentis de idade)
- **Profissionais:** Psicólogos que atendem crianças

### Necessidade Identificada
Na prática, a clínica atende:
1. ✅ **Crianças 0-6 anos** - Sistema funciona perfeitamente
2. ⚠️ **Crianças >6 anos** - Sistema NÃO foi pensado para isso
3. ⚠️ **Pacientes adultos** - Sistema tem suporte PARCIAL (prontuários sim, avaliação não)

### Decisão Estratégica
Após análise arquitetural e consulta com stakeholders:

| Tipo de Paciente | Modelo | Denver? | Prontuários? | Status |
|------------------|--------|---------|--------------|--------|
| Criança 0-6 anos | `Kid` | ✅ Sim | ✅ Sim | **Mantém como está** |
| Criança >6 anos | `Kid` | ❌ Não aplicável | ✅ Sim | **Sem limite de idade** |
| Adulto | `User` | ❌ Não | ✅ Sim | **Implementar atribuição** |

**Impacto:** MÍNIMO - Aproveita estrutura polimórfica existente

---

## 🏗️ Arquitetura Atual vs. Proposta

### ATUAL: Sistema Focado em Kids

```
┌─────────────────────────────────────────────────┐
│            PACIENTES (Apenas Kids)               │
├─────────────────────────────────────────────────┤
│                                                  │
│  Kid (0-6 anos)                                 │
│  ├── Checklists (Denver II)                     │
│  │   └── Competências (percentis)               │
│  ├── Planes (Planos de Desenvolvimento)         │
│  ├── Professionals (kid_professional)           │
│  ├── Responsible (pai/mãe)                      │
│  └── MedicalRecords (prontuários)               │
│                                                  │
└─────────────────────────────────────────────────┘

⚠️ PROBLEMA: E crianças >6 anos? E adultos?
```

### PROPOSTA: Sistema Multi-Tipo

```
┌─────────────────────────────────────────────────────────────┐
│                  PACIENTES (Múltiplos Tipos)                 │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  Kid (TODAS as idades)                                      │
│  ├── SE idade <= 6 anos:                                    │
│  │   ├── ✅ Checklists (Denver II)                          │
│  │   ├── ✅ Competências (percentis)                        │
│  │   └── ✅ Planes (planos)                                 │
│  ├── SE idade > 6 anos:                                     │
│  │   ├── ⚠️ Checklists (aviso: fora da faixa Denver)        │
│  │   └── ✅ APENAS Prontuários                              │
│  ├── Professionals (kid_professional) ✅                     │
│  ├── Responsible (pai/mãe) ✅                                │
│  └── MedicalRecords (prontuários) ✅                         │
│                                                              │
│  User (Pacientes Adultos)                                   │
│  ├── ❌ Checklists (não aplicável)                          │
│  ├── ❌ Competências Denver (não aplicável)                 │
│  ├── ✅ Professionals (professional_user_patient) ⚡ NOVO    │
│  └── ✅ MedicalRecords (prontuários)                        │
│                                                              │
└─────────────────────────────────────────────────────────────┘

✅ SOLUÇÃO: Aproveita polimorfismo de MedicalRecord
```

---

## 🔍 Análise Detalhada: Por Que o Sistema Quebra?

### 1. Modelo Kid - Hard-coded para Crianças

**Arquivo:** `app/Models/Kid.php`

#### A. Cálculo de Idade em Meses
```php
// Linha 174-182
public function getMonthsAttribute()
{
    return Carbon::parse($this->birth_date)->diffInMonths(Carbon::now());
}

// Usado para:
// - Validar níveis de checklist
// - Calcular percentis Denver
// - Mostrar idade ("6a 3m")
```

**Análise:**
- ✅ Funciona para qualquer idade (não há limite)
- ✅ Não precisa alteração
- ⚠️ Denver só faz sentido até ~72 meses (6 anos)

#### B. Relacionamento com Responsible (Pai/Mãe)
```php
// Linha 71-75
public function responsible()
{
    return $this->belongsTo(User::class, 'responsible_id');
}
```

**Análise:**
- ✅ Continua útil mesmo para crianças >6 anos
- ❌ Adultos NÃO têm responsável (são independentes)
- 💡 **Conclusão:** Manter apenas para Kids

---

### 2. Sistema Denver - Limitado a 0-6 Anos

**Arquivo:** `app/Models/Checklist.php`

#### A. Estrutura do Checklist
```php
Schema::create('checklists', function (Blueprint $table) {
    $table->id();
    $table->foreignId('kid_id')->constrained()->cascadeOnDelete();
    $table->integer('level'); // 1-4 (faixas etárias)
    $table->enum('situation', ['a', 'f']); // a=aberto, f=fechado
    $table->text('description')->nullable();
    // ...
});
```

**Problema Identificado:**
- `kid_id` é obrigatório (NOT NULL)
- NÃO há campo `patient_type` (polimórfico)
- NÃO suporta User (adultos)

**Análise:**
- ❌ Alterar para polimórfico = ALTO IMPACTO
- ✅ Manter exclusivo para Kids = MENOR IMPACTO
- 💡 **Decisão:** Checklists permanecem exclusivos para Kids

#### B. Competências com Percentis
```php
// Competence model
Schema::create('competences', function (Blueprint $table) {
    // ...
    $table->integer('percentil_25')->nullable();
    $table->integer('percentil_50')->nullable();
    $table->integer('percentil_75')->nullable();
    $table->integer('percentil_90')->nullable();
});
```

**Percentis = Idade em Meses Esperada**

Exemplo real (Competência: "Empilha 2 cubos"):
- `percentil_25`: 14 meses (25% das crianças conseguem com 14 meses)
- `percentil_50`: 15 meses (50% conseguem)
- `percentil_75`: 16 meses (75% conseguem)
- `percentil_90`: 18 meses (90% conseguem)

**Análise:**
- ✅ Faz sentido para 0-6 anos (0-72 meses)
- ❌ NÃO faz sentido para >6 anos (ex: "empilha cubos" aos 10 anos?)
- ❌ TOTALMENTE inaplicável para adultos
- 💡 **Conclusão:** Denver permanece 0-6 anos

#### C. Lógica de Avaliação (KidsController)
```php
// Linhas 1098-1166: Cálculo de status baseado em percentis
if ($ageInMonths < $competence->percentil_25) {
    // Criança muito nova, ainda não deveria saber
    $status = 'dentro_esperado';
} elseif ($note == 3 && $ageInMonths < $competence->percentil_50) {
    // Desenvolveu cedo!
    $status = 'adiantada';
    $color = 'blue';
} elseif ($note < 3 && $ageInMonths > $competence->percentil_90) {
    // Deveria ter desenvolvido mas não desenvolveu
    $status = 'atrasada';
    $color = 'red';
}
// ... mais 50 linhas de lógica complexa
```

**Análise:**
- ⚠️ Lógica MUITO acoplada a percentis
- ❌ Impossível adaptar para adultos sem reescrever tudo
- 💡 **Conclusão:** NÃO tentar adaptar

---

### 3. MedicalRecord - JÁ É POLIMÓRFICO ✅

**Arquivo:** `app/Models/MedicalRecord.php`

```php
// Relacionamento polimórfico (linha 42-45)
public function patient()
{
    return $this->morphTo();
}

// Tabela suporta múltiplos tipos
Schema::create('medical_records', function (Blueprint $table) {
    $table->morphs('patient'); // patient_id + patient_type
    // ...
});
```

**Análise:**
- ✅ JÁ funciona com Kid E User
- ✅ Admin consegue criar prontuários para adultos
- ❌ Profissionais NÃO conseguem (falta atribuição)
- 💡 **Conclusão:** Sistema perfeito, só precisa de atribuição

---

### 4. Atribuição Profissional-Paciente

#### A. Kids - FUNCIONA ✅

**Tabela:** `kid_professional`
```sql
CREATE TABLE kid_professional (
    id BIGINT PRIMARY KEY,
    kid_id BIGINT NOT NULL,
    professional_id BIGINT NOT NULL,
    is_primary BOOLEAN DEFAULT false,
    UNIQUE(kid_id, professional_id)
);
```

**Relacionamento:**
```php
// Kid.php
public function professionals() {
    return $this->belongsToMany(Professional::class, 'kid_professional');
}

// Professional.php
public function kids() {
    return $this->belongsToMany(Kid::class, 'kid_professional');
}
```

**Análise:**
- ✅ Many-to-many (um kid pode ter vários profissionais)
- ✅ Profissional vê apenas seus kids
- ✅ Sistema robusto e testado

#### B. Users (Adultos) - NÃO EXISTE ❌

**Código Atual (MedicalRecordsController, linha 602-612):**
```php
private function getUserPatientsForUser()
{
    if (auth()->user()->can('medical-record-list-all')) {
        // Admin sees all active users
        return User::where('allow', 1)->orderBy('name')->get();
    }

    // Professional sees only their assigned user patients
    // TODO: Implement when User->Professional relationship is defined
    return collect([]); // ❌ RETORNA VAZIO
}
```

**Impacto:**
- ✅ Admin vê todos os adultos
- ❌ Profissional vê ZERO adultos (dropdown vazio)
- ❌ Profissional NÃO consegue criar prontuários para adultos

**Análise:**
- 💡 Falta criar tabela `professional_user_patient` (igual a `kid_professional`)
- 💡 Falta relacionamento `Professional->patients()` e `User->assignedProfessionals()`
- 💡 **ESTA É A PRIORIDADE #1**

---

## 🎯 Estratégia de Menor Impacto

### Princípio KISS (Keep It Simple, Stupid)

> "A melhor solução é aquela que resolve o problema com menos código alterado."

### Decisões Arquiteturais

#### ✅ O QUE MANTER
1. **Kid como modelo único de criança**
   - Sem limite de idade
   - Continua tendo responsible, professionals, checklists
   - Denver disponível mas com aviso se >6 anos

2. **Denver exclusivo para 0-6 anos**
   - NÃO adaptar para outras idades
   - Mostrar aviso na UI se Kid tem >72 meses
   - Profissional decide se usa ou não

3. **MedicalRecord polimórfico**
   - JÁ funciona para Kid e User
   - Não precisa alteração
   - Prontuários são a forma universal de registro

#### ⚡ O QUE CRIAR
1. **Tabela `professional_user_patient`**
   - Igual a `kid_professional`
   - Many-to-many
   - Permite atribuir adultos a profissionais

2. **Relacionamentos em Professional e User**
   - `Professional->patients()` (retorna Users)
   - `User->assignedProfessionals()` (retorna Professionals)

3. **Interface de atribuição**
   - Página para admin atribuir adultos a profissionais
   - Similar à atribuição de Kids

#### ❌ O QUE NÃO FAZER
1. ❌ Alterar Checklist para polimórfico
2. ❌ Criar sistema Denver para adultos
3. ❌ Criar modelo separado "Adolescent"
4. ❌ Limitar idade máxima de Kid

---

## 📊 Comparação de Impacto: 3 Abordagens

### Abordagem 1: Polimorfismo Total (❌ NÃO RECOMENDADO)

**Ideia:** Fazer Checklist suportar Kid E User

```
MUDANÇAS:
- Alterar table checklists (kid_id → patient_id + patient_type)
- Migrar dados existentes
- Criar competências para adultos
- Adaptar toda lógica de percentis
- Refatorar 50+ arquivos
```

**Impacto:**
- ⚠️ Risco ALTO de quebrar funcionalidade existente
- ⚠️ Esforço: 80-120 horas
- ⚠️ Requer nova lógica de avaliação
- ❌ Denver não faz sentido para adultos

---

### Abordagem 2: Sistema Paralelo (⚠️ MÉDIO IMPACTO)

**Ideia:** Criar sistema de avaliação separado para adultos

```
MUDANÇAS:
- Criar table adult_assessments
- Criar table adult_competences
- Criar AdultAssessment model
- Criar AdultAssessmentController
- Criar views para avaliação de adultos
- Manter Checklist apenas para Kids
```

**Impacto:**
- ✅ NÃO quebra sistema existente
- ⚠️ Esforço: 40-60 horas
- ⚠️ Duplicação de código
- ⚠️ Manutenção duplicada
- ⚠️ Usuário precisa aprender 2 sistemas

---

### Abordagem 3: Prontuários Apenas (✅ RECOMENDADO - MENOR IMPACTO)

**Ideia:** Kids têm Denver, Adultos têm só prontuários

```
MUDANÇAS:
- Criar table professional_user_patient (pivot)
- Adicionar 2 relationships (Professional + User)
- Atualizar 1 método (getUserPatientsForUser)
- Atualizar 1 scope (forAuthProfessional)
- Criar 1 view (assign-patients)
- Adicionar 2 rotas
```

**Impacto:**
- ✅ Risco MÍNIMO (não toca em Denver)
- ✅ Esforço: 4-6 horas
- ✅ Aproveitaa estrutura polimórfica existente
- ✅ Consistente com arquitetura atual
- ✅ Fácil manutenção

---

## 🔢 Matriz de Decisão

| Critério | Polimorfismo Total | Sistema Paralelo | Prontuários Apenas |
|----------|-------------------|------------------|-------------------|
| **Impacto no código** | 🔴 ALTO (50+ arquivos) | 🟡 MÉDIO (20 arquivos) | 🟢 BAIXO (6 arquivos) |
| **Risco de quebra** | 🔴 ALTO | 🟢 BAIXO | 🟢 MÍNIMO |
| **Esforço (horas)** | 🔴 80-120h | 🟡 40-60h | 🟢 4-6h |
| **Consistência arquitetural** | 🟡 MÉDIA | 🔴 BAIXA (duplica) | 🟢 ALTA |
| **Manutenibilidade** | 🔴 COMPLEXA | 🟡 DUPLICADA | 🟢 SIMPLES |
| **Funcionalidade para adultos** | 🟢 COMPLETA | 🟢 COMPLETA | 🟡 BÁSICA |
| **Tempo até produção** | 🔴 3-4 semanas | 🟡 1-2 semanas | 🟢 1-2 dias |

**Vencedor:** ✅ Abordagem 3 (Prontuários Apenas)

---

## 💰 Análise Custo-Benefício

### Abordagem Recomendada: Prontuários Apenas

#### Custos
- ⏱️ 4-6 horas de desenvolvimento
- 🧪 1-2 horas de testes
- 📝 30 minutos de documentação
- **Total:** ~7-9 horas

#### Benefícios
- ✅ Profissionais podem criar prontuários para adultos
- ✅ Sistema de atribuição consistente (User igual a Kid)
- ✅ Zero risco de quebrar Denver
- ✅ Código limpo e manutenível
- ✅ Produção em 1-2 dias

#### ROI (Return on Investment)
- **Funcionalidade:** 80% das necessidades resolvidas
- **Custo:** 10% do esforço das outras abordagens
- **Risco:** Praticamente zero
- **Conclusão:** 🎯 EXCELENTE ROI

---

## 🚦 Roadmap de Implementação

### Fase 1: MVP - Prontuários para Adultos (PRIORIDADE)
**Prazo:** 1-2 dias
**Objetivo:** Profissionais criarem prontuários para adultos

1. ✅ Criar pivot `professional_user_patient`
2. ✅ Adicionar relationships
3. ✅ Atualizar controller/scope
4. ✅ Criar UI de atribuição
5. ✅ Testar end-to-end

### Fase 2: Validações e Avisos (RECOMENDADO)
**Prazo:** 4 horas
**Objetivo:** Orientar uso correto do Denver

1. ⚠️ Aviso na UI quando Kid >6 anos tenta usar Denver
2. ⚠️ Badge visual "Fora da faixa Denver" em checklists
3. 📊 Filtro opcional: mostrar apenas Kids <6 anos no checklist
4. 📝 Documentação interna sobre limitações

### Fase 3: Melhorias Futuras (OPCIONAL)
**Prazo:** A definir
**Objetivo:** Sistema de avaliação para adultos

1. 🔬 Pesquisar escalas de avaliação para adultos
2. 🏗️ Desenhar arquitetura de `AdultAssessment`
3. 💻 Implementar sistema paralelo
4. 🧪 Testar com profissionais

---

## 📈 Métricas de Sucesso

### Fase 1 (MVP)
| Métrica | Meta | Como Medir |
|---------|------|------------|
| Profissionais veem adultos no dropdown | 100% | Teste manual |
| Prontuários criados para adultos | >0 | Query no BD |
| Erros ao criar prontuário adulto | 0 | Logs |
| Tempo médio de atribuição | <2 min | Observação |

### Fase 2 (Validações)
| Métrica | Meta | Como Medir |
|---------|------|------------|
| Avisos exibidos corretamente | 100% | Teste manual |
| Profissionais entendem limitação Denver | >80% | Survey |
| Kids >6 anos sem Denver desnecessário | Trend ↓ | Analytics |

---

## ⚠️ Riscos e Contingências

### Risco 1: Confusão entre User Profissional e User Paciente

**Problema:**
- User pode ser profissional (`user_professional` pivot)
- User pode ser paciente (`professional_user_patient` pivot)
- Mesma pessoa pode ser ambos (ex: psicólogo em terapia)

**Mitigação:**
```php
// User.php - Comentário claro
public function professional() {
    // This user AS a professional (has CRP, atends patients)
}

public function assignedProfessionals() {
    // This user AS a patient (is attended by professionals)
}
```

**Contingência:**
- Documentação robusta
- Testes unitários para ambos os casos
- Validação na UI (não permitir auto-atribuição)

---

### Risco 2: Migration Falhar em Produção

**Problema:**
- Produção pode ter dados inconsistentes
- Foreign keys podem falhar

**Mitigação:**
```php
// Migration com tratamento de erros
public function up() {
    try {
        Schema::create('professional_user_patient', ...);
    } catch (\Exception $e) {
        // Log error
        // Rollback
        throw $e;
    }
}
```

**Contingência:**
- Backup do BD antes de rodar
- Testar migration em staging PRIMEIRO
- Ter script de rollback pronto

---

### Risco 3: Profissional Ver Paciente Não Atribuído

**Problema:**
- Bug no scope `forAuthProfessional()`
- Vazamento de dados sensíveis

**Mitigação:**
```php
// Testes unitários rigorosos
public function test_professional_only_sees_assigned_patients() {
    $prof = Professional::factory()->create();
    $assignedUser = User::factory()->create();
    $notAssignedUser = User::factory()->create();

    $prof->patients()->attach($assignedUser);

    $this->actingAs($prof->user->first());
    $patients = MedicalRecord::forAuthProfessional()->get();

    $this->assertTrue($patients->contains('patient_id', $assignedUser->id));
    $this->assertFalse($patients->contains('patient_id', $notAssignedUser->id));
}
```

**Contingência:**
- Code review obrigatório
- Testes de segurança manuais
- Audit logs ativados

---

## 📚 Documentação Complementar

### Arquivos a Consultar

1. **`adulto.md`** - Análise anterior focada no problema
2. **`CLAUDE.md`** - Padrões do projeto
3. **`docs/PROFESSIONAL_USER_RELATIONSHIP.md`** - Relacionamentos
4. **`medical-records.md`** - Sistema de prontuários

### Arquivos a Criar/Atualizar

1. **`analise_adulto.md`** - Este documento
2. **`docs/TIPOS_DE_PACIENTES.md`** - Novo documento sobre tipos
3. **`docs/DENVER_LIMITES.md`** - Limitações do Denver

---

## 🎓 Aprendizados e Boas Práticas

### 1. Polimorfismo é Poderoso
O sistema MedicalRecord provou que polimorfismo é a melhor escolha para múltiplos tipos de entidade. Evita duplicação de código.

### 2. Nem Tudo Precisa Ser Polimórfico
Checklists sendo exclusivos para Kids é OK. Forçar polimorfismo onde não faz sentido cria complexidade desnecessária.

### 3. Many-to-Many é Flexível
Tabelas pivot (`kid_professional`, `professional_user_patient`) permitem relacionamentos complexos sem rigidez de 1:1.

### 4. Menor Impacto ≠ Menos Funcionalidade
A solução mais simples (prontuários apenas) resolve 80% do problema com 10% do esforço.

### 5. Validação na UI, Não no BD
Permitir Kids >6 anos no BD mas avisar na UI é melhor que bloquear. Dá flexibilidade sem rigidez.

---

## 🔮 Visão de Longo Prazo

### Ano 1: Consolidação
- ✅ Sistema de prontuários funcionando para todos
- ✅ Profissionais trabalhando normalmente
- ✅ Denver apenas 0-6 anos (consciente)

### Ano 2: Expansão (Se Necessário)
- 🔬 Avaliar necessidade de sistema de avaliação para >6 anos
- 📊 Coletar dados sobre tipos de atendimento
- 🎯 Decidir se vale a pena sistema paralelo

### Ano 3: Especialização
- 🏥 Possível integração com outras escalas (WISC, WAIS, etc.)
- 🤖 IA para sugerir competências baseado em prontuários
- 📱 App mobile para pais acompanharem evolução

**Conclusão:** Começar simples, evoluir com necessidade real (não antecipada).

---

## ✅ Checklist de Implementação

### Antes de Começar
- [ ] Backup do banco de dados de produção
- [ ] Criar branch `feature/adult-patients`
- [ ] Rodar todos os testes existentes (baseline)

### Desenvolvimento
- [ ] Criar migration `professional_user_patient`
- [ ] Adicionar relationship `Professional->patients()`
- [ ] Adicionar relationship `User->assignedProfessionals()`
- [ ] Atualizar `MedicalRecordsController::getUserPatientsForUser()`
- [ ] Atualizar `MedicalRecord::scopeForAuthProfessional()`
- [ ] Criar view `assign-patients.blade.php`
- [ ] Adicionar métodos `assignPatients()` e `syncPatients()`
- [ ] Adicionar rotas
- [ ] Adicionar links na UI

### Testes
- [ ] Teste: Admin atribui adulto a profissional
- [ ] Teste: Profissional vê adulto no dropdown
- [ ] Teste: Profissional cria prontuário para adulto
- [ ] Teste: Profissional NÃO vê adultos não atribuídos
- [ ] Teste: Kids continuam funcionando normalmente
- [ ] Teste: Denver funciona para Kids <6 anos

### Documentação
- [ ] Atualizar `medical-records.md` (remover TODOs)
- [ ] Criar `docs/TIPOS_DE_PACIENTES.md`
- [ ] Atualizar `CLAUDE.md`
- [ ] Commit com mensagem descritiva

### Deploy
- [ ] Code review
- [ ] Merge para `main`
- [ ] Rodar migration em staging
- [ ] Testes em staging
- [ ] Rodar migration em produção
- [ ] Monitorar logs por 24h

---

## 🎯 Conclusão Final

### Problema Original
Sistema pensado apenas para crianças 0-6 anos precisa atender crianças maiores e adultos.

### Solução Escolhida
**Abordagem 3: Prontuários Apenas (Menor Impacto)**

### Por Quê?
1. ✅ Resolve 80% do problema com 10% do esforço
2. ✅ Zero risco de quebrar funcionalidade existente
3. ✅ Aproveita estrutura polimórfica já implementada
4. ✅ Consistente com arquitetura atual
5. ✅ Produção em 1-2 dias

### Próximos Passos
1. **Implementar Fase 1 (MVP)** - 4-6 horas
2. **Testar rigorosamente** - 2 horas
3. **Documentar** - 30 minutos
4. **Deploy em produção**
5. **Monitorar uso** - 1 semana
6. **Avaliar Fase 2** - Conforme necessidade

### Expectativa
Profissionais conseguirão criar e gerenciar prontuários para pacientes adultos de forma simples e intuitiva, mantendo a robustez do sistema Denver para crianças pequenas.

---

**Status:** ✅ Análise Completa - Pronto para Implementação
**Próxima Ação:** Aprovação do plano → Desenvolvimento
**Responsável:** Equipe de Desenvolvimento
**Prazo Estimado:** 1-2 dias úteis

---

**Documento elaborado por:** Claude Code
**Data:** 27/12/2025
**Versão:** 1.0
