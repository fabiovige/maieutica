---
description: Modelo unificado Kids, classificação por birth_date, scopes adults/children
---

Leia `docs/TIPOS_DE_PACIENTES.md` na íntegra. Use-o para responder perguntas sobre pacientes.

## Modelo Unificado

**Todos os pacientes estão na tabela `kids`** — crianças e adultos. A distinção é calculada dinamicamente pela data de nascimento.

**Classificação automática:**
- `$kid->is_adult` — accessor computado: `diffInYears(birth_date, now()) >= 13` retorna `true`
- Constante: `Kid::ADULT_AGE_YEARS = 13`
- NÃO existe flag `is_adult` no banco — é sempre calculado

**Scopes:**
```php
Kid::adults()->get();           // idade >= 13
Kid::children()->get();         // idade < 13 (ou sem birth_date)
Kid::denverEligible()->get();   // crianças elegíveis à Avaliação Multidimensional (até 60 meses) — nome do scope mantido por compatibilidade
```

## Diferenças por Tipo

| Aspecto | Criança (< 13) | Adulto (>= 13) |
|---------|----------------|-----------------|
| Checklists Avaliação Multidimensional | Sim | Não |
| Planos de desenvolvimento | Sim | Não |
| Prontuários | Sim (morphMany) | Sim (morphMany) |
| Documentos gerados | Sim | Sim |
| Responsável legal | Sim (`responsible_id`) | Opcional |
| Atribuição a profissional | `kid_professional` | `kid_professional` |

## Regras Críticas

- `birth_date` é obrigatório — a classificação depende dele
- Nunca usar flag booleana para classificar — sempre calcular pela idade
- Atribuição a profissional: pivot `kid_professional` com campo `is_primary` (funciona para ambos)
- Cadastro de adulto via UI: disponível apenas para Admin (limitação conhecida)

Para autorização e relacionamentos, consulte `/auth`. Para prontuários, consulte `/prontuarios`.
