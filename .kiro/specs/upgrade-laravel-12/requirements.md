# Requirements Document

## Introduction

Esta spec define o upgrade incremental do framework da plataforma Maiêutica de **Laravel 9.52 para Laravel 12**, percorrendo as versões intermediárias (`9 → 10 → 11 → 12`). O objetivo é restaurar o suporte de segurança (Laravel 9 e 10 estão em EOL) e resolver os advisories de segurança remanescentes que não têm correção dentro do Laravel 9: as 7 CVEs do `tecnickcom/tcpdf` (bloqueadas pelo wrapper `elibyy/tcpdf-laravel`), a advisory MEDIUM "File Validation Bypass" do `laravel/framework` e a advisory LOW do `firebase/php-jwt`. O upgrade ocorre num sistema **em produção** (Hostinger, sem Docker), portanto a estabilidade e a reversibilidade são requisitos de primeira classe. A migração deve preservar 100% das funcionalidades existentes, em especial geração de PDF (TCPDF + DomPDF), DataTables server-side, autorização permission-based (Spatie) e os componentes Vue montados como ilhas em Blade.

## Glossary

- **Upgrade_Framework**: O processo completo de migração do Laravel 9.52 para o Laravel 12 descrito nesta spec.
- **Salto_Major**: A migração de uma versão major do Laravel para a próxima (9→10, 10→11 ou 11→12), tratada como uma unidade de trabalho com checkpoint próprio.
- **Checkpoint**: Ponto de validação ao fim de cada Salto_Major em que a aplicação é exercitada (build, boot, suíte de QA) antes de prosseguir.
- **Rede_de_Testes**: Conjunto mínimo de testes automatizados e/ou checklist de QA manual que cobre os fluxos críticos, usado para detectar regressões a cada Checkpoint.
- **Fluxo_Crítico**: Funcionalidade cuja quebra impacta diretamente o uso em produção — login/reCAPTCHA, autorização, CRUD de pacientes (kids), avaliação Denver/checklist, prontuários, geração de PDF, listagens DataTables e envio de e-mails.
- **Pacote_Abandonado**: Dependência sem manutenção ativa declarada como `abandoned` pelo Composer (`fruitcake/laravel-cors`, `biscolab/laravel-recaptcha`).
- **Advisory_Bloqueante**: Vulnerabilidade conhecida que só possui correção numa versão de framework superior à atual.
- **Ambiente_Produção**: Hostinger (hospedagem compartilhada, **sem Docker**), PHP 8.2, usuário único, path `/home/u350247040/domains/maieuticavalia.com.br/public_html`.
- **Ambiente_Dev**: Stack Docker local (containers `maieutica_app`, `maieutica_db`, etc.), PHP 8.2.
- **Wrapper_TCPDF**: O pacote `elibyy/tcpdf-laravel`, que adapta o `tecnickcom/tcpdf` ao Laravel e cuja constraint de versão hoje (9.x) bloqueia o tcpdf em `6.6.*|dev-main`.

## Requirements

### Requisito 1: Caminho Incremental com Checkpoints

**User Story:** Como mantenedor da plataforma, quero migrar o framework em saltos major sequenciais e validados, para que cada etapa seja pequena, reversível e auditável.

#### Critérios de Aceitação

1. THE Upgrade_Framework SHALL executar os Saltos_Major na ordem `9.52 → 10 → 11 → 12`, sem pular versões intermediárias.
2. THE Upgrade_Framework SHALL concluir um Checkpoint completo (build de assets, boot do framework, execução da Rede_de_Testes) ao final de cada Salto_Major antes de iniciar o próximo.
3. THE Upgrade_Framework SHALL registrar cada Salto_Major em um commit próprio e isolado, permitindo reverter um salto sem desfazer os anteriores.
4. IF um Checkpoint detecta regressão em um Fluxo_Crítico, THEN THE Upgrade_Framework SHALL interromper o avanço e corrigir a regressão antes de prosseguir para o próximo Salto_Major.
5. THE Upgrade_Framework SHALL ocorrer em branch dedicada, sem merge automático para `main` ou `develop` — o merge é decisão manual do mantenedor.

