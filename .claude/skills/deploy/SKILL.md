---
description: Manual de atualização em produção, regras de deploy
---

Leia `docs/MANUAL_ATUALIZACAO_PRODUCAO.md` na íntegra. Use-o para responder perguntas sobre deploy em produção.

## Ambiente

- **Produção:** maieuticavaliacom.br
- **Este sistema está em produção** — siga os passos exatamente, sem improvisar

## Regras de Deploy

- Sempre testar localmente antes de enviar para produção
- Migrations devem ser reversíveis (método `down()` funcional)
- Nunca rodar `migrate:fresh` em produção
- Compilar assets (`npm run production`) antes de deploy
- Limpar caches após deploy (`composer clear`)
- Se houver mudança em `.env`, atualizar manualmente no servidor
- Backup do banco antes de migrations que alteram dados
