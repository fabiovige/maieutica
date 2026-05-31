# Implementation Plan: Upgrade Laravel 9 → 12

## Overview

Migração incremental do framework em quatro Saltos_Major (`9.52 → 10 → 11 → 12`), precedida pela construção de uma Rede_de_Testes e seguida por cleanups de segurança. Cada onda termina em um Checkpoint obrigatório (build + boot + audit + smoke tests + checklist de QA). Tarefas marcadas com `*` são opcionais/oportunistas. O trabalho ocorre em branch dedicada, **sem merge automático para `main`**.

## Tasks

- [ ] 1. Onda 0 — Rede de testes e pré-requisitos
  - [ ] 1.1 Decidir e versionar a estratégia de testes
    - Remover `/tests` do `.gitignore` (ou justificar checklist manual) e versionar a Rede_de_Testes
    - _Requisitos: 3.1, 3.2_
  - [ ] 1.2 Criar smoke tests Feature HTTP mínimos
    - Boot, login com reCAPTCHA, rota autorizada (200/403), DataTable (JSON), geração de PDF (TCPDF e DomPDF)
    - _Requisitos: 3.3_
  - [ ] 1.3 Criar checklist de QA manual em `docs/`
    - Roteiro por Fluxo_Crítico para execução no browser a cada Checkpoint
    - _Requisitos: 3.3, 3.4_
  - [ ] 1.4 Atualizar constraint de PHP e confirmar produção
    - `composer.json`: `php` `^8.0.2` → `^8.2`; confirmar PHP 8.2 no hPanel da Hostinger
    - _Requisitos: 2.1, 2.2, 2.4_

- [ ] 2. Checkpoint 0 — Baseline verde
  - Rodar a Rede_de_Testes contra o Laravel 9.52.17 atual e garantir que tudo passa (baseline de comparação)
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 3. Onda 1 — Laravel 10
  - [ ] 3.1 Atualizar `laravel/framework` para `^10.0` e dependências de primeira-parte
    - `laravel/sanctum ^3.2`, `laravel/ui ^4.0`, `spatie/laravel-ignition ^2.0`, `nunomaduro/collision ^7.0`, `phpunit/phpunit ^10.0`, `barryvdh/laravel-ide-helper ^3.0`
    - _Requisitos: 1.1, 4.1, 4.5_
  - [ ] 3.2 Atualizar dependências de terceiros para faixas L10
    - `yajra/laravel-datatables-oracle ^10` (fixar), `laravellegends/pt-br-validator ^10`, demais conforme matriz
    - _Requisitos: 4.1, 4.2_
  - [ ] 3.3 Aplicar mudanças de código 9→10
    - Migrar `$dates` → `$casts` nos models; `dispatchNow` → `dispatchSync`; tipos de retorno; revisar `Http/Kernel`, `Exceptions/Handler`, providers
    - _Requisitos: 7.4, 7.5, 7.6_
  - [ ] 3.4 Remover `fruitcake/laravel-cors` e usar CORS nativo
    - Configurar `config/cors.php` + middleware `HandleCors`; validar CORS das APIs
    - _Requisitos: 6.1_
  - [ ] 3.5 Eliminar `biscolab/laravel-recaptcha`
    - Consolidar reCAPTCHA no `josiasmontag/laravel-recaptchav3`; validar login
    - _Requisitos: 6.2_
  - [ ] 3.6 Remover o `ignore-id` da File Validation Bypass
    - Com Laravel ≥10.48.29, remover `config.policy.advisories.ignore-id` (`PKSA-8qx3-n5y5-vvnd`) do `composer.json`
    - _Requisitos: 5.2_

- [ ] 4. Checkpoint 1 — Laravel 10 estável
  - Build (`npm run prod`), boot, `composer audit`, smoke tests, checklist de QA
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 5. Onda 2 — TCPDF ≥ 6.8
  - [ ] 5.1 Atualizar `elibyy/tcpdf-laravel` para versão compatível com L10 (≥10/13)
    - Instalar `tecnickcom/tcpdf` em tag estável ≥6.8 (nunca `dev-main`)
    - _Requisitos: 5.1_
  - [ ] 5.2 Validar todos os modelos de PDF (TCPDF e DomPDF)
    - Conferir download forçado, fonte, nomenclatura, marca d'água e os 6 modelos de documento
    - _Requisitos: 7.1_

