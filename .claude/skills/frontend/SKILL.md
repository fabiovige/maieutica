---
description: Vue 3 Options API, CSS architecture, componentes, compilação, Bootstrap 5.3
---

Leia `docs/frontend.md` na íntegra. Use-o para responder perguntas sobre o frontend.

## Stack e Padrões

- **Vue 3.5 Options API** — NÃO usar Composition API
- **Bootstrap 5.3** — framework CSS principal
- **Bootstrap Icons** — biblioteca de ícones (`bi bi-*`)
- **Laravel Mix 6.x** — build com Webpack
- **jQuery** — necessário para DataTables e plugins legados

**Fluxo de renderização:** Blade renderiza estrutura HTML → Vue monta componentes → Axios busca dados da API

## Arquitetura CSS (Ordem de Carregamento)

1. `app.css` — compilado pelo Mix (SCSS: `_config.scss` → `_variables.scss` → `_custom.scss` → bootstrap → `_buttons.scss`)
2. `custom.css` — tokens CSS (`--fs-*`, `--fw-*`, `--lh-*`), carregado direto (mudanças imediatas)
3. `typography.css` — tipografia standalone, carregado direto

**Exceções:**
- Sidebar: estilos INLINE em `app.blade.php` (não compilados)
- Login: `auth/login.blade.php` standalone — não carrega `app.css`/`custom.css`

## Padrões de UI

- **Botões:** Sistema em `_buttons.scss` (608 linhas) — paleta clínica/institucional, padrão ícone + texto
- **Tabelas:** DataTables server-side via `yajra/laravel-datatables`
- **Confirmações:** SweetAlert2 (via `vue-sweetalert2`)
- **Flash messages:** `laracasts/flash` (toasts)
- **Selects:** Select2 como padrão para dropdowns (aplicar gradualmente)
- **Formulários:** Validação via VeeValidate + máscaras via `jquery-mask-plugin`

## Componentes Vue (9)

Localizados em `resources/js/components/`. Registrados globalmente em `app.js`.

## Compilação

```bash
npm run dev    # Uma vez
npm run watch  # Contínuo (recompila ao salvar)
```

Após mudar SCSS: `npm run dev`. Após mudar `custom.css` ou `typography.css`: nada (direto).

Para tipografia, consulte `/tipografia`. Para sidebar, consulte `/sidebar`.