### Requisito 2: Paridade de Ambiente e Versão de PHP

**User Story:** Como mantenedor, quero garantir que a versão de PHP requerida seja suportada em dev e produção, para que o upgrade não quebre o deploy na Hostinger.

#### Critérios de Aceitação

1. THE Upgrade_Framework SHALL atualizar a constraint `php` em `composer.json` de `^8.0.2` para `^8.2`, compatível com Laravel 11 e 12.
2. THE Upgrade_Framework SHALL confirmar que o Ambiente_Produção (Hostinger) executa PHP 8.2 antes de qualquer deploy do framework atualizado.
3. THE Upgrade_Framework SHALL manter o Ambiente_Dev (Docker, PHP 8.2) em paridade de versão major de PHP com o Ambiente_Produção.
4. IF a versão de PHP da Hostinger for inferior a 8.2 em algum momento, THEN THE Upgrade_Framework SHALL bloquear o deploy e sinalizar a necessidade de ajuste de PHP no hPanel antes de prosseguir.

### Requisito 3: Rede de Testes antes da Migração

**User Story:** Como mantenedor, quero uma rede de segurança de testes/QA antes de migrar, para que regressões introduzidas por um Salto_Major sejam detectadas de forma confiável.

#### Critérios de Aceitação

1. THE Upgrade_Framework SHALL estabelecer uma Rede_de_Testes que cubra todos os Fluxos_Críticos antes de iniciar o primeiro Salto_Major.
2. THE Upgrade_Framework SHALL remover `tests/` do `.gitignore` OU manter um checklist de QA manual versionado, de modo que a Rede_de_Testes seja reproduzível e rastreável.
3. THE Rede_de_Testes SHALL incluir verificação de login com reCAPTCHA, autorização permission-based, CRUD de pacientes, avaliação Denver/checklist, visualização e versionamento de prontuários, geração de PDF via TCPDF e via DomPDF, listagens DataTables server-side e envio de pelo menos um e-mail do ciclo de vida do usuário.
4. THE Upgrade_Framework SHALL executar a Rede_de_Testes em cada Checkpoint e exigir que ela passe antes de avançar.

### Requisito 4: Compatibilidade de Dependências de Terceiros

**User Story:** Como mantenedor, quero que todas as dependências sejam atualizadas para versões compatíveis com o framework alvo, para que o `composer install` resolva sem conflitos em dev e produção.

#### Critérios de Aceitação

1. WHEN um Salto_Major é executado, THE Upgrade_Framework SHALL atualizar cada dependência direta para a faixa de versão compatível com o Laravel alvo daquele salto, conforme a matriz de compatibilidade do documento de design.
2. THE Upgrade_Framework SHALL substituir a constraint não delimitada (`*`) de `yajra/laravel-datatables-oracle` e demais pacotes por constraints fixadas por major compatível com o framework alvo.
3. THE Upgrade_Framework SHALL avaliar a compatibilidade de `arcanedev/log-viewer` com Laravel 11/12 e, caso não suportado, substituí-lo por uma alternativa mantida (ex.: `opcodesio/log-viewer`), preservando a rota `/log-viewer`.
4. IF uma dependência direta não possuir versão compatível com o Laravel alvo, THEN THE Upgrade_Framework SHALL propor uma substituição mantida ou a remoção do pacote, documentando a decisão antes de aplicá-la.
5. THE Upgrade_Framework SHALL manter o sistema de autorização baseado em permissões via Spatie Laravel Permission, sem regressão de permissões ou roles existentes.

### Requisito 5: Resolução dos Advisories Remanescentes

**User Story:** Como responsável pela segurança, quero que o upgrade resolva as vulnerabilidades que não tinham correção no Laravel 9, para que o `composer audit` fique limpo.

#### Critérios de Aceitação

