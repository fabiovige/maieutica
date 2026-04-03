# Frontend — Maiêutica

## Vue 3 + Blade

**Fluxo de dados:** Blade renderiza estrutura → Vue monta → Axios busca dados da API  
**DataTables:** Plugin jQuery para paginação server-side  
**Webpack alias:** `@` → `resources/js/`

---

## Vue Components (9) — `resources/js/components/`

| Component | Propósito |
|-----------|-----------|
| `Competences.vue` | **Maior** — Interface de avaliação (notas 0-3) |
| `Planes.vue` | Gestão de planos de desenvolvimento |
| `Charts.vue` | Gráficos radar (Chart.js) |
| `Checklists.vue` | Listar/gerenciar checklists |
| `Dashboard.vue` | Visão do dashboard |
| `Resume.vue` | Resumo do kid |
| `Resumekid.vue` | Resumo específico do kid |
| `TableDescriptions.vue` | Tabelas de descrições |
| `Initials.vue` | Avatar com iniciais |

---

## Composables (9) — `resources/js/composables/`

- `charts.js` — Lógica de gráficos
- `checklists.js` — Operações de checklist
- `checklistregisters.js` — Registro de competências
- `competences.js` — Busca e filtro de competências
- `domains.js` — Domínios cognitivos
- `kids.js` — Dados de kids
- `levels.js` — Níveis de avaliação
- `planes.js` — Planos de desenvolvimento
- `expiringStorage.js` — LocalStorage com TTL

---

## Layout System

> Documentação completa: `docs/novo-layout-sidebar.md`

**Sidebar v2.0** (implementado em 2026-02-08) — Layout moderno com sidebar vertical:

```
┌─────────────────────────────────────────────────────────────┐
│  [LOGO]          Breadcrumb > Item          [Perfil] [Sair] │  ← Header
├──────────┬──────────────────────────────────────────────────┤
│  MENU    │              CONTEÚDO FLUIDO                     │
│  LATERAL │              (container-fluid)                    │
└──────────┴──────────────────────────────────────────────────┘
```

**Arquivos:**
- `resources/views/layouts/app.blade.php` — Layout principal (estilos sidebar **inline** no `<style>`)
- `resources/views/layouts/app-backup.blade.php` — Backup do layout anterior (navbar horizontal)
- `resources/sass/_sidebar-layout.scss` — Referência SCSS (criado mas **não importado**)

**Para usar em views:**
```blade
@extends('layouts.app')

@section('title', 'Título da Página')

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="#">Pai</a></li>
    <li class="breadcrumb-item active">Atual</li>
@endsection

@section('header-actions')
    <a href="#" class="btn btn-primary btn-sm">Novo</a>
@endsection

@section('content')
    {{-- container-fluid automático --}}
@endsection
```

---

## CSS Architecture

**Arquivos CSS carregados no HTML:**
```html
<link href="{{ asset('css/app.css') }}" rel="stylesheet">      <!-- SCSS compilado -->
<link href="{{ asset('css/custom.css') }}" rel="stylesheet">   <!-- CSS direto -->
<link href="{{ asset('css/typography.css') }}" rel="stylesheet"> <!-- CSS direto -->
```

**Ordem de load SCSS** (`resources/sass/app.scss`):
```scss
@use './config';        // $primary, $font-size-base
@use './variables';     // Bootstrap variable overrides
@use './_custom';       // Estilos customizados
@use 'bootstrap/scss/bootstrap';
@use './buttons';       // Sistema de botões (após Bootstrap)
```

**Arquivos SCSS:**

| Arquivo | Função |
|---------|--------|
| `_config.scss` | Variáveis base (`$primary: #AD6E9B`, `$font-size-base: 1rem`) |
| `_variables.scss` | Overrides do Bootstrap |
| `_custom.scss` | Estilos customizados |
| `_buttons.scss` | Sistema de botões padronizado (608 linhas) |
| `_sidebar-layout.scss` | Estilos do sidebar (referência, **não importado**) |
| `_bootstrap-overrides.scss` | Overrides adicionais do Bootstrap |
| `app.scss` | Entry point + tipografia + estilos da aplicação |

**Importante:**
- `custom.css` e `typography.css` são **standalone** (não compilados) — mudanças são imediatas
- `app.scss` deve ser compilado com `npm run dev` → gera `public/css/app.css`
- Cor primária rosa `#AD6E9B` unificada em SCSS (`_config.scss`) e CSS (`custom.css`)
- Estilos do sidebar estão **inline** no `app.blade.php`, não no SCSS compilado

---

## Tipografia

> Documentação completa: `docs/tipografia.md`

**Fonte:** Nunito (Google Fonts) — Visual clean e profissional  
**Base:** 16px (1rem) — unificado em todos os arquivos  
**PDF:** DejaVu Sans (requisito DomPDF) — classes em `pdf-base.blade.php`

**Tokens CSS** (`public/css/custom.css`): `--fs-base`, `--fs-xs`, `--fs-sm`, `--fw-*`, `--lh-*`

---

## Design System — Botões

Sistema padronizado em `resources/sass/_buttons.scss` (608 linhas) com paleta clínica/institucional.

**Classes principais:**

| Classe | Uso | Cor |
|--------|-----|-----|
| `btn-primary` | Ação principal, salvar | Azul médico (#2563eb) |
| `btn-secondary` | Cancelar, voltar, limpar | Cinza azulado (#64748b) |
| `btn-success` | Confirmar, ativar, download | Verde saúde (#059669) |
| `btn-danger` | Excluir, desativar, alerta | Vermelho (#dc2626) |
| `btn-warning` | Editar, modificar, cautela | Laranja (#d97706) |
| `btn-info` | Visualizar, informação | Ciano (#0891b2) |

**Variantes Outline:** `btn-outline-primary`, `btn-outline-secondary`, etc.

**Tamanhos:**
- `btn-sm` — 13px (tabelas, ações compactas)
- Padrão — 14px (formulários)
- `btn-lg` — 15px (CTAs importantes)

**Botões Contextuais:**
- `btn-action-primary` — CTA principal com sombra
- `btn-cancel` — Cancelar/voltar
- `btn-save` — Salvar
- `btn-delete` — Excluir (outline)
- `btn-edit` — Editar (outline)
- `btn-view` — Visualizar (outline)
- `btn-download` — Download
- `btn-restore` — Restaurar da lixeira
- `btn-filter` — Filtrar

**Estados especiais:** `btn-loading` (spinner), `btn-has-badge`, `btn-icon` (quadrado), `btn-block` (full width)

**Padrão obrigatório em tabelas (ícone + texto):**
```blade
<a href="..." class="btn btn-primary btn-sm"><i class="bi bi-eye"></i> Ver</a>
<a href="..." class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i> Editar</a>
<button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Excluir</button>

<!-- Formulário -->
<button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Salvar</button>
<a href="{{ route('index') }}" class="btn btn-secondary">Cancelar</a>
```

**Guia completo:** `resources/views/examples/buttons-guide.blade.php`

---

## Select2

Padrão para dropdowns com muitos itens. Aplicar gradualmente em formulários.  
Pacote: `vue3-select2-component` ^0.1.7
