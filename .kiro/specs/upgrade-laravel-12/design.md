# Design Document: Upgrade Laravel 9 → 12

## Overview

O upgrade é conduzido como uma sequência de quatro estados estáveis do framework (`9.52 → 10 → 11 → 12`), cada um validado por um Checkpoint antes do próximo. A estratégia prioriza **estabilidade sobre velocidade**: o salto 9→10 concentra a maior parte do esforço (mudanças de código e de dependências); 10→11 e 11→12 são incrementais e leves. Cleanups de dívida técnica (remoção de pacotes abandonados, correção do TCPDF) são executados no salto em que se tornam possíveis, evitando trabalho duplicado.

Premissa central: **o projeto não versiona testes** (`tests/` está no `.gitignore`). Por isso, a primeira entrega não é código de framework, mas a construção de uma Rede_de_Testes — sem ela, nenhum Checkpoint é confiável.

## Estado Atual e Alvo

| Aspecto | Atual | Alvo |
|---|---|---|
| `laravel/framework` | v9.52.17 | v12.x |
| `php` (constraint) | `^8.0.2` | `^8.2` |
| PHP dev / prod | 8.2 / 8.2 | 8.2 / 8.2 |
| Advisories PHP (`composer audit`) | 8 (tcpdf 7, php-jwt 1) + 1 MEDIUM ignorada | 0 ativos não justificados |
| Suporte de segurança | EOL | Ativo (L12) |

## Estratégia Incremental (ondas)

```
Onda 0  Rede de testes + PHP ^8.2            (pré-requisito)
Onda 1  Laravel 10 + deps L10 + cleanups     (cors, recaptcha)   → Checkpoint
Onda 2  TCPDF ≥6.8 via wrapper L10           (resolve 7 CVEs)    → Checkpoint
Onda 3  Laravel 11 + Sanctum 4 + deps L11                        → Checkpoint
Onda 4  Laravel 12 + deps L12                                    → Checkpoint
Onda 5  php-jwt 7 + remoção do ignore-id + reavaliação log-viewer → Checkpoint final
```

A separação do TCPDF (Onda 2) do salto de framework (Onda 1) é deliberada: isola a validação de PDF, que é um Fluxo_Crítico com dois motores distintos (TCPDF e DomPDF).

## Matriz de Compatibilidade de Dependências

Faixas-alvo por versão de framework. Versões exatas a confirmar no momento de cada onda via `composer why`/`composer outdated`.

| Pacote | Hoje | L10 | L11 | L12 | Observação |
|---|---|---|---|---|---|
| `laravel/framework` | ^9.0 | ^10.0 | ^11.0 | ^12.0 | núcleo |
| `laravel/sanctum` | ^2.14 | ^3.2 | ^4.0 | ^4.0 | Sanctum 3 muda config; revisar `config/sanctum.php` |
| `laravel/ui` | ^3.4 | ^4.0 | ^4.0 | ^4.0 | — |
| `laravel/socialite` | ^5.16 | ^5.x | ^5.x | ^5.x | ✅ já cobre L10–12 |
| `laravel/tinker` | ^2.7 | ^2.x | ^2.x | ^2.x | ✅ |
| `spatie/laravel-permission` | ^6.9 | ^6.x | ^6.x | ^6.x | v6 cobre L9–L12; manter na última v6 |
| `spatie/laravel-ignition` (dev) | ^1.0 | ^2.0 | (substituído) | — | em L11+ a ignition foi para o core; revisar |
| `nunomaduro/collision` (dev) | ^6.1 | ^7.0 | ^8.0 | ^8.0 | acompanha PHPUnit |
| `phpunit/phpunit` (dev) | ^9.5 | ^10.0 | ^10/^11 | ^11.0 | — |
| `elibyy/tcpdf-laravel` | ^9.1 | ^10/^13 | ^13 | ^13 | **destrava tcpdf ≥6.8** (resolve 7 CVEs) |
| `tecnickcom/tcpdf` (transitivo) | 6.6.2 | ≥6.8 | ≥6.8 | ≥6.8 | hoje travado em `6.6.*` pelo wrapper 9.x |
| `barryvdh/laravel-dompdf` | `*` | fixar | fixar | fixar | pinar por compat |
| `barryvdh/laravel-debugbar` (dev) | ^3.14 | ^3.x | ^3.x | ^3.x | ✅ |
| `barryvdh/laravel-ide-helper` (dev) | ^2.15 | ^3.0 | ^3.0 | ^3.0 | — |
| `yajra/laravel-datatables-oracle` | `*` | ^10 | ^11 | ^12 | **fixar** (hoje `*`, risco) |
| `laravellegends/pt-br-validator` | ^9.1 | ^10 | ^11 | ^12 | versão acompanha major do Laravel |
| `arcanedev/log-viewer` | `*` | verificar | **risco** | **risco** | avaliar troca por `opcodesio/log-viewer` |
| `renatomarinho/laravel-page-speed` | ^2.1 | verificar | verificar | verificar | confirmar suporte |
| `laracasts/flash` | ^3.2 | ^3.x | ^3.x | ^3.x | ✅ |
| `biscolab/laravel-recaptcha` | ^6.1 | **remover** | — | — | abandonado; consolidar no recaptchav3 |
| `fruitcake/laravel-cors` | ^3.0 | **remover** | — | — | abandonado; CORS nativo |
| `josiasmontag/laravel-recaptchav3` | ^1.0 | ^1.x | ^1.x | ^1.x | confirmar compat L11/12 |
| `lucascudo/laravel-pt-br-localization` (dev) | ^1.2 | verificar | verificar | verificar | — |

