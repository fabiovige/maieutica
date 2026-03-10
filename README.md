# Maieutica - Plataforma de Avaliacao Cognitiva para Clinicas Psicologicas

## Descricao

Maieutica e um sistema web completo para clinicas psicologicas e terapias associadas, com foco em avaliacao cognitiva de criancas, acompanhamento de progresso, gestao de profissionais, responsaveis e geracao de relatorios detalhados.

**Versao:** 1.0.18
**Producao:** maieuticavaliacom.br

## Stack

- **Backend:** Laravel 9.x (PHP ^8.0.2)
- **Frontend:** Vue 3.5 (Options API) + Bootstrap 5.3 + Chart.js 3.9
- **Banco de Dados:** MySQL/MariaDB
- **Build:** Laravel Mix 6.x (Webpack)
- **Auth:** Spatie Laravel Permission ^6.9 (permission-based)

## Funcionalidades Principais

- **Avaliacao Cognitiva (Denver):** Checklists com competencias avaliadas de 0-3, graficos radar, analise por nivel/dominio, clonagem para acompanhamento longitudinal
- **Prontuarios Medicos:** Registros polimorficos (criancas e adultos) com versionamento
- **Geracao de Documentos:** 6 modelos de documentos, HTML armazenado, PDF sob demanda (DomPDF)
- **Planos de Desenvolvimento:** Geracao automatica baseada em checklists
- **Gestao de Profissionais:** Cadastro, vinculacao a pacientes, ativacao/desativacao, e-mail com senha provisoria
- **Gestao de Usuarios e Permissoes:** Sistema baseado em permissoes (93 permissoes, 10 policies)
- **Dashboard:** Metricas, graficos interativos, resumos de progresso
- **Layout Sidebar:** Menu lateral responsivo (260px, colapsavel para 70px, drawer mobile)

## Instalacao

```bash
# 1. Clonar e instalar dependencias
composer install && npm install

# 2. Configurar ambiente
cp .env.example .env
php artisan key:generate

# 3. Banco de dados
php artisan migrate --seed

# 4. Compilar assets
npm run dev

# 5. Iniciar servidor
php artisan serve
```

## Comandos Uteis

```bash
npm run watch              # Watch e recompilacao de assets
npm run hot                # Hot reload
composer clear             # Limpar caches (route, view, config)
composer fresh             # migrate:fresh --seed (CUIDADO: apaga dados)
php artisan test           # Rodar testes
./vendor/bin/pint          # Formatador Laravel Pint
```

## Estrutura do Projeto

```
app/
  Controllers/         # 15 web + 8 API controllers
  Models/              # 23 models
  Policies/            # 10 policies
  Observers/           # 6 observers
  Mail/                # UserCreatedMail, UserUpdatedMail, UserDeletedMail
  Services/            # ChecklistService, OverviewService
resources/
  views/
    layouts/           # app.blade.php (sidebar layout)
    emails/            # Templates de e-mail (layout limpo institucional)
    components/        # Blade components reutilizaveis
  js/components/       # 9 Vue components
  js/composables/      # 9 composables
  sass/                # SCSS (config, variables, buttons, custom)
public/css/            # app.css (compilado), custom.css, typography.css
docs/                  # 20 arquivos de documentacao
tests/                 # 20 testes (Feature + Unit)
```

## Design System

- **Fonte:** Nunito (Google Fonts), base 16px (1rem)
- **Cor primaria:** Rosa `#AD6E9B`
- **Botoes:** Sistema padronizado em `_buttons.scss` (paleta clinica/institucional)
- **E-mails:** Templates limpos com header rosa, corpo neutro, sem emojis
- **PDF:** DejaVu Sans (requisito DomPDF)

## Documentacao

Documentacao detalhada em `docs/` e `CLAUDE.md`.

## Licenca

MIT
