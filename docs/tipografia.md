# Tipografia - Guia de Padronização

## Sumário

- [1. Diagnóstico Atual](#1-diagnóstico-atual)
- [2. Problemas Encontrados](#2-problemas-encontrados)
- [3. Proposta de Padronização](#3-proposta-de-padronização)
- [4. Escala Tipográfica](#4-escala-tipográfica)
- [5. Hierarquia de Headings](#5-hierarquia-de-headings)
- [6. Pesos de Fonte](#6-pesos-de-fonte)
- [7. Cores de Texto](#7-cores-de-texto)
- [8. Classes Utilitárias Propostas](#8-classes-utilitárias-propostas)
- [9. Plano de Implementação](#9-plano-de-implementação)

---

## 1. Diagnóstico Atual

### 1.1 Fontes em Uso

| Local | Fonte | Arquivo |
|-------|-------|---------|
| Variáveis Bootstrap | `'Nunito', sans-serif` | `_variables.scss:6` |
| Config Bootstrap | `'Nunito', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif` | `_config.scss:9` |
| Seletor global `*` | `Arial, Helvetica, sans-serif` | `app.scss:53` |
| Google Fonts import | `Nunito:wght@400` (apenas peso 400) | `app.scss:15` |
| PDFs (DomPDF) | `DejaVu Sans, sans-serif` | `pdf-base.blade.php:9` |

### 1.2 Tamanhos de Fonte Encontrados

**Unidades REM (usadas no app principal):**
| Tamanho | Contexto | Local |
|---------|----------|-------|
| `0.8rem` | Texto pequeno, badges | Vue components, `app.css` |
| `0.9rem` | Base font-size (override) | `_variables.scss:7` |
| `1rem` | Base font-size (config) | `_config.scss:10` |
| `1.5rem` | Ícones médios | Blade templates |
| `2rem` | Ícones grandes | Documentos |
| `3rem` | Ícones extra grandes | Dashboard, kids |
| `4rem` | Ícones maiores | Profile, overview |

**Unidades PX (usadas na tela de login):**
| Tamanho | Contexto | Local |
|---------|----------|-------|
| `11px` | Rodapé PDF | `pdf-base.blade.php` |
| `12px` | Referência PDF, ícone avatar | Templates PDF |
| `14px` | "Lembrar-me", seções PDF | `app.scss:172` |
| `16px` | Labels login | `app.scss:142` |
| `17px` | Inputs login | `app.scss:135` |
| `20px` | Botão submit login | `app.scss:160` |
| `22px` | H3 login | `app.scss:203` |

**Unidades EM (usadas em Vue components):**
| Tamanho | Contexto | Local |
|---------|----------|-------|
| `1em` | Form check inputs | `_variables.scss` |
| `2em` | Avatares circulares | `kids/edit.blade.php` |
| `2.5em` | Avatares kid-info-card | `kid-info-card.blade.php` |

### 1.3 Pesos de Fonte em Uso

| Peso | Valor | Uso |
|------|-------|-----|
| Normal | 400 | Texto padrão, inputs login |
| Medium | 500 | Botões login, h3 login |
| Semibold | 600 | Card titles, h2 login, `.fw-semibold` |
| Bold | 700 | Títulos de página, métricas, `.fw-bold` |

### 1.4 Line Heights

| Valor | Contexto | Local |
|-------|----------|-------|
| `1.3` | Badges em Vue | `Checklists.vue` |
| `1.4` | Rodapé PDF | `pdf-base.blade.php` |
| `1.6` | Base global | `_variables.scss`, `_config.scss` |

### 1.5 Uso de Headings (h1-h6)

| Heading | Qtd Aprox | Uso Atual | Problema |
|---------|-----------|-----------|----------|
| **h1** | ~1 | Chart demo apenas | Não usado para títulos de página |
| **h2** | ~15 | Métricas do dashboard, headers de domínio | OK para seções principais |
| **h3** | ~30 | Títulos de gráficos, seções PDF | Uso variado |
| **h4** | ~10 | **Título principal da página** (`app.blade.php:34`) | Deveria ser h1 |
| **h5** | ~50+ | Card titles, **wrapper de badges** (não semântico) | Sobreuso |
| **h6** | ~20 | Labels do dashboard, seções prontuário | OK para rótulos menores |

### 1.6 Dois Sistemas de Cores Primárias

| Sistema | Cor | Hex | Arquivo |
|---------|-----|-----|---------|
| Bootstrap SCSS | Azul | `#4aa4ee` | `_config.scss:14` |
| Custom CSS vars | Rosa | `#AD6E9B` | `custom.css:5` |

---

## 2. Problemas Encontrados

### 2.1 CRÍTICO: Conflito de Font-Family

```scss
// _config.scss:9 — define Nunito como fonte principal
$font-family-sans-serif: 'Nunito', system-ui, ...;

// app.scss:53 — seletor * SOBRESCREVE tudo com Arial
* {
    font-family: Arial, Helvetica, sans-serif; // ← sobrescreve Nunito!
}
```

**Impacto:** A fonte Nunito é importada do Google Fonts mas NUNCA é aplicada no app principal porque o seletor `*` tem prioridade. O sistema inteiro renderiza em Arial.

### 2.2 CRÍTICO: Dois Base Font-Size

```scss
// _config.scss:10 — define 1rem (16px)
$font-size-base: 1rem !default;

// _variables.scss:7 — sobrescreve para 0.9rem (14.4px)
$font-size-base: 0.9rem !default;
```

**Impacto:** Confusão sobre qual valor é realmente aplicado. A ordem de `@use` em `app.scss` determina o resultado, mas é ambíguo.

### 2.3 CRÍTICO: Google Fonts importa apenas peso 400

```scss
// app.scss:15 — só carrega Regular (400)
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400&display=swap');
```

**Impacto:** Quando o sistema usa `fw-bold` (700), `fw-semibold` (600) ou qualquer peso diferente de 400, o navegador faz **faux bold** (engrossamento artificial), resultando em texto com aparência ruim e inconsistente.

### 2.4 ALTO: Unidades Misturadas (rem, px, em)

- App principal usa `rem`
- Login usa `px`
- Vue components usam `em` e `rem` misturados
- Sem escala tipográfica definida

### 2.5 ALTO: Hierarquia de Headings Incorreta

- `h1` praticamente não existe
- `h4` é usado como título principal de página
- `h5` é sobreusado (50+ instâncias), inclusive para conteúdo não-semântico (wrappers de badges)
- Pula-se níveis (h6 direto após h2)

### 2.6 MÉDIO: Tela de Login Isolada

A tela de login possui seu próprio sistema tipográfico completamente separado (px, pesos diferentes, cores hardcoded), sem reaproveitamento dos tokens do sistema.

### 2.7 MÉDIO: Duas Paletas de Cores Primárias

- SCSS Bootstrap: azul `#4aa4ee`
- CSS Custom: rosa `#AD6E9B`
- Ambas são usadas em contextos diferentes, causando inconsistência visual

---

## 3. Proposta de Padronização

### 3.1 Fonte Principal

```scss
// Uma única declaração, uma única fonte
$font-family-sans-serif: 'Nunito', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
```

**Google Fonts — carregar pesos necessários:**
```html
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700&display=swap');
```

| Peso | Uso | Classe Bootstrap |
|------|-----|------------------|
| 400 (Regular) | Corpo de texto, parágrafos | — |
| 500 (Medium) | Labels, breadcrumbs | `fw-medium` |
| 600 (Semibold) | Subtítulos, valores de dados | `fw-semibold` |
| 700 (Bold) | Títulos, ênfase forte | `fw-bold` |

### 3.2 Base Font-Size

```scss
$font-size-base: 0.875rem; // 14px — compacto e profissional para sistema clínico
$line-height-base: 1.5;    // Bootstrap padrão, boa legibilidade
```

**Justificativa:** 14px é o padrão de mercado para sistemas admin/clínicos (ex: Google Workspace, Salesforce). Compacto o suficiente para density de informações sem sacrificar legibilidade.

---

## 4. Escala Tipográfica

Escala baseada em **ratio 1.25 (Major Third)** a partir do base de 14px:

### 4.1 Escala de Tamanhos

| Token | Rem | Px (aprox) | Uso |
|-------|-----|------------|-----|
| `--fs-xs` | `0.75rem` | 12px | Captions, labels auxiliares, rodapés |
| `--fs-sm` | `0.8125rem` | 13px | Texto secundário, badges, meta info |
| `--fs-base` | `0.875rem` | 14px | Corpo de texto, inputs, botões |
| `--fs-md` | `1rem` | 16px | Texto de destaque, subtítulos de card |
| `--fs-lg` | `1.125rem` | 18px | Títulos de card, seções internas |
| `--fs-xl` | `1.25rem` | 20px | Títulos de seção |
| `--fs-2xl` | `1.5rem` | 24px | Títulos de página |
| `--fs-3xl` | `1.875rem` | 30px | Métricas/KPIs do dashboard |
| `--fs-4xl` | `2.25rem` | 36px | Display (uso raro) |

### 4.2 Variáveis CSS Propostas

```css
:root {
    /* Escala tipográfica */
    --fs-xs:   0.75rem;    /* 12px */
    --fs-sm:   0.8125rem;  /* 13px */
    --fs-base: 0.875rem;   /* 14px */
    --fs-md:   1rem;       /* 16px */
    --fs-lg:   1.125rem;   /* 18px */
    --fs-xl:   1.25rem;    /* 20px */
    --fs-2xl:  1.5rem;     /* 24px */
    --fs-3xl:  1.875rem;   /* 30px */
    --fs-4xl:  2.25rem;    /* 36px */

    /* Pesos */
    --fw-normal:   400;
    --fw-medium:   500;
    --fw-semibold: 600;
    --fw-bold:     700;

    /* Line heights */
    --lh-tight:  1.25;  /* headings */
    --lh-normal: 1.5;   /* corpo de texto */
    --lh-loose:  1.75;  /* texto longo, acessibilidade */
}
```

---

## 5. Hierarquia de Headings

### 5.1 Mapeamento Proposto

| Heading | Tamanho | Peso | Line-Height | Uso no Sistema |
|---------|---------|------|-------------|----------------|
| **h1** | `--fs-2xl` (1.5rem/24px) | 700 | 1.25 | Título principal da página (ex: "Crianças", "Profissionais") |
| **h2** | `--fs-xl` (1.25rem/20px) | 700 | 1.25 | Título de seção dentro da página |
| **h3** | `--fs-lg` (1.125rem/18px) | 600 | 1.3 | Título de card, painel |
| **h4** | `--fs-md` (1rem/16px) | 600 | 1.3 | Subtítulo de card, grupo de campos |
| **h5** | `--fs-base` (0.875rem/14px) | 600 | 1.4 | Rótulo de seção interna |
| **h6** | `--fs-sm` (0.8125rem/13px) | 600 | 1.4 | Caption, rótulo menor |

### 5.2 Aplicação por Contexto

```
┌─────────────────────────────────────────────────────┐
│ Layout (app.blade.php)                              │
│                                                     │
│  h1  "Crianças"  |  Breadcrumb                     │  ← Título da página
│                                                     │
│  ┌─────────────────────────────────────────────┐    │
│  │ Card                                        │    │
│  │  h3  "Dados Pessoais"                       │    │  ← Título do card
│  │                                              │    │
│  │  h4  "Informações de Contato"                │    │  ← Subtítulo
│  │                                              │    │
│  │  label  Nome: ________                       │    │  ← fs-base (14px)
│  │  label  Email: _______                       │    │
│  │  p.text-muted  "Texto auxiliar"              │    │  ← fs-sm (13px)
│  └─────────────────────────────────────────────┘    │
│                                                     │
│  ┌──────────────────┐  ┌──────────────────┐         │
│  │ Card Métrica      │  │ Card Métrica      │        │
│  │  h6  "Total"      │  │  h6  "Ativos"     │        │  ← Rótulo
│  │  span.fs-3xl "42" │  │  span.fs-3xl "38" │        │  ← KPI grande
│  └──────────────────┘  └──────────────────┘         │
└─────────────────────────────────────────────────────┘
```

### 5.3 Regras de Uso

1. **Cada página deve ter exatamente um `h1`** — o título principal
2. **Nunca pular níveis** — após h2, usar h3 (não h4 ou h5)
3. **Não usar headings para estilização** — se precisa do tamanho mas não da semântica, usar classes (`.fs-lg`, `.fw-bold`)
4. **h5 em Vue components** — substituir por `<span>` ou `<div>` quando usado apenas como wrapper de badges

---

## 6. Pesos de Fonte

### 6.1 Guia de Uso

| Peso | Classe | Quando Usar |
|------|--------|-------------|
| **400 Regular** | — | Corpo de texto, parágrafos, inputs, descrições |
| **500 Medium** | `.fw-medium` | Breadcrumbs, labels de formulário, links de navegação |
| **600 Semibold** | `.fw-semibold` | Títulos de card (h3-h6), dados numéricos, valores em tabela |
| **700 Bold** | `.fw-bold` | Títulos de página (h1-h2), métricas KPI, ações primárias |

### 6.2 Regras

- Nunca usar `font-weight` inline — sempre classes Bootstrap
- Não combinar bold + uppercase (escolha um)
- Máximo 2 pesos diferentes por componente/card

---

## 7. Cores de Texto

### 7.1 Paleta de Texto Proposta

| Token | Hex | Uso |
|-------|-----|-----|
| `--text-primary` | `#1a1a2e` | Títulos, texto principal, headings |
| `--text-secondary` | `#495057` | Texto secundário, descrições |
| `--text-muted` | `#6c757d` | Auxiliar, placeholders, timestamps |
| `--text-disabled` | `#adb5bd` | Conteúdo desabilitado |
| `--text-inverse` | `#ffffff` | Texto sobre fundos escuros |
| `--text-link` | `var(--color-primary)` | Links, ações clicáveis |
| `--text-danger` | `#dc3545` | Erros, campos obrigatórios |
| `--text-success` | `#198754` | Confirmações, status positivo |
| `--text-warning` | `#856404` | Alertas, atenção |

### 7.2 Mapeamento com Bootstrap

| Classe Bootstrap | Quando Usar |
|------------------|-------------|
| `.text-dark` | Títulos e texto principal |
| `.text-body` | Corpo de texto padrão |
| `.text-muted` | Informação secundária, timestamps |
| `.text-primary` | Links, ações, destaques da marca |
| `.text-danger` | Erros, indicadores obrigatórios (`*`) |
| `.text-success` | Status positivo, competência "Consistente" |
| `.text-white` | Sobre fundos coloridos/escuros |

### 7.3 Cores de Status (Avaliação)

Manter as cores atuais do sistema de notas, mas padronizar com variáveis:

```css
:root {
    --note-0-text: #856404;  /* Não observado - amarelo */
    --note-0-bg:   #fff3cd;
    --note-1-text: #004085;  /* Emergente - azul */
    --note-1-bg:   #cce5ff;
    --note-2-text: #721c24;  /* Inconsistente - vermelho */
    --note-2-bg:   #f8d7da;
    --note-3-text: #155724;  /* Consistente - verde */
    --note-3-bg:   #d4edda;
}
```

---

## 8. Classes Utilitárias Propostas

### 8.1 Tamanhos de Texto

```css
.fs-xs   { font-size: var(--fs-xs) !important; }    /* 12px - caption */
.fs-sm   { font-size: var(--fs-sm) !important; }    /* 13px - secundário */
.fs-base { font-size: var(--fs-base) !important; }  /* 14px - padrão */
.fs-md   { font-size: var(--fs-md) !important; }    /* 16px - destaque */
.fs-lg   { font-size: var(--fs-lg) !important; }    /* 18px - título card */
.fs-xl   { font-size: var(--fs-xl) !important; }    /* 20px - título seção */
.fs-2xl  { font-size: var(--fs-2xl) !important; }   /* 24px - título página */
.fs-3xl  { font-size: var(--fs-3xl) !important; }   /* 30px - KPI */
.fs-4xl  { font-size: var(--fs-4xl) !important; }   /* 36px - display */
```

### 8.2 Line Heights

```css
.lh-tight  { line-height: var(--lh-tight) !important; }   /* 1.25 */
.lh-normal { line-height: var(--lh-normal) !important; }  /* 1.5 */
.lh-loose  { line-height: var(--lh-loose) !important; }   /* 1.75 */
```

### 8.3 Composições Comuns

```css
/* Título de página */
.page-title {
    font-size: var(--fs-2xl);
    font-weight: var(--fw-bold);
    line-height: var(--lh-tight);
    color: var(--text-primary);
}

/* Título de card */
.card-title {
    font-size: var(--fs-lg);
    font-weight: var(--fw-semibold);
    line-height: var(--lh-tight);
}

/* Métrica/KPI */
.metric-value {
    font-size: var(--fs-3xl);
    font-weight: var(--fw-bold);
    line-height: 1;
}

/* Rótulo de métrica */
.metric-label {
    font-size: var(--fs-sm);
    font-weight: var(--fw-medium);
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

/* Texto de tabela */
.table-text {
    font-size: var(--fs-base);
    font-weight: var(--fw-normal);
}

/* Caption/Auxiliar */
.caption {
    font-size: var(--fs-xs);
    color: var(--text-muted);
}
```

---

## 9. Plano de Implementação

### Fase 1 — Correções Críticas (baixo risco, alto impacto)

**Prioridade:** Imediata

| # | Tarefa | Arquivo | Mudança |
|---|--------|---------|---------|
| 1.1 | Remover override global `*` | `app.scss:49-54` | Remover ou mover `font-family` do `*` para `.container-login` apenas |
| 1.2 | Carregar pesos corretos do Nunito | `app.scss:15` | Alterar import para `wght@400;500;600;700` |
| 1.3 | Unificar `$font-size-base` | `_variables.scss:7` e `_config.scss:10` | Definir `0.875rem` em um único local |
| 1.4 | Unificar `$font-family-sans-serif` | `_variables.scss:6` e `_config.scss:9` | Manter apenas em `_config.scss` |

**Estimativa:** Mudanças em 2-3 arquivos SCSS. Rebuild com `npm run dev`.

### Fase 2 — Variáveis e Escala (médio risco)

**Prioridade:** Curto prazo

| # | Tarefa | Arquivo | Mudança |
|---|--------|---------|---------|
| 2.1 | Adicionar CSS custom properties | `custom.css` ou novo `_typography.scss` | Adicionar variáveis `--fs-*`, `--fw-*`, `--lh-*` |
| 2.2 | Adicionar classes utilitárias | `custom.css` ou `_typography.scss` | Adicionar `.fs-xs` até `.fs-4xl` |
| 2.3 | Adicionar composições | `custom.css` ou `_typography.scss` | `.page-title`, `.card-title`, `.metric-value`, etc. |

### Fase 3 — Refatoração de Headings (médio risco)

**Prioridade:** Médio prazo — fazer página por página

| # | Tarefa | Arquivos | Mudança |
|---|--------|----------|---------|
| 3.1 | Título de página: h4 → h1 | `app.blade.php:34` | `<h4 class="mb-0 fw-bold">` → `<h1 class="page-title mb-0">` |
| 3.2 | Dashboard: corrigir hierarquia | `home.blade.php` | Revisar uso de h2/h6 |
| 3.3 | Cards: padronizar títulos | Todas as views com cards | Usar h3 para títulos de card consistentemente |
| 3.4 | Vue: remover h5 de badges | `Checklists.vue`, `Planes.vue` | Substituir `<h5>` por `<div>` ou `<span>` |

### Fase 4 — Login e Estilos Inline (baixo risco)

**Prioridade:** Médio prazo

| # | Tarefa | Arquivos | Mudança |
|---|--------|----------|---------|
| 4.1 | Login: converter px → rem | `app.scss` (seção login) | Converter todos os valores px para rem |
| 4.2 | Login: usar variáveis de cor | `app.scss` (seção login) | Substituir cores hardcoded por `var(--*)` |
| 4.3 | Remover inline styles de font-size | Blade templates diversos | Mover para classes utilitárias |

### Fase 5 — Limpeza e PDFs (baixo risco) ✅ CONCLUÍDA

**Prioridade:** Longo prazo

| # | Tarefa | Arquivos | Mudança | Status |
|---|--------|----------|---------|--------|
| 5.1 | Padronizar tipografia de PDFs | `pdf-base.blade.php` + modelos | Body 16px→14px, classes CSS (`pdf-section-title`, `pdf-text`, `pdf-note`, `pdf-reference`), inline styles removidos dos modelos 3-6 e pdf-template | ✅ |
| 5.2 | Resolver conflito azul vs rosa | `_config.scss`, `app.scss`, `login.blade.php`, `navbar.blade.php` | `$primary` azul→rosa (`#AD6E9B`), todas refs hardcoded `#4aa4ee`/`#3286ca` substituídas por `var(--color-primary*)`, navbar com classe `.navbar-custom` | ✅ |
| 5.3 | Auditar `text-muted` vs cores inline | Todas as views | Auditoria: `text-muted` já consistente (120+ usos corretos). Inline `color:#` eliminados. Única exceção: avatar font-sizes (contextual, aceitável) | ✅ |

---

## Resumo Visual: Antes vs Depois

```
ANTES (estado atual)                    DEPOIS (implementado)
─────────────────────                   ─────────────────────
Font: Arial (por causa do *)            Font: Nunito (corrigido) ✅
Base: 0.9rem OU 1rem (conflito)         Base: 0.875rem (14px, único) ✅
Pesos: só 400 carregado                 Pesos: 400, 500, 600, 700 ✅
Título página: h4                       Título página: h1 ✅
Heading mais usado: h5 (~50x)           Cada heading com propósito claro ✅
Unidades: rem + px + em misturados      Unidades: rem (padrão), px só PDF ✅
Cores primárias: azul E rosa            Cor primária: rosa unificada ✅
Escala: ad-hoc, sem padrão              Escala: 9 tokens definidos ✅
Login: sistema próprio isolado          Login: reutiliza tokens globais ✅
PDFs: inline styles repetitivos         PDFs: classes CSS centralizadas ✅
```
