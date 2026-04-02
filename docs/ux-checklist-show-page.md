# Plano UX: Checklists — Cards Mobile-First + Página "Ver"

**Data:** 2026-04-02  
**Objetivos:**
1. Substituir a tabela da lista de checklists por **cards mobile-first** (um card por checklist)
2. Reduzir as ações da lista a um único botão "Ver", transferindo tudo para `checklists/show.blade.php`

> Este será o **padrão visual futuro** do sistema — começamos pelos checklists e replicamos para as demais listagens aos poucos.

---

## Situação Atual

### Lista (`/checklists`) — Coluna Ações atual:
| Botão | Permissão | Condição extra |
|-------|-----------|----------------|
| Editar | `checklist-edit` | sempre |
| Avaliação | `checklist-avaliation` | checklist aberto ou admin |
| Plano Manual | `checklist-plane-manual` | checklist aberto ou admin |
| Plano Auto | `checklist-plane-automatic` | checklist aberto ou admin |
| Clonar | `checklist-clone` | checklist aberto ou admin |

### Tela Show (`/checklists/{id}`) — Estado atual:
- Vue component `<Checklists>` (exibe competências)
- Cards de status (Não observado / Em desenvolvimento / Não desenvolvido / Desenvolvido)
- Include `information-register` (legado, sem botões de ação úteis)
- **Sem nenhum botão de ação**

---

## O Que Fazer

### Fase 0 — Converter lista de checklists de tabela para cards (mobile-first)

**Motivação:** Tabelas são difíceis de ler em mobile e visualmente pesadas. Cards permitem
layout flexível, hierarquia visual clara e touch-friendly.

**Layout do card por checklist (col-12 — horizontal full-width):**
```
┌──────────────────────────────────────────────────────────────────────────┐
│  [ABERTO]  João da Silva  │  Nível 2  │  4 anos 2m  │  15/01/2026  [Ver]│
└──────────────────────────────────────────────────────────────────────────┘
┌──────────────────────────────────────────────────────────────────────────┐
│ [FECHADO]  Maria Santos   │  Nível 1  │  3 anos 5m  │  10/12/2025  [Ver]│
└──────────────────────────────────────────────────────────────────────────┘
```

**Regras de layout:**
- Cada card ocupa **12 colunas** (full-width), layout interno **horizontal** (flexbox row)
- Info distribuída da esquerda para direita, botão "Ver" fixo à direita
- Cantos arredondados (`border-radius`), sombra suave, fundo branco
- Badge de status colorido (verde=aberto, cinza=fechado) à esquerda
- Responsivo: em mobile os campos se reorganizam em 2 linhas mas o card mantém full-width
- Separação visual entre cards com `gap` (sem bordas entre eles, apenas sombra)

**Dados exibidos no card:**
| Campo | Fonte |
|-------|-------|
| Nome da criança | `$checklist->kid->name` |
| Nível | `$checklist->level` (badge) |
| Status | `$checklist->situation` → Aberto / Finalizado (badge colorido) |
| Data de nascimento + idade | `$checklist->kid->birth_date` / `FullNameMonths` |
| Data de criação | `$checklist->created_at->format('d/m/Y')` |
| Criado por | `$checklist->creator->name` (se disponível) |

**Filtros:** manter o formulário de filtros acima dos cards (já existente)

**Paginação:** manter `$checklists->links()` abaixo dos cards

**Arquivo:** `resources/views/checklists/index.blade.php` — substituir `<table>` por grid de cards

---

### Fase 1 — Enriquecer `checklists/show.blade.php`

Adicionar na tela de detalhe:

1. **Header com info do checklist:**
   - Nome da criança + link para `kids.show`
   - Nível do checklist (badge)
   - Status: Aberto / Finalizado (badge colorido)

2. **Barra de ações** (logo abaixo do header, antes do conteúdo):
   ```
   [ Editar ]  [ Avaliação ]  [ Plano Manual ]  [ Plano Auto ]  [ Clonar ]  [ Excluir ]
   ```
   - Mesmas permissões e condições da lista atual
   - `Editar` sempre aparece se tiver permissão
   - `Avaliação`, `Plano Manual`, `Plano Auto`, `Clonar` só aparecem se checklist aberto ou admin
   - `Excluir` com confirmação SweetAlert2

3. **Botão "Voltar"** → `checklists.index` (com `kidId` se vier de kid)

4. **Manter conteúdo existente:** cards de status + Vue component

### Fase 2 — Simplificar `checklists/index.blade.php`

Substituir toda a coluna "Ações" por um único botão:
```blade
<a href="{{ route('checklists.show', $checklist->id) }}" class="btn btn-secondary btn-sm">
    <i class="bi bi-eye"></i> Ver
</a>
```

Remover as colunas/condicionais de cada botão individualmente.

### Fase 3 — Ajustar `ChecklistController@show`

- Passar variável `$kid` para a view (já disponível via `$checklist->kid`)
- Passar `$isOpen` e `$isAdmin` como no index (para controle de botões)
- Garantir que `kidId` é preservado no breadcrumb/botão Voltar

---

## Arquivos a Modificar

| Arquivo | Mudança |
|---------|---------|
| `resources/views/checklists/index.blade.php` | Substituir `<table>` por grid de cards mobile-first + botão "Ver" único |
| `resources/views/checklists/show.blade.php` | Adicionar header, barra de ações, botão Voltar |
| `app/Http/Controllers/ChecklistController.php` | `show()`: passar `$isOpen`, `$isAdmin`, `$kidId` |

---

## Layout Proposto da Tela Show

```
┌─────────────────────────────────────────────────────────────┐
│ [← Voltar]                                                   │
│                                                              │
│ João da Silva  •  Nível 2  •  [ABERTO]                      │
│                                                              │
│ [Editar] [Avaliação] [Plano Manual] [Plano Auto] [Clonar]   │
│                                    (somente se aberto/admin) │
├─────────────────────────────────────────────────────────────┤
│  Cards de status (Não obs. / Em dev. / Não dev. / Desenv.)  │
├─────────────────────────────────────────────────────────────┤
│  Tabela de competências (Vue component)                      │
└─────────────────────────────────────────────────────────────┘
```

---

## Notas

- O `show` já existe e tem `$this->authorize('view', $checklist)` — sem alteração de policy
- O botão Excluir na show page deve usar SweetAlert2 (padrão do sistema)
- O `kidId` via query string deve ser preservado no breadcrumb e no botão Voltar
- Não remover o Vue component `<Checklists>` — ele exibe os dados de avaliação

---

## Checklist de Execução

- [ ] Fase 0: Converter index.blade.php — tabela → grid de cards mobile-first
- [ ] Fase 1: Enriquecer show.blade.php (header + ações + botão Voltar)
- [ ] Fase 2: Simplificar index.blade.php — coluna ações → único botão "Ver"
- [ ] Fase 3: Ajustar ChecklistController@show (variáveis extras)
- [ ] Teste mobile: lista de cards em 1 coluna, touch-friendly
- [ ] Teste com usuário admin (vê todos os botões na show)
- [ ] Teste com profissional (vê Editar sempre; outros só se aberto)
- [ ] Teste com checklist fechado (vê só Editar na show)

---

## Próximas Listagens a Converter (backlog)

Após validar o padrão com checklists, replicar para:
1. `kids/index.blade.php` — lista de crianças
2. `medical-records/index.blade.php` — prontuários
3. `professionals/index.blade.php` — profissionais
4. `users/index.blade.php` — usuários

Critério para cada conversão: tabela difícil em mobile → card com info relevante + botão "Ver".
