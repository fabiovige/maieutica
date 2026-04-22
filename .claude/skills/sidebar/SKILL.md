---
description: Layout sidebar v2.0, estilos inline, menu com permissões
---

Leia `docs/novo-layout-sidebar.md` na íntegra. Use-o para responder perguntas sobre o layout sidebar v2.0.

## Estrutura

- Sidebar vertical: 260px expandida, 70px colapsada
- Layout em `resources/views/layouts/app.blade.php`
- **Estilos INLINE** no `<style>` do `app.blade.php` — NÃO estão no SCSS compilado
- `_sidebar-layout.scss` existe como referência mas NÃO é importado no `app.scss`

## Variáveis CSS (inline)

```css
--sidebar-width: 260px;
--sidebar-collapsed: 70px;
--sidebar-bg: #1e293b;
--sidebar-text: #94a3b8;
--sidebar-active: #3b82f6;
```

## Menu e Permissões

A sidebar usa `@can()` para mostrar/ocultar seções:
- Seção Avaliação Multidimensional: `@can('checklist-list')` e `@can('kid-list')`
- Seção Prontuários: `@can('medical-record-list')`
- Seção Documentos: `@can('document-list')`
- Seção Cadastros: `@can('professional-list')`, `@can('user-list')`, `@can('role-list')`
- Seção Administração: `@can('checklist-list-all')` ou `@can('kid-list-all')` ou `@can('user-list-all')`

## Regras

- Para adicionar item ao menu: seguir padrão `<div class="menu-item">` com `menu-link` e ícone Bootstrap Icons
- Submenu: usar classe `has-submenu` + `<ul class="submenu">`
- Badge de contagem: `<span class="menu-badge">{{ $count }}</span>`
- Responsivo: sidebar oculta em mobile, aberta via `menu-toggle`
- Estado de colapso salvo em `localStorage`