## Principais Mudanças de Código por Salto

### Laravel 9 → 10 (maior esforço)
- Remoção da propriedade `$dates` nos models → migrar para `$casts` (auditar `BaseModel` e os 24 models).
- `dispatchNow()` → `dispatchSync()`.
- Tipos de retorno nativos passam a ser exigidos em assinaturas sobrescritas (ex.: métodos de Service Providers, middlewares, `Console\Kernel`).
- Revisão de `app/Http/Kernel.php`, `app/Exceptions/Handler.php` e provider de eventos.
- Predis/Redis e mudanças de `langPath`/`lang/` (validar traduções pt-BR).
- Ignition 1→2.

### Laravel 10 → 11
- PHP 8.2 obrigatório (já atendido).
- Sanctum 3→4.
- A estrutura "slim" do skeleton é **opcional** — manter o esqueleto atual (`Http/Kernel`, `Console/Kernel`, `Exceptions/Handler`) é suportado e reduz risco; **não** adotar a estrutura slim neste upgrade.
- Carbon 2 ou 3 (manter 2 para minimizar risco).
- Doctrine/DBAL deixa de ser necessário para alguns comandos de schema (sem impacto direto se migrations já usam o builder nativo).

### Laravel 11 → 12
- Salto majoritariamente sem mudanças de código. Atualizar dependências de primeira-parte para `^12` e validar.

## Estratégia de Validação

Como `tests/` é gitignored, a Rede_de_Testes combina duas camadas:

1. **Smoke tests automatizados (mínimos, versionados):** um pequeno conjunto de testes Feature HTTP que exercite boot, autenticação, uma rota autorizada (403/200), uma listagem DataTable (JSON) e uma geração de PDF. Requer **remover `/tests` do `.gitignore`** (decisão a confirmar com o mantenedor).
2. **Checklist de QA manual (versionado em `docs/`):** roteiro passo a passo cobrindo cada Fluxo_Crítico, executado a cada Checkpoint no browser (`http://maieutica.test`).

Cada Checkpoint executa: `npm run prod` (build), `php artisan about` (boot), `composer audit`, smoke tests e o checklist manual.

## Resolução dos Advisories

| Advisory | Severidade | Resolvido em | Onda |
|---|---|---|---|
| TCPDF (7 CVEs) | high/medium | tcpdf ≥6.8 via wrapper L10+ | 2 |
| Laravel "File Validation Bypass" (`PKSA-8qx3-n5y5-vvnd`) | medium | Laravel ≥10.48.29 → remover `ignore-id` | 1 |
| `firebase/php-jwt` criptografia fraca | low | php-jwt 7 | 5 |

## Riscos e Mitigações

| Risco | Impacto | Mitigação |
|---|---|---|
| Ausência de testes versionados | Regressões silenciosas | Onda 0 cria Rede_de_Testes antes de qualquer salto |
| `yajra/datatables` com constraint `*` | Resolução imprevisível | Fixar por major em cada onda |
| `arcanedev/log-viewer` sem suporte L11+ | Quebra de `/log-viewer` | Avaliar/trocar por `opcodesio/log-viewer` na Onda 3 |
| Sanctum 3→4 muda config/tokens | Quebra de auth de API | Revisar `config/sanctum.php` e fluxo de tokens no Checkpoint da Onda 3 |
| TCPDF dev-main vs tag estável | Build não reproduzível | Exigir tag estável ≥6.8; nunca `dev-main` |
| PHP < 8.2 na Hostinger | Deploy quebra | Confirmar PHP 8.2 no hPanel antes do deploy (Req. 2) |
| Mix/webpack defasado em majors futuros | Build de assets quebra | Frontend tooling é esforço separado; manter Mix 6 nesta spec |

## Plano de Rollback

- Cada Salto_Major é um commit isolado; rollback = redeploy do commit anterior + `composer install` do lock anterior.
- Produção: manter a tag/commit anterior disponível; em falha crítica, reverter o checkout e rodar `composer install --no-dev` + clears/caches.
- Sem mudanças destrutivas de banco (Req. 8.4), o rollback de código é suficiente.

## Impacto no Deploy (Hostinger, sem Docker)

- Procedimento atual preservado: SSH → `git pull` (webhook) → `composer install` → `php artisan optimize:clear` → `config:cache`/`route:cache`/`view:cache`.
- `composer.json` carrega `config.policy.advisories.ignore-id` (necessário enquanto em L9; **removido na Onda 1** ao atingir L10.48.29+).
- Atualizar `docs/MANUAL_ATUALIZACAO_PRODUCAO.md` com a exigência de PHP 8.2 e quaisquer novos passos pós-deploy.