- [ ] 6. Checkpoint 2 — PDFs validados
  - `composer audit` deve zerar as 7 advisories do TCPDF
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 7. Onda 3 — Laravel 11
  - [ ] 7.1 Atualizar `laravel/framework` para `^11.0` e primeira-parte
    - `laravel/sanctum ^4.0`, `nunomaduro/collision ^8.0`, PHPUnit conforme matriz; manter esqueleto clássico (não adotar slim)
    - _Requisitos: 1.1, 4.1_
  - [ ] 7.2 Revisar Sanctum 3→4 (config e tokens)
    - Validar `config/sanctum.php` e o fluxo de tokens de API
    - _Requisitos: 4.1, 7.6_
  - [ ] 7.3 Resolver `arcanedev/log-viewer`
    - Confirmar suporte a L11; se ausente, migrar para `opcodesio/log-viewer` preservando a rota `/log-viewer`
    - _Requisitos: 4.3, 4.4_
  - [ ]* 7.4 Atualizar dependências de terceiros remanescentes para L11
    - `laravellegends/pt-br-validator ^11`, `yajra ^11`, demais conforme matriz
    - _Requisitos: 4.1, 4.2_

- [ ] 8. Checkpoint 3 — Laravel 11 estável
  - Build, boot, audit, smoke tests, checklist de QA (atenção a auth/API e log-viewer)
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 9. Onda 4 — Laravel 12
  - [ ] 9.1 Atualizar `laravel/framework` para `^12.0` e primeira-parte para `^12`
    - Salto leve; atualizar `yajra ^12`, `laravellegends/pt-br-validator ^12`, demais `^12`
    - _Requisitos: 1.1, 4.1, 4.2_

- [ ] 10. Checkpoint 4 — Laravel 12 estável
  - Build, boot, audit, smoke tests, checklist de QA
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 11. Onda 5 — Cleanups finais de segurança
  - [ ] 11.1 Atualizar `firebase/php-jwt` para 7.x
    - Validar pontos de uso de JWT
    - _Requisitos: 5.3_
  - [ ]* 11.2 Reavaliar pacotes restantes e `composer audit` final
    - Garantir `composer audit` sem advisories ativos não justificados
    - _Requisitos: 5.4_
  - [ ] 11.3 Atualizar documentação de deploy
    - `docs/MANUAL_ATUALIZACAO_PRODUCAO.md`: exigência de PHP 8.2 e novos passos; revisar `CLAUDE.md`/`tech.md` (versão do framework)
    - _Requisitos: 8.5_

- [ ] 12. Checkpoint final — Validação completa
  - `composer audit` limpo, todos os Fluxos_Críticos validados, documentação atualizada
  - Confirmar reversibilidade (rollback) e ausência de mudanças destrutivas de banco
  - _Requisitos: 5.4, 8.3, 8.4_

## Notes

- Tarefas `*` são opcionais/oportunistas e podem ser agrupadas para reduzir ciclos.
- Cada onda referencia requisitos específicos para rastreabilidade.
- Os Checkpoints são barreiras: não avançar com Fluxo_Crítico quebrado (Req. 1.4).
- **Nunca** `migrate:fresh`/`ALTER TABLE`/esvaziar banco (Req. 8.4).
- **Sem merge automático** para `main`/`develop` — decisão manual do mantenedor (Req. 1.5).
- Recomenda-se avaliar o **Laravel Shift** para automatizar cada salto e reduzir risco/tempo.

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.2", "1.3", "1.4"] },
    { "id": 1, "tasks": ["2"] },
    { "id": 2, "tasks": ["3.1", "3.2", "3.3", "3.4", "3.5", "3.6"] },
    { "id": 3, "tasks": ["4"] },
    { "id": 4, "tasks": ["5.1", "5.2"] },
    { "id": 5, "tasks": ["6"] },
    { "id": 6, "tasks": ["7.1", "7.2", "7.3", "7.4"] },
    { "id": 7, "tasks": ["8"] },
    { "id": 8, "tasks": ["9.1"] },
    { "id": 9, "tasks": ["10"] },
    { "id": 10, "tasks": ["11.1", "11.2", "11.3"] },
    { "id": 11, "tasks": ["12"] }
  ]
}
```
