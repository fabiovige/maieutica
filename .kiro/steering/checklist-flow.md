---
inclusion: manual
---

# Fluxo de Avaliação de Checklists

## Visão Geral

O checklist é o núcleo da avaliação cognitiva Denver. Cada checklist pertence a uma criança (`Kid`), cobre um conjunto de níveis (1–4) e registra notas por competência na tabela pivot `checklist_competence`.

---

## Estrutura de Dados

### Hierarquia de avaliação

```
Level (1–4)
  └── Domain (M:N via domain_level)  — ex: Motor, Linguagem, Social
        └── Competence               — item avaliável com code + description
              └── checklist_competence (pivot)
                    ├── checklist_id
                    ├── competence_id
                    └── note (0–3)
```

### Escala de notas (pivot `checklist_competence.note`)

| Valor | Sigla | Significado |
|-------|-------|-------------|
| 0 | X | Não observado |
| 1 | N | Não desenvolvido (difícil de obter) |
| 2 | P | Em desenvolvimento (parcial/com ajuda) |
| 3 | A | Desenvolvido (consistente) |

> **Atenção:** `Plane::NOTES_DESCRIPTION` usa mapeamento diferente (0=Não observado, 1=Não desenvolvido, 2=Em desenvolvimento, 3=Desenvolvido). O valor semântico de "Consistente/Desenvolvido" é sempre **note = 3**.

### Situação do checklist (`situation`)

| Valor | Label |
|-------|-------|
| `'a'` | Aberto — checklist ativo, editável |
| `'f'` | Fechado — somente leitura (exceto admin com `checklist-edit-all`) |

**Regra de negócio crítica:** ao criar um novo checklist para uma criança, todos os checklists anteriores com `situation = 'a'` são automaticamente fechados (`'f'`). Isso é feito no `booted()` do model `Checklist`. Só pode existir **um checklist aberto por criança** por vez.

---

## Fluxo Completo

### 1. Criação do checklist

**Rota:** `POST /checklists` → `ChecklistController@store`

- Usuário escolhe a criança e o nível máximo (padrão: 4).
- Dois modos disponíveis via modal na listagem:
  - **Checklist atual** — data = hoje, `situation = 'a'`, notas zeradas.
  - **Checklist retroativo** — data passada, `situation = 'f'`, notas clonadas do checklist ativo mais recente (se existir).
- Ao criar, o sistema:
  1. Fecha todos os checklists abertos da criança (via `booted()`).
  2. Cria o `Checklist`.
  3. Cria automaticamente um `Plane` vinculado ao checklist.
  4. Popula `checklist_competence` com todas as competências dos níveis 1..N com `note = 0` (ou notas clonadas se retroativo).

### 2. Listagem de checklists

**Rota:** `GET /checklists?kidId={id}` → `ChecklistController@index`

- Exibe todos os checklists da criança com barra de progresso (`developmentPercentage`).
- Filtro de visibilidade por perfil:
  - `checklist-list-all` → vê todos.
  - Profissional → vê apenas checklists de kids vinculados a ele.
  - Responsável → vê apenas checklists de seus filhos.
- Dois gráficos Chart.js no contexto de kid: evolução temporal (bar+line) e distribuição de status do último checklist.

### 3. Preenchimento da avaliação (interface principal)

**Rota:** `GET /checklists/{id}/fill` → `ChecklistController@fill`  
**View:** `checklists/fill.blade.php`  
**Componente Vue:** `Competences.vue`

A interface de avaliação é um componente Vue montado dentro do Blade. O fluxo é:

1. Vue carrega os domínios do nível selecionado via `GET /api/competences?level=&domain=&checklist_id=`.
2. O avaliador seleciona **nível** e **domínio** nos selects.
3. Para cada competência exibida, o avaliador clica em um radio button (N / P / A / X).
4. A cada mudança de radio, Vue chama `updateCompetenceNote()` → `POST /api/checklistregisters/single` com `{checklist_id, competence_id, note}`.
5. O backend usa `$checklist->competences()->syncWithoutDetaching([competence_id => ['note' => $note]])` para salvar na pivot.
6. A barra de progresso é atualizada via `GET /api/checklistregisters/progressbar/{checklist_id}/{totalLevel}`.

**Bloqueio de edição:** se `situation = 'f'` e o usuário não tem `checklist-edit-all`, todos os radios ficam `disabled`. O campo `is_admin` passado ao componente controla isso.

### 4. Preenchimento automático de níveis anteriores

Disponível quando `level_id > 1`. Botão "Preencher níveis anteriores" abre modal com:
- Seleção de quais níveis anteriores preencher.
- Seleção de uma nota única (N/P/A/X) para aplicar em massa.
- Processamento sequencial via `POST /api/checklistregisters/single` para cada competência.
- Exibe resumo agrupado por nível → domínio → competência em tempo real.

