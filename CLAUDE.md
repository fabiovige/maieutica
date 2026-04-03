# CLAUDE.md — Constituição do Maiêutica

Este arquivo é a **constituição** do projeto para o Claude Code. Contém apenas regras inegociáveis e comandos essenciais. Detalhes técnicos estão em `docs/`.

## Projeto

**Maiêutica** — Plataforma clínica de avaliação cognitiva infantil. Em produção em maieuticavaliacom.br.

**Versão:** 1.0.18

**Stack:**
- Backend: Laravel 9.x (PHP ^8.0.2)
- Frontend: Vue 3.5 (Options API) + Bootstrap 5.3 + Chart.js 3.9
- Database: MySQL/MariaDB
- Build: Laravel Mix 6.x (Webpack)
- Auth: Spatie Laravel Permission ^6.9 (baseada em permissões, NÃO em roles)

---

## Regras Críticas de Produção

**Este sistema está em produção.** Nunca fazer mudanças que possam quebrar funcionalidades existentes.

- Testar manualmente antes de commitar
- Sempre usar migrations para mudanças no banco (nunca `ALTER TABLE` direto)
- Refatorar incrementalmente e validar antes de mergear
- Nunca otimizar prematuramente — estabilidade em primeiro lugar
- **Nunca esvaziar o banco local de desenvolvimento** — usar `php artisan db:seed` para popular

---

## Comandos Essenciais

```bash
# Setup
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed && npm run dev

# Desenvolvimento
npm run watch          # Watch e recompilar assets
npm run hot            # Hot reload (mais rápido)
php artisan serve      # Servidor de desenvolvimento

# Cache
composer clear         # limpa: cache, route, view, config, clear-compiled

# Banco (desenvolvimento)
php artisan db:seed    # Popular banco (uso normal)
composer fresh         # migrate:fresh --seed (SOMENTE se usuário pedir explicitamente)

# Testes
php artisan test
./vendor/bin/pint      # Formatter PHP

# Logs
# Browser: /log-viewer | Terminal: tail -f storage/logs/laravel.log
```

---

## Sistema de Autorização

### CRÍTICO: Baseado em Permissões (NÃO em Roles)

**SEMPRE usar `can()` para autorização:**
```php
$user->can('user-edit')              // Controller/Service
@can('user-edit') ... @endcan        // Blade
$this->authorize('update', $user);   // Delegação para Policy
```

**NUNCA usar `hasRole()` para autorização:**
```php
// ERRADO — quebra a arquitetura!
if ($user->hasRole('admin')) { }
@role('admin') ... @endrole
```

**Roles** = Contêineres para agrupar permissões (apenas para atribuição: `$user->assignRole('profissional')`)  
**Permissions** = Verificações reais de autorização no código

### Padrão de Nomes: `{entidade}-{ação}[-all]`

| Padrão | Significado |
|--------|-------------|
| `user-list` | Pode listar próprios/atribuídos |
| `user-list-all` | Pode listar TODOS (admin) |
| `user-edit` | Pode editar próprios/atribuídos |
| `user-edit-all` | Pode editar TODOS (admin) |

**Ações:** `list`, `show`, `create`, `edit`, `delete`, `restore` (usa `edit`)

### Policies (10 total)

Todas seguem o mesmo padrão:
```php
public function viewAny(User $user): bool {
    return $user->can('user-list') || $user->can('user-list-all');
}
public function update(User $user, User $model): bool {
    return $user->can('user-edit') || $user->can('user-edit-all');
}
// viewTrash/restore usam permissão 'edit'
// forceDelete usa apenas permissão '-all'
```

**Policies disponíveis:** `ChecklistPolicy`, `KidPolicy`, `MedicalRecordPolicy`, `GeneratedDocumentPolicy`, `PlanePolicy`, `ProfessionalPolicy`, `UserPolicy`, `RolePolicy`, `ResponsiblePolicy`, `CompetencePolicy`

Ver `docs/PROFESSIONAL_USER_RELATIONSHIP.md` para padrões de autorização detalhados.

---

## Notas Importantes

- **Localização BR:** `laravellegends/pt-br-validator` para CPF, datas, telefones
- **Windows dev:** Desenvolvido no MINGW64, paths podem diferir
- **Estilos do sidebar:** Estão **inline** no `app.blade.php` (`<style>`), não no SCSS compilado
- **Fonte base:** 16px (1rem) unificado em todos os arquivos (SCSS, CSS, inline)
- **Login standalone:** `auth/login.blade.php` não carrega `app.css`/`custom.css`
- **PDF:** Templates estendem `documents.layouts.pdf-base`, fonte `DejaVu Sans`
- **Limitação conhecida:** Profissionais não podem criar prontuários para pacientes adultos via UI. Workaround: Admin cria. Requer completar pivot `professional_user_patient`.

---

## Índice de Documentação

| Arquivo | Conteúdo |
|---------|----------|
| `docs/SDD.md` | Metodologia Spec-Driven Development para este projeto |
| `docs/architecture.md` | Modelos, controllers, observers, jobs, middleware, estatísticas |
| `docs/frontend.md` | Vue components, CSS architecture, design system de botões |
| `docs/packages.md` | Todos os pacotes (backend, frontend, dev) |
| `docs/testing.md` | Estrutura de testes e debugging |
| `docs/dicionario-dados.md` | Schema completo (31 tabelas) |
| `docs/tipografia.md` | Sistema tipográfico completo |
| `docs/novo-layout-sidebar.md` | Layout sidebar v2.0 |
| `docs/medical-records.md` | Prontuários (polimórfico + versionamento) |
| `docs/documentos.md` | Geração de documentos |
| `docs/PROFESSIONAL_USER_RELATIONSHIP.md` | Relacionamentos profissional/usuário |
| `docs/PRD.md` | Product Requirements Document |
| `docs/MANUAL_ATUALIZACAO_PRODUCAO.md` | Deploy em produção |
| `docs/analise_adulto.md` | Pacientes adultos (parcialmente implementado) |

---

## Mudanças Recentes

- **2026-03-10:** Redesign dos templates de e-mail (layout limpo, cor rosa institucional)
- **2026-03-10:** Fix: senha provisória no cadastro de profissional
- **2026-03-10:** Aumento global de fonte: base 14px → 16px (1rem)
- **2026-02-09:** Padronização de botões em tabelas (ícone + label)
- **2026-02-08:** Novo layout com sidebar vertical
- **2026-02-08:** Padronização tipográfica completa (5 fases)
- **2026-02-08:** Sistema de botões padronizado (`_buttons.scss`)
- **2026-01-27:** Campo `is_intern` na tabela `professionals`
- **2025-12-28:** Tabela pivot `professional_user_patient`
- **2025-12-22:** Prontuários com suporte polimórfico e versionamento
- **2025-12-06:** Documentos gerados com suporte polimórfico
