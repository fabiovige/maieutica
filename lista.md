# Release Notes - Maieutica v1.0.18

## Entrega: Novo Layout, Tipografia e Melhorias (Março/2026)

---

### Layout e Interface

- **Novo layout com sidebar vertical** - Substituicao da navbar horizontal por menu lateral fixo (260px, colapsavel para 70px, drawer em mobile)
- **Submenus dropdown no sidebar** - Denver, Prontuarios, Documentos e Cadastros agora possuem submenus colapsaveis com animacao, seguindo o mesmo padrao da Lixeira
- **Visualizacao padrao em tabela** - Lista de criancas agora abre em tabela por padrao (era cards)

### Tipografia e Design System

- **Fonte base unificada em 16px (1rem)** - Padronizacao global: SCSS, CSS vars, typography.css, app.blade.php
- **Fonte Nunito** (Google Fonts) com pesos 300-800
- **Escala tipografica completa** - Tamanhos proporcionais para h1-h6, tabelas, forms, botoes, badges
- **CSS vars padronizadas** - `--fs-base`, `--fs-xs`, `--fs-sm` em custom.css
- **Sistema de botoes padronizado** - `_buttons.scss` (608 linhas) com paleta clinica/institucional
- **Cor primaria rosa #AD6E9B unificada** em SCSS e CSS

### E-mails

- **Templates de e-mail redesenhados** - Visual limpo e profissional (sem emojis, sem cores excessivas)
- **Layout padrao institucional** - Header rosa, corpo neutro cinza, footer clean
- **Templates atualizados:**
  - Boas-vindas (`user_created.blade.php`) - Dados em tabela, senha em `<code>`
  - Conta atualizada (`user_updated.blade.php`) - Dados em tabela, aviso de seguranca
  - Conta desativada (`user_deleted.blade.php`) - Dados em tabela, aviso de suporte

### Correcoes de Bugs

- **Login com senha provisoria** - Corrigido bug que impedia profissionais de fazer primeiro acesso com a senha enviada por e-mail
  - `ProfessionalController`: alterado `User::create()` para `new User()` + `save()` para que o Observer receba a senha temporaria
  - `UserCreatedMail`: adicionado `password` ao array `with()` do `content()`
- **Menu acoes em checklists** - Corrigido `@can('edit checklists')` para `@canany(['checklist-edit', 'checklist-edit-all'])` seguindo o padrao de permissoes `{entity}-{action}[-all]`

### Novas Funcionalidades

- **Ordenacao por progresso na lista de criancas** - Filtro "Ordenar por" com opcoes: Nome (A-Z/Z-A), Progresso (maior/menor primeiro), Data (recente/antigo)
- **Colunas ordenáveis na tabela** - Nome e Progresso clicáveis para ordenacao rapida com indicador visual (icone de seta)
- **Ordenacao inteligente por progresso** - Busca todos os registros, calcula progresso via OverviewService, ordena e pagina corretamente

### Documentacao

- **CLAUDE.md atualizado** - Layout sidebar, tipografia, sistema de botoes, templates de e-mail, arquitetura CSS
- **README.md reescrito** - Stack atual, funcionalidades, design system, comandos uteis
- **docs/tipografia.md** - Auditoria completa + plano de padronizacao tipografica
- **docs/novo-layout-sidebar.md** - Documentacao do layout sidebar
- **docs/dicionario-dados.md** - Dicionario de dados completo (31 tabelas)

---

### Commits desta entrega

| Hash | Descricao |
|------|-----------|
| `5e04f48` | Ajuste de fonte novo layout |
| `a577988` | Refatorando layout |
| `3344dcf` | Fix: corrigir envio de senha provisoria no cadastro de profissional |
| `337dd9a` | Style: redesign templates de e-mail com visual limpo e profissional |
| `437fe52` | Docs: atualizar CLAUDE.md e README.md + build de producao |
| `d58604b` | Feat: adicionar dropdowns no sidebar |
| `dd34837` | Fix: corrigir permissao do menu acoes em checklists |
| `f2994a8` | Feat: adicionar ordenacao por progresso na lista de criancas |

---

### Arquivos modificados (principais)

**Backend:**
- `app/Http/Controllers/ProfessionalController.php` - Fix senha provisoria
- `app/Http/Controllers/KidsController.php` - Ordenacao por progresso
- `app/Mail/UserCreatedMail.php` - Fix password no template

**Frontend:**
- `resources/views/layouts/app.blade.php` - Novo sidebar + submenus dropdown + tipografia
- `resources/views/kids/index.blade.php` - Tabela padrao + ordenacao
- `resources/views/checklists/index.blade.php` - Fix permissoes acoes
- `resources/views/emails/layout.blade.php` - Template base clean
- `resources/views/emails/user_created.blade.php` - Redesign
- `resources/views/emails/user_updated.blade.php` - Redesign
- `resources/views/emails/user_deleted.blade.php` - Redesign

**CSS/SCSS:**
- `resources/sass/_config.scss` - Font base 1rem
- `resources/sass/app.scss` - Tipografia atualizada
- `resources/sass/_buttons.scss` - Sistema de botoes
- `public/css/custom.css` - CSS vars atualizadas
- `public/css/typography.css` - Tamanhos atualizados

**Documentacao:**
- `CLAUDE.md` - Atualizado com todas as mudancas
- `README.md` - Reescrito
- `docs/tipografia.md` - Novo
- `docs/novo-layout-sidebar.md` - Novo
- `docs/dicionario-dados.md` - Novo
