---
inclusion: fileMatch
fileMatchPattern: "resources/views/components/**/*"
---

# Component Patterns

## Tables

### Standard listing table
All listing pages use Bootstrap table classes. The base combination is:

```html
<table class="table table-bordered table-hover table-striped align-middle mb-0">
    <thead class="table-light">
        <tr>
            <th style="width: 60px;" class="text-center">ID</th>
            <th>NOME</th>
            <th class="text-center" style="width: 100px;">AÇÕES</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-center">{{ $item->id }}</td>
            <td>{{ $item->name }}</td>
            <td class="text-center">
                {{-- action buttons --}}
            </td>
        </tr>
    </tbody>
</table>
```

**Rules:**
- Always `table-bordered table-hover align-middle mb-0` as the base.
- Add `table-striped` on dense listing tables (users, professionals).
- `thead` always uses `table-light`.
- Column headers in UPPERCASE.
- Fixed-width columns use inline `style="width: Xpx;"` on `<th>`.
- Action column: `text-center`, fixed width (80–100px).
- Wrap in `<div class="table-responsive">` when inside a card.

### Pagination
Always placed after the table, aligned to the right (or centered when inside tabs):

```html
{{-- right-aligned (default) --}}
<div class="d-flex justify-content-end mt-3">
    {{ $items->onEachSide(1)->appends(request()->query())->links() }}
</div>

{{-- centered (inside tabs) --}}
<div class="d-flex justify-content-center mt-3">
    {{ $items->onEachSide(1)->appends(array_merge(request()->query(), ['tab' => 'tabname']))->links() }}
</div>
```

### Action buttons in rows
Single action (view only): use a direct `<a>` button.
Multiple actions: use the `components.table-actions` dropdown component.

```html
{{-- single action --}}
<a href="{{ route('entity.show', $item->id) }}" class="btn btn-secondary btn-sm">
    <i class="bi bi-eye"></i>
</a>

{{-- multiple actions --}}
@component('components.table-actions')
    @slot('items')
        <li><a class="dropdown-item" href="{{ route('entity.edit', $item->id) }}">Editar</a></li>
        <li><a class="dropdown-item" href="{{ route('entity.show', $item->id) }}">Visualizar</a></li>
    @endslot
@endcomponent
```

### Empty state
```html
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> Nenhum registro encontrado.
</div>
```

### Search/filter bar above a table
Always a card with `mb-3`, form with `row g-3`, search input in `col-md-10`, buttons in `col-md-2 d-flex align-items-end`:

```html
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('entity.index') }}" class="row g-3">
            <div class="col-md-10">
                <label for="search" class="form-label">
                    <i class="bi bi-search"></i> Buscar
                </label>
                <input type="text" class="form-control" id="search" name="search"
                       placeholder="Buscar por..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="d-flex gap-2 w-100">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    @if(request('search'))
                        <a href="{{ route('entity.index') }}" class="btn btn-secondary" title="Limpar filtro">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
```

When a search is active, show a result count alert below the filter bar:

```html
@if(request('search'))
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        Exibindo resultados para "<strong>{{ request('search') }}</strong>".
        <strong>{{ $items->total() }}</strong> registro(s) encontrado(s).
    </div>
@endif
```

---

## Cards

### Standard content card
Used to wrap tables, forms, and content sections:

```html
<div class="card">
    <div class="card-header">
        Título da Seção
    </div>
    <div class="card-body">
        {{-- content --}}
    </div>
</div>
```

### Detail/info card (e.g. patient header)
Used at the top of show pages to display entity summary. Uses `shadow-sm`:

```html
<div class="card shadow-sm">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">
                {{-- avatar or initials fallback --}}
            </div>
            <div class="col-md-5">
                <h4 class="mb-1">Nome da Entidade</h4>
                <p class="text-muted mb-2">...</p>
            </div>
            <div class="col-md-5">
                {{-- secondary info panel --}}
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    {{-- action buttons --}}
                </div>
            </div>
        </div>
    </div>
</div>
```

### Metric/summary card (dashboard and index summaries)
Small stat cards used in rows. Use `border-0 shadow-sm` with a colored background and a colored icon circle:

```html
<div class="card border-0 shadow-sm" style="background:#e8f0fe; border-radius:12px;">
    <div class="card-body p-3 d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center"
             style="width:42px; height:42px; background:#4285f4;">
            <i class="bi bi-person-hearts text-white"></i>
        </div>
        <div>
            <div class="small" style="color:#5f6368;">Rótulo</div>
            <div class="fs-4 fw-bold" style="color:#202124;">{{ $count }}</div>
        </div>
    </div>
</div>
```

### Nested info card (inside a detail card)
Used for secondary info blocks (e.g. list of professionals inside a patient card). Uses `bg-light`:

```html
<div class="card bg-light">
    <div class="card-body">
        <h3 class="card-title">
            <i class="bi bi-people"></i> Título
        </h3>
        {{-- list items --}}
    </div>
</div>
```

---

## Avatar / Initials Fallback

Whenever displaying a user or patient photo, always provide an initials fallback:

```html
@if ($entity->photo)
    <img src="{{ asset($entity->photo) }}"
         style="width:40px; height:40px; border-radius:50%; object-fit:cover;"
         alt="{{ $entity->name }}">
@else
    <div class="d-flex align-items-center justify-content-center"
         style="width:40px; height:40px; border-radius:50%; background:#e2e8f0; color:#94a3b8;">
        <i class="bi bi-person"></i>
    </div>
@endif
```

For larger avatars (120px, detail cards), use `font-size: 2.5em` and `bg-secondary text-white` on the fallback div.

---

## Badges

Status badges follow this convention:

| State | Classes |
|---|---|
| Active / success | `badge bg-success` |
| Warning / intern | `badge bg-warning text-dark` |
| Inactive / disabled | `badge bg-secondary` or `badge bg-danger` |
| Info / count | `badge bg-info` |
| Role / tag | `badge text-bg-info` |
| Adult patient | `badge` with inline `style="background:#7c3aed;"` |
| Child patient | `badge bg-primary` |
