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
- **Tabelas:** DataTables server-side via `yajra/laravel-datatables` para listagens grandes; **tabelas simples** para listagens pequenas/paginadas server-side (ver abaixo)
- **Confirmações:** SweetAlert2 (via `vue-sweetalert2`)
- **Flash messages:** `laracasts/flash` (toasts)
- **Selects:** Select2 como padrão para dropdowns (aplicar gradualmente)
- **Formulários:** Validação via VeeValidate + máscaras via `jquery-mask-plugin`

## Padrão de Listagem Simples (Tabela + Filtro)

Para listagens paginadas server-side (sem DataTables), usar o padrão estabelecido em `resources/views/kids/index.blade.php` e `medical-records/index.blade.php`:

**Card de Filtro** — sem shadow, com borda sutil:
```blade
<div class="card mb-3" style="border-radius:12px; border:1px solid #e9ecef;">
    <div class="card-body">
        <form method="GET" action="{{ route('...') }}" class="row g-3">
            {{-- campos do filtro --}}
            <div class="col-auto d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Buscar
                </button>
                @if(request()->hasAny([...]))
                    <a href="{{ route('...') }}" class="btn btn-secondary" title="Limpar filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>
```

**Tabela** — classes exatas:
```blade
<table class="table table-hover table-bordered align-middle mb-0">
    <thead class="table-light">
        <tr>
            <th style="width:110px;">Data</th>
            <th>Nome</th>
            <th style="width:80px;" class="text-center">Ações</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td><small class="text-muted">{{ ... }}</small></td>
                <td>{{ $item->name }}</td>
                <td class="text-center">
                    <a href="..." class="btn btn-secondary btn-sm">
                        <i class="bi bi-eye"></i>
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="d-flex justify-content-center mt-3">
    {{ $items->onEachSide(1)->appends(request()->query())->links() }}
</div>
```

**Estado vazio** — `alert alert-light` (sem shadow, sem borda destacada):
```blade
<div class="alert alert-light mt-3 mb-0">
    <i class="bi bi-info-circle"></i> Nenhum registro encontrado.
</div>
```

**Regras:**
- Células secundárias (datas, metadados, profissional) → `<small class="text-muted">`
- Ações → `btn btn-secondary btn-sm` só com ícone (texto opcional)
- Paginação sempre centralizada: `d-flex justify-content-center mt-3`
- Badges de categoria (ex: Criança/Adulto) dentro da célula `Tipo`
- **NÃO usar** `shadow-sm` em cards de filtro nem listar em cards soltos (usar tabela)

## Componentes Vue (9)

Localizados em `resources/js/components/`. Registrados globalmente em `app.js`.

## Compilação

```bash
npm run dev    # Uma vez
npm run watch  # Contínuo (recompila ao salvar)
```

Após mudar SCSS: `npm run dev`. Após mudar `custom.css` ou `typography.css`: nada (direto).

Para tipografia, consulte `/tipografia`. Para sidebar, consulte `/sidebar`.
