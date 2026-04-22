---
description: Geração de tags e releases do Maiêutica no GitHub (gh CLI, tag anotada, release notes agrupadas)
---

Use esta skill sempre que o usuário pedir para criar uma tag, publicar release, fechar uma versão ou perguntar "quais commits entraram desde a última release".

## Convenção de versionamento

SemVer com prefixo `v`: `vMAJOR.MINOR.PATCH`.

- **MAJOR** — quebras de contrato com produção (migration destrutiva, remoção de rota pública, rework de schema)
- **MINOR** — features novas retrocompatíveis (nova tela, nova permission, nova coluna additive)
- **PATCH** — apenas bugfixes retrocompatíveis

Ao decidir o número, rode `git tag --sort=-version:refname | head -5` e incremente a partir da maior tag. O projeto tem tags legadas sem prefixo (`2.1.0`, `2.2.0`) — ignore-as, use sempre `v` para novas.

**Cuidado com gaps:** se a última tag for `v2.7.0` e o usuário pedir `v2.9.0`, pergunte se `v2.8.0` foi pulada intencionalmente antes de criar.

## Pré-requisitos obrigatórios

Antes de qualquer `git tag`:

1. Branch `develop` com tudo que deve entrar na release já commitado e pushed
2. `git status` limpo
3. `git fetch` e branch sincronizada com origin
4. `gh auth status` retornando logado — se não, pedir ao usuário para rodar `! gh auth login`
5. Testes passando: `docker compose exec app php artisan test` (pular apenas se houver decisão explícita do usuário)

## Fluxo canônico

### 1. Mapear commits desde a última release

```bash
LAST_TAG=$(git tag --sort=-version:refname | grep -E '^v[0-9]' | head -1)
git log --oneline "$LAST_TAG"..HEAD
```

Filtre mentalmente os merges auto-gerados (`Merge pull request #N...`) — eles não entram nas notas. Foque nos commits `feat:`, `fix:`, `refactor:`, `hotfix:`, `chore:`.

### 2. Compor as notas em 3 seções

Agrupe por categoria usando emoji-free markdown:

```markdown
## Destaques
- <features principais — o que o usuário final verá>

## Correções
- <bugfixes relevantes>

## Refatorações
- <mudanças internas que impactam arquitetura/manutenção>
```

Evite: commits triviais ("ajuste de texto", "lint"), merges, WIPs. Use português direto, sem adjetivos vazios ("melhorias diversas"). Cada item começa com verbo no infinitivo ou substantivo curto.

### 3. Criar tag anotada com as notas embutidas

A mensagem da tag é o que o `gh release create --notes-from-tag` vai usar. **Sempre anotada** (`-a`), nunca leve.

```bash
git tag -a vX.Y.Z -m "$(cat <<'EOF'
Release vX.Y.Z

## Destaques
- ...

## Correções
- ...

## Refatorações
- ...
EOF
)"
```

### 4. Push da tag

```bash
git push origin vX.Y.Z
```

### 5. Criar a Release formal no GitHub

```bash
gh release create vX.Y.Z \
    --title "vX.Y.Z - <resumo curto do destaque principal>" \
    --verify-tag \
    --notes-from-tag
```

Flags:
- `--verify-tag` — aborta se a tag não existir no remote (protege contra digitação errada)
- `--notes-from-tag` — reusa a mensagem da tag como corpo da release (evita duplicação)
- `--title` — título curto e legível, complementa a tag

Se quiser marcar como pre-release: adicionar `--prerelease`. Se for draft: `--draft`.

### 6. Verificar

```bash
gh release view vX.Y.Z
```

Ou abrir `https://github.com/fabiovige/maieutica/releases/tag/vX.Y.Z` em nova aba.

## Quando NÃO criar release

- Mudanças apenas em `.claude/`, `docs/` ou `CLAUDE.md` (não afeta produção)
- Experimentos em branch feature ainda não mergeados na `develop`
- Hotfix urgente que vai direto pra produção via `main` sem passar pela cadência normal de release (nesse caso, criar a tag depois do hotfix estabilizar)

## ReleaseSeeder (mirror interno)

O projeto tem `database/seeders/ReleaseSeeder.php` que mantém um changelog visível dentro da aplicação (`/releases`). **Não é automático** — se a release for importante para o usuário ver no app, acrescentar uma entrada `Release::updateOrCreate(['version' => 'vX.Y.Z'], [...])` com o mesmo conteúdo das notas. Não bloquear a tag por causa disso; pode ser commit separado depois.

## Comandos canônicos (cola rápida)

```bash
# Ver última tag semver
git tag --sort=-version:refname | grep -E '^v[0-9]' | head -1

# Commits desde a última release
git log --oneline $(git tag --sort=-version:refname | grep -E '^v[0-9]' | head -1)..HEAD

# Criar tag anotada + push + release (fluxo full)
git tag -a vX.Y.Z -m "..."
git push origin vX.Y.Z
gh release create vX.Y.Z --title "..." --verify-tag --notes-from-tag

# Listar releases publicadas
gh release list --limit 10

# Apagar tag errada (CUIDADO, confirmar com usuário)
git tag -d vX.Y.Z && git push origin :refs/tags/vX.Y.Z
gh release delete vX.Y.Z --yes
```

## Ações hard-to-reverse

- **Force-push de tag** (`git push --force origin vX.Y.Z`) — só fazer se a release não foi anunciada/deploy ainda, e sempre confirmar com o usuário
- **Deletar release publicada** — idem; links externos podem ficar quebrados
- **Rename de tag** — não existe; precisa deletar e recriar

Nessas situações, sempre avisar o usuário antes de executar.

Para deploy em produção após a release, veja `/deploy`. Para padrões de commit, veja o histórico via `git log --oneline -20`.
