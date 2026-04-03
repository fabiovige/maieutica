# Spec-Driven Development (SDD) — Maiêutica

## O que é SDD

Spec-Driven Development é uma abordagem onde **especificações são a fonte de verdade**, não o código. O agente de IA lê specs estruturadas antes de escrever qualquer linha, garantindo alinhamento com intenção, arquitetura e restrições do projeto.

No Maiêutica, isso se traduz em:

- **CLAUDE.md** = Constituição (regras inegociáveis, comandos, autorização)
- **docs/*.md** = Specs de referência (arquitetura, banco, frontend, features)
- **docs/PRD.md** = Requisitos do produto
- **Código** = Resultado das specs — nunca a fonte

---

## Estrutura de Documentos

```
CLAUDE.md                    ← Constituição (constitution.md no SDD)
docs/
├── SDD.md                   ← Este arquivo: metodologia
├── architecture.md          ← Modelos, controllers, observers, jobs
├── frontend.md              ← Vue, CSS, botões
├── packages.md              ← Dependências backend/frontend
├── testing.md               ← Testes e debugging
├── dicionario-dados.md      ← Schema completo (31 tabelas)
├── tipografia.md            ← Sistema tipográfico
├── novo-layout-sidebar.md   ← Layout sidebar v2.0
├── medical-records.md       ← Prontuários (polimórfico + versionamento)
├── documentos.md            ← Geração de documentos
├── PROFESSIONAL_USER_RELATIONSHIP.md  ← Relacionamentos pro/usuário
├── PRD.md                   ← Product Requirements Document
└── MANUAL_ATUALIZACAO_PRODUCAO.md    ← Deploy em produção
```

---

## Fluxo SDD para Novas Features

### 1. Pesquisa (antes de qualquer código)

Antes de implementar, o agente deve:
- Ler `docs/architecture.md` para entender modelos e controllers relacionados
- Ler `docs/dicionario-dados.md` para entender o schema
- Ler `CLAUDE.md` para confirmar restrições (auth, produção, padrões)
- Ler a doc específica do domínio (ex: `medical-records.md` para prontuários)

### 2. Especificação

Para features não triviais, criar uma spec em `docs/` antes de implementar:

```markdown
# Spec: [Nome da Feature]

## Intenção
O que o usuário quer fazer (em linguagem de negócio).

## Capacidades
- [ ] Capacidade 1 (com critério de aceite)
- [ ] Capacidade 2

## Restrições
- Deve respeitar permissão `x-create`
- Deve usar migration para mudanças no banco
- Deve funcionar para Kids e Users (polimórfico se aplicável)

## Decisões Técnicas
- Abordagem escolhida e por quê
- Alternativas descartadas

## Tasks
1. Migration (se necessário)
2. Model / relacionamentos
3. Controller + routes
4. Views Blade
5. Testes
```

### 3. Aprovação

Antes de implementar: revisar a spec com o usuário. Confirmar:
- Intenção está correta?
- Restrições foram capturadas?
- Tasks estão completas?

### 4. Implementação

Seguir as tasks da spec em ordem. Após cada task:
- Validar manualmente no browser
- Confirmar que nada quebrou

### 5. Verificação

Após implementar:
- Rodar `php artisan test` para confirmar que testes existentes passam
- Verificar permissões usam `can()` (nunca `hasRole()`)
- Confirmar que migrações estão corretas
- Atualizar `CLAUDE.md` se houver mudanças na arquitetura

---

## Regras para o Agente de IA

### Antes de escrever código

1. **Ler primeiro, escrever depois.** Nunca modificar um arquivo sem lê-lo antes.
2. **Perguntar antes de assumir.** Se a intenção for ambígua, perguntar ao usuário.
3. **Verificar docs relevantes.** Sempre consultar a doc do domínio afetado.

### Durante a implementação

4. **Seguir o padrão de autorização.** `can()` no PHP, `@can()` no Blade. Jamais `hasRole()`.
5. **Migrations para banco.** Nunca `ALTER TABLE` direto.
6. **Botões com ícone + label.** Padrão do design system (ver `docs/frontend.md`).
7. **Sem features extras.** Implementar exatamente o especificado — sem melhorias não solicitadas.

### Qualidade

8. **Sem abstrações prematuras.** Três linhas similares é melhor que uma abstração especulativa.
9. **Sem tratamento de erros para casos impossíveis.** Validar apenas nas fronteiras do sistema.
10. **Sem comentários óbvios.** Comentar apenas lógica não evidente.

---

## Quando Criar uma Nova Spec

| Situação | Ação |
|----------|------|
| Bug simples (1-2 arquivos) | Implementar direto, sem spec |
| Feature nova (modelo + controller + views) | Criar spec em `docs/` |
| Mudança arquitetural | Criar spec + atualizar `CLAUDE.md` |
| Mudança de schema (nova tabela/coluna) | Criar spec com migration detalhada |
| Feature com impacto em segurança/auth | Criar spec + revisar com usuário |

---

## Anatomia de um Arquivo de Spec no Maiêutica

Arquivos de spec seguem o padrão dos docs existentes no projeto:

```markdown
# [Título da Feature/Componente]

## Visão Geral
Descrição em 2-3 linhas da responsabilidade.

## Modelo de Dados
Tabelas e colunas relevantes (ou link para dicionario-dados.md).

## Fluxo Principal
Passo a passo do fluxo feliz.

## Regras de Negócio
- Regra 1
- Regra 2

## Permissões
- `entidade-create` para criar
- `entidade-edit` para editar
- `entidade-list-all` para admin ver tudo

## Observações
Limitações conhecidas, casos edge, débito técnico.
```

---

## Referências

- Artigo SDD Parte 1-3: origem da metodologia
- [GitHub Spec Kit](https://githubnext.com/projects/spec-kit) — toolkit de referência
- [Tessl](https://tessl.io) — framework + registry de specs
- `CLAUDE.md` — constituição deste projeto
