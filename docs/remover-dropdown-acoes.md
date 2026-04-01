# Plano: Remover Dropdown "Ações" e Substituir por Botões Simples

**Data:** 2026-04-01  
**Status:** Pendente  
**Referência:** Lista de prontuários já convertida (commit f2b8052)

---

## Objetivo

Substituir todos os componentes `table-actions` (dropdown Bootstrap) por botões simples `btn-secondary btn-sm` em linha, eliminando problemas de usabilidade em mobile e simplificando a interface.

## Padrão Adotado

```blade
{{-- ANTES (dropdown) --}}
@component('components.table-actions')
    @slot('items')
        <li><a class="dropdown-item" href="...">Ver</a></li>
        <li><a class="dropdown-item" href="...">Editar</a></li>
    @endslot
@endcomponent

{{-- DEPOIS (botões simples) --}}
<div class="d-flex flex-wrap gap-1 justify-content-center">
    <a href="..." class="btn btn-secondary btn-sm">Ver</a>
    <a href="..." class="btn btn-secondary btn-sm">Editar</a>
</div>
```

Para ações destrutivas (excluir/desativar) dentro de `<form>`:
```blade
<form action="..." method="POST" class="d-inline"
      onsubmit="return confirm('Mensagem de confirmação');">
    @csrf @method('DELETE')
    <button type="submit" class="btn btn-secondary btn-sm">Excluir</button>
</form>
```

---

## Arquivos a Alterar (11 arquivos)

### PRIORIDADE 1 — Lixeiras (ação única: Restaurar)

São os casos mais simples: um único botão por linha.

| # | Arquivo | Ação atual | Observação |
|---|---------|-----------|------------|
| 1 | `resources/views/medical-records/trash.blade.php` | Restaurar | Usa `<form>` POST |
| 2 | `resources/views/professionals/trash.blade.php` | Restaurar | Usa SweetAlert2 |
| 3 | `resources/views/kids/trash.blade.php` | Restaurar | Usa SweetAlert2 |
| 4 | `resources/views/users/trash.blade.php` | Restaurar | Usa SweetAlert2 |
| 5 | `resources/views/checklists/trash.blade.php` | Restaurar | Usa SweetAlert2 |
| 6 | `resources/views/roles/trash.blade.php` | Restaurar | Usa SweetAlert2 |

> **Nota SweetAlert2:** As lixeiras usam um botão com classe `btn-restore` que dispara SweetAlert2 via JS. Manter esse comportamento: o botão simples pode ter a mesma classe `btn-restore` para o JS continuar funcionando.

---

### PRIORIDADE 2 — Listagens simples (1-2 ações)

| # | Arquivo | Ações | Observação |
|---|---------|-------|------------|
| 7 | `resources/views/roles/index.blade.php` | Editar | Ação única; HTML atual tem bug (`<li>` faltando) |
| 8 | `resources/views/users/index.blade.php` | Ver, Editar | Duas ações simples |

---

### PRIORIDADE 3 — Listagens complexas (3+ ações)

| # | Arquivo | Ações | Observação |
|---|---------|-------|------------|
| 9 | `resources/views/professionals/index.blade.php` | Editar, Pacientes, Desativar/Ativar | Ativar/Desativar são condicionais |
| 10 | `resources/views/kids/index.blade.php` | Ver, Editar, Checklists, Comparativo, Desenvolvimento | Só na view tabela; cards já têm botões |
| 11 | `resources/views/checklists/index.blade.php` | Editar, Avaliação, Plano Manual, Plano Automático, Clonar | Mais complexo: dropdown pode ser disabled condicionalmente |

---

## Considerações por Arquivo

### `checklists/index.blade.php`
O dropdown atual aceita um slot `disabled` — quando o checklist está fechado (`situacao != 'a'`), o botão inteiro fica desabilitado. Com botões simples, cada botão deve ser exibido/ocultado individualmente conforme a situação.

### `professionals/index.blade.php`
As ações **Ativar** e **Desativar** são mutuamente exclusivas (condicionais no blade). Com botões simples, isso funciona igual — o `@if` apenas mostra um ou outro botão.

### `kids/index.blade.php`
A view tem dois modos: **tabela** e **cards**. A view em cards já usa botões simples. Alterar apenas o bloco da tabela. Após a mudança, os dois modos ficam visualmente consistentes.

---

## Colunas a Remover/Ajustar

Ao converter para botões em linha, a coluna de ações pode precisar de mais espaço. Avaliar remoção de colunas menos essenciais em cada listagem:

| Listagem | Coluna candidata a remover |
|----------|---------------------------|
| `kids/index` (tabela) | — (já tem cards como alternativa) |
| `professionals/index` | "Registro" (já aparece no nome) |
| `checklists/index` | Nenhuma óbvia |
| `users/index` | "Tipo" (pouco usado) |

---

## O que NÃO muda

- O componente `resources/views/components/table-actions.blade.php` pode ser mantido para uso futuro ou deprecado após todas as conversões.
- As permissões `@can` em cada botão permanecem idênticas.
- Confirmações de ações destrutivas permanecem (SweetAlert2 ou `confirm()`).

---

## Checklist de Execução

- [ ] 1. `medical-records/trash.blade.php`
- [ ] 2. `professionals/trash.blade.php`
- [ ] 3. `kids/trash.blade.php`
- [ ] 4. `users/trash.blade.php`
- [ ] 5. `checklists/trash.blade.php`
- [ ] 6. `roles/trash.blade.php`
- [ ] 7. `roles/index.blade.php`
- [ ] 8. `users/index.blade.php`
- [ ] 9. `professionals/index.blade.php`
- [ ] 10. `kids/index.blade.php` (somente view tabela)
- [ ] 11. `checklists/index.blade.php`
