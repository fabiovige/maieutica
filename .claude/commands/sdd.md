Leia `docs/SDD.md` na íntegra. Use-o para entender e aplicar a metodologia Spec-Driven Development.

## Quando Usar

SDD deve ser aplicado para features que envolvam:
- Novas entidades/tabelas no banco
- Mudanças em regras de autorização
- Fluxos que afetam múltiplos controllers/models
- Qualquer alteração que possa impactar funcionalidades existentes

## Processo

1. **Spec antes do código** — documentar decisões de design antes de implementar
2. **Validação** — revisar spec com o usuário antes de começar
3. **Implementação** — seguir a spec, registrar desvios
4. **Documentação** — atualizar docs ao final

## Regra de Ouro

Para mudanças simples e isoladas (bug fix, ajuste de UI), não é necessário SDD. Use bom senso para decidir quando a complexidade justifica uma spec formal.
