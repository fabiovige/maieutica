Leia `docs/tipografia.md` na íntegra. Use-o para responder perguntas sobre o sistema tipográfico.

## Fundamentos

- **Fonte:** Nunito (Google Fonts, weights 300-800)
- **Base:** 16px = 1rem (unificado em TODOS os arquivos)
- **Cor primária:** Rosa `#AD6E9B`
- **SCSS:** `$font-size-base: 1rem` em `resources/sass/_config.scss`

## Tokens CSS

Definidos em `public/css/custom.css` (variáveis `--fs-*`, `--fw-*`, `--lh-*`).
Tipografia adicional em `public/css/typography.css` (standalone, não compilado).

## Ordem de Carregamento

1. SCSS: `_config.scss` → `_variables.scss` → `_custom.scss` → Bootstrap → `_buttons.scss`
2. HTML: `app.css` → `custom.css` → `typography.css`

## Regras

- Alterar `custom.css` ou `typography.css` = mudança imediata (não compilados)
- Alterar arquivos SCSS = rodar `npm run dev` depois
- Login (`auth/login.blade.php`) não carrega `app.css`/`custom.css` — é standalone
- PDF usa `DejaVu Sans` (requisito DomPDF), classes em `pdf-base.blade.php`

Para sidebar e layout, consulte `/sidebar`.