1. WHEN o Wrapper_TCPDF for atualizado para uma versão compatível com Laravel 10+, THE Upgrade_Framework SHALL instalar `tecnickcom/tcpdf` em versão **≥ 6.8** (tag estável), eliminando as 7 advisories do TCPDF.
2. WHEN o framework atingir Laravel 10.48.29 ou superior, THE Upgrade_Framework SHALL remover a entrada `config.policy.advisories.ignore-id` referente à advisory "File Validation Bypass" (`PKSA-8qx3-n5y5-vvnd`) do `composer.json`, pois ela passa a estar corrigida.
3. THE Upgrade_Framework SHALL avaliar a atualização de `firebase/php-jwt` para a versão 7.x, validando os pontos de uso de JWT, para eliminar a advisory LOW de criptografia fraca.
4. THE Upgrade_Framework SHALL produzir, ao final, um resultado de `composer audit` sem advisories ativos não justificados.

### Requisito 6: Remoção de Pacotes Abandonados

**User Story:** Como mantenedor, quero eliminar dependências abandonadas durante o upgrade, para reduzir superfície de risco e dívida técnica.

#### Critérios de Aceitação

1. THE Upgrade_Framework SHALL remover `fruitcake/laravel-cors` e configurar o tratamento de CORS por meio do middleware nativo do Laravel (`HandleCors` + `config/cors.php`), preservando o comportamento atual de CORS.
2. THE Upgrade_Framework SHALL eliminar o Pacote_Abandonado `biscolab/laravel-recaptcha`, consolidando a funcionalidade de reCAPTCHA no pacote mantido `josiasmontag/laravel-recaptchav3` já presente, OU em substituição equivalente, sem regressão no fluxo de login.
3. THE Upgrade_Framework SHALL validar, após cada remoção, que o Fluxo_Crítico associado (CORS de APIs, reCAPTCHA no login) continua funcionando.

### Requisito 7: Preservação de Funcionalidades Críticas

**User Story:** Como usuário da clínica, quero que todas as funcionalidades continuem operando após o upgrade, para que minha rotina de trabalho não seja afetada.

#### Critérios de Aceitação

1. THE Upgrade_Framework SHALL preservar a geração de PDF tanto via TCPDF quanto via DomPDF, incluindo download forçado e nomenclatura de arquivos.
2. THE Upgrade_Framework SHALL preservar as listagens DataTables server-side (rotas `*/datatable/index`) sem alteração de comportamento para o usuário.
3. THE Upgrade_Framework SHALL preservar os componentes Vue 3 (Options API) montados como ilhas em templates Blade, garantindo que o build via Laravel Mix continue funcional.
4. THE Upgrade_Framework SHALL preservar os Observers, Domain Loggers e o logging em banco (tabela `logs`) sem perda de eventos de auditoria.
5. THE Upgrade_Framework SHALL preservar o modelo unificado de pacientes (`kids`) e a classificação por `birth_date` (constante `Kid::ADULT_AGE_YEARS`).
6. THE Upgrade_Framework SHALL manter o padrão de autorização `can()` no PHP e `@can()` no Blade, sem introduzir `hasRole()` para autorização.

### Requisito 8: Estabilidade de Produção e Reversibilidade

**User Story:** Como mantenedor, quero poder reverter o upgrade com segurança, para que um problema em produção possa ser contornado rapidamente.

#### Critérios de Aceitação

1. THE Upgrade_Framework SHALL ser deployável seguindo o procedimento existente da Hostinger (SSH + `composer install` + clears/caches), sem depender de Docker em produção.
2. THE Upgrade_Framework SHALL preservar `composer.lock` versionado, garantindo que o `composer install` de produção instale exatamente as versões validadas em dev.
3. IF o deploy em produção apresentar falha crítica, THEN THE Upgrade_Framework SHALL permitir rollback para a tag/commit anterior do framework via redeploy do estado anterior, sem perda de dados.
4. THE Upgrade_Framework SHALL nunca executar `migrate:fresh`, `ALTER TABLE` direto ou esvaziamento de banco como parte do processo; mudanças de schema, se houver, SHALL usar migrations reversíveis.
5. THE Upgrade_Framework SHALL atualizar a documentação de deploy (`docs/MANUAL_ATUALIZACAO_PRODUCAO.md`) com quaisquer passos novos exigidos pelo framework atualizado.