### 5. Cálculo de progresso (`developmentPercentage`)

Calculado por `ChecklistService::percentualDesenvolvimento()`:

```
Para cada domínio:
  - itemsTested = competências com note != 0
  - itemsValid  = competências com note == 3 (Consistente)

percentual = (totalItemsValid / totalItemsTested) * 100
```

- Retorna `0` se nenhuma competência foi avaliada.
- Usado na listagem de checklists e na listagem de kids.
- A barra de progresso na interface de preenchimento usa endpoint diferente (`progressbar`), que conta apenas competências com `note != 0` sobre o total de competências dos níveis cobertos.

### 6. Clonagem de checklist

**Rota:** `POST /checklists/{id}/clonar` → `ChecklistController@clonarChecklist`

- Cria novo checklist com `situation = 'a'` e mesmo nível do original.
- Copia todas as notas da pivot do checklist original para o novo.
- Cria automaticamente um novo `Plane` vinculado.
- Fecha o checklist original (via `booted()`).
- Usado para acompanhamento longitudinal — preserva o histórico e inicia nova avaliação a partir do estado atual.

### 7. Plano de desenvolvimento

Cada checklist tem um `Plane` criado automaticamente no momento da criação do checklist. O plano é uma seleção de competências que o profissional quer trabalhar com a criança.

- **Rota de visualização:** `GET /plane-automatic/{kidId}/{checklistId}` → `PlaneAutomaticController@index`
- **API de gestão:** `PlaneController` (API) — adicionar/remover competências do plano via `POST /api/planes/store` e `DELETE /api/planes/delete`.
- O componente Vue `Planes` gerencia a interface de seleção de competências para o plano.

### 8. Gráfico radar

**Rota:** `GET /checklists/{id}/chart` → `ChecklistController@chart`  
**Componente Vue:** `Charts.vue`

Exibe gráfico radar com as notas por domínio do checklist. Permite comparar múltiplos checklists da mesma criança.

---

## Permissões Relevantes

| Permissão | Descrição |
|-----------|-----------|
| `checklist-list` | Listar checklists próprios/vinculados |
| `checklist-list-all` | Listar todos os checklists |
| `checklist-create` | Criar checklist |
| `checklist-show` | Ver checklist próprio/vinculado |
| `checklist-show-all` | Ver qualquer checklist |
| `checklist-edit` | Editar checklist próprio/vinculado |
| `checklist-edit-all` | Editar qualquer checklist (ignora `situation = 'f'`) |
| `checklist-delete` | Mover para lixeira (próprio/vinculado) |
| `checklist-delete-all` | Mover qualquer checklist para lixeira |

---

## Rotas

```
GET    /checklists                        index (listagem)
GET    /checklists/create                 create (formulário)
POST   /checklists                        store
GET    /checklists/{id}                   show
GET    /checklists/{id}/edit              edit
PUT    /checklists/{id}                   update
DELETE /checklists/{id}                   destroy (soft delete)
GET    /checklists/trash                  trash
POST   /checklists/{id}/restore           restore
GET    /checklists/{id}/fill              fill (interface de avaliação)
GET    /checklists/{id}/chart             chart (gráfico radar)
POST   /checklists/{id}/clonar            clonarChecklist

# API (consumidas pelo Vue)
GET    /api/checklists/{id}               dados estruturados por nível/domínio
GET    /api/competences                   competências filtradas por checklist/level/domain
POST   /api/checklistregisters            salvar notas em lote
POST   /api/checklistregisters/single     salvar nota de uma competência
GET    /api/checklistregisters/progressbar/{id}/{totalLevel}  progresso
GET    /api/levels/{id}                   domínios de um nível (usado no autofill)
```

---

## Regras de Negócio Críticas

1. **Um checklist aberto por criança:** criar novo checklist fecha automaticamente todos os anteriores com `situation = 'a'`.
2. **Checklist fechado é somente leitura** para usuários sem `checklist-edit-all`.
3. **Checklist retroativo** nunca fecha outros checklists e nasce com `situation = 'f'`.
4. **Notas são salvas individualmente** via `syncWithoutDetaching` — nunca reescrever toda a pivot de uma vez ao salvar uma nota individual.
5. **Plane é criado automaticamente** junto com o checklist — nunca criar checklist sem criar o Plane correspondente.
6. **Clonagem preserva notas** — ao clonar, copiar todas as notas da pivot do checklist original.
7. **Progresso = note 3 / total avaliados** — apenas `note = 3` conta como "desenvolvido" no cálculo de `developmentPercentage`.
8. **Elegibilidade Denver:** apenas kids elegíveis aparecem no `create` — usar `Kid::getDenverEligibleKids()`.
9. **Exclusão em cascata:** ao deletar um checklist, os Planes associados também são soft-deleted. Ao restaurar, os Planes também são restaurados.
