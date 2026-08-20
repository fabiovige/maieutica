# Orientação para publicação dos agendamentos na Hostinger

Este documento reúne o que precisa ser configurado na Hostinger para colocar em
produção a integração completa de agendamentos do Maiêutica.

O fluxo final é:

```text
WhatsApp
  -> N8N cria o evento
    -> Google Calendar
      -> Hostinger executa agenda:sync
        -> Maiêutica lista o agendamento
          -> recepção confirma
            -> Maiêutica chama o webhook público do N8N
              -> Evolution envia WhatsApp ao paciente e ao profissional
```

## 1. Diferença entre os ambientes

O Docker existente no projeto é usado somente no desenvolvimento local. A
Hostinger executa PHP, Laravel, banco e cron diretamente na hospedagem.

Na Hostinger:

- não use comandos `docker compose`;
- não use caminhos internos como `/var/www/html`;
- use o caminho absoluto real da conta de hospedagem;
- configure o cron pelo painel da Hostinger;
- use a URL pública HTTPS do N8N administrado pelo EasyPanel.

## 2. Como o deploy de produção funciona

O deploy do Maiêutica é automático:

```text
branch de funcionalidade
  -> Pull Request
    -> merge na main
      -> webhook do GitHub
        -> Hostinger inicia o deploy
```

O procedimento normal de publicação **não** usa `git pull` manual por SSH. O
merge na `main` é o evento que autoriza e inicia o deploy.

O painel da Hostinger deve ser usado para:

- acompanhar o status e os logs do deploy;
- configurar as variáveis de ambiente de produção;
- configurar o cron;
- executar comandos manuais de diagnóstico quando necessário;
- confirmar qual commit da `main` está publicado.

Configurações externas ao Git não são recriadas pelo merge:

- `.env` de produção;
- chave JSON da Service Account;
- compartilhamento do Google Calendar;
- cron do Laravel;
- workflow e credenciais do N8N/Evolution.

## 3. Antes da publicação

Confirme que:

- a branch da funcionalidade foi revisada e está pronta para Pull Request;
- o destino do Pull Request é a `main`;
- o webhook GitHub -> Hostinger está ativo;
- há acesso aos logs do deploy automático;
- o workflow N8N está salvo e ativo;
- o webhook N8N usa Header Auth;
- os dois nodes EvoGo usam a credencial e o token corretos da Evolution;
- a agenda está compartilhada com a Service Account;
- há backup recente do banco de produção;
- a chave JSON da Service Account está disponível em local seguro.

Não envie chaves ou tokens por mensagens e não os coloque no Git.

## 4. Descobrir os caminhos reais da Hostinger

Com acesso SSH, execute:

```bash
pwd
which php
php -v
```

Entre no diretório da aplicação e confirme:

```bash
cd /CAMINHO/REAL/DA/APLICACAO
pwd
test -f artisan && echo "Laravel encontrado"
```

Exemplos de caminhos possíveis, que devem ser substituídos pelos valores reais:

```text
/home/USUARIO/domains/DOMINIO/public_html
/home/USUARIO/htdocs
/home/USUARIO/public_html
```

Não copie um caminho de exemplo sem conferir no servidor.

## 5. Fazer backup

Antes de atualizar o código:

```bash
cd /CAMINHO/REAL/DA/APLICACAO
```

Faça backup do banco pelo painel da Hostinger ou pelo terminal:

```bash
mysqldump -u USUARIO_DB -p NOME_DB > backup_agendamentos_$(date +%Y%m%d_%H%M%S).sql
```

Guarde também uma cópia do `.env`:

```bash
cp .env ../env_backup_$(date +%Y%m%d_%H%M%S)
```

O backup deve ficar fora de `public_html`.

## 6. Publicar o código pelo GitHub

O fluxo oficial de publicação é:

1. Finalize e valide a branch da funcionalidade.
2. Envie os commits para o GitHub.
3. Abra um Pull Request para a `main`.
4. Revise o diff e confirme que não há `.env`, chaves JSON ou tokens.
5. Faça o merge na `main`.
6. Acompanhe no painel da Hostinger o deploy iniciado pelo webhook.
7. Confirme que o commit publicado é o commit resultante do merge.

Não execute `git pull` manual como parte do procedimento normal. Isso pode
divergir do estado administrado pelo deploy automático da Hostinger.

### Comandos esperados no deploy automático

Confira na configuração ou nos logs da Hostinger se o processo executa, nessa
ordem ou em ordem equivalente:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

O seeder de permissões também precisa ser executado nesta publicação:

```bash
php artisan db:seed --class=RoleAndPermissionSeeder --force
```

Se o deploy automático não executa migrations ou seeders, rode esses comandos
uma vez pelo terminal da Hostinger após o deploy. Não assuma que o merge, por si
só, alterou o banco.

Se a Hostinger oferece um campo de script pós-deploy, mantenha esses comandos
nele para que futuras publicações sejam consistentes. Antes de modificar esse
script, salve uma cópia da configuração atual.

### Falha no deploy

Se o deploy falhar:

1. Não repita merges sem entender a causa.
2. Consulte o log da etapa que falhou.
3. Confirme se o código anterior continua ativo.
4. Corrija em uma nova branch e abra outro Pull Request.
5. Para reverter código já publicado, prefira um `revert` no GitHub seguido de
   merge na `main`, preservando o histórico.
6. Não faça rollback de migration automaticamente sem analisar se há dados
   gravados na nova tabela.

## 7. Instalar a credencial do Google Calendar

A chave JSON da Service Account deve ficar fora do diretório público da
aplicação.

Crie um diretório privado, por exemplo:

```bash
mkdir -p /home/USUARIO/secure
chmod 700 /home/USUARIO/secure
```

Envie a credencial para:

```text
/home/USUARIO/secure/google-calendar.json
```

Restrinja sua leitura:

```bash
chmod 600 /home/USUARIO/secure/google-calendar.json
```

Confirme que o arquivo pode ser lido pelo mesmo usuário que executa o PHP:

```bash
test -r /home/USUARIO/secure/google-calendar.json \
  && echo "Credencial legível" \
  || echo "Credencial NÃO legível"
```

Nunca coloque a credencial em:

```text
public_html/
public/
storage/app/public/
```

## 8. Confirmar o compartilhamento do Calendar

No Google Calendar, a agenda usada pelo N8N precisa estar compartilhada com o
`client_email` da Service Account.

Permissão recomendada:

```text
Ver todos os detalhes de eventos
```

O Maiêutica usa o escopo `calendar.readonly` e não precisa alterar eventos.

Copie também o valor de **Configurações e compartilhamento > Integrar agenda >
ID da agenda**. Não confunda esse valor com o ID da Service Account.

## 9. Configurar o `.env` da Hostinger

Edite o `.env` de produção sem substituir as demais configurações existentes.

### Google Calendar

```env
GOOGLE_CALENDAR_ID=ID_REAL_DA_AGENDA
GOOGLE_CALENDAR_CREDENTIALS=/home/USUARIO/secure/google-calendar.json
GOOGLE_CALENDAR_SYNC_DAYS=60
```

O caminho de `GOOGLE_CALENDAR_CREDENTIALS` precisa ser absoluto e válido na
Hostinger. Não use `/var/www/html`, pois esse é o caminho do Docker local.

### Webhook N8N

```env
N8N_APPOINTMENT_CONFIRMED_WEBHOOK=https://DOMINIO-PUBLICO-N8N/webhook/maieutica/agendamentos/confirmado
N8N_WEBHOOK_TOKEN=TOKEN_DO_HEADER_AUTH
N8N_WEBHOOK_TIMEOUT=10
N8N_NOTIFICATION_TEST_PHONE=
```

Regras:

- usar a Production URL do N8N;
- usar HTTPS;
- não usar `/webhook-test/`;
- usar o mesmo token salvo na credencial Header Auth do N8N;
- deixar `N8N_NOTIFICATION_TEST_PHONE` vazio em produção;
- não colocar `Bearer` em `N8N_WEBHOOK_TOKEN`: o Laravel adiciona esse prefixo;
- não usar colchetes, parênteses ou formatação Markdown na URL.

### Configurações gerais importantes

Confira também:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://DOMINIO-DO-MAIEUTICA
SESSION_SECURE_COOKIE=true
```

Não copie integralmente o `.env` local para a produção.

## 10. Verificar a migration e o seeder

Verifique primeiro o estado das migrations:

```bash
php artisan migrate:status
```

Se o deploy automático ainda não as executou, rode:

```bash
php artisan migrate --force
```

A migration esperada para esta funcionalidade é:

```text
2026_08_18_170508_create_appointments_table
```

Se o script de deploy ainda não executou o seeder, rode:

```bash
php artisan db:seed --class=RoleAndPermissionSeeder --force
```

As permissões criadas são:

- `appointment-list`;
- `appointment-list-all`;
- `appointment-confirm`.

O seeder atribui as permissões administrativas ao perfil administrador e a
visualização dos próprios agendamentos ao perfil profissional.

## 11. Verificar os caches

O deploy automático deve tratar os caches. Depois de alterar o `.env`
manualmente, ou se os logs do deploy não mostrarem essa etapa, execute:

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

Em seguida, recrie os caches de produção:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Se algum comando de cache falhar, não ignore a mensagem; corrija o erro antes
de reabrir a aplicação.

## 12. Validar as rotas

Confirme as rotas da tela:

```bash
php artisan route:list --path=appointments
```

Resultado esperado:

```text
GET|HEAD  appointments
POST      appointments/{appointment}/confirm
POST      appointments/{appointment}/refuse
```

Confirme também que o comando existe:

```bash
php artisan list | grep agenda
```

## 13. Testar o Google Calendar sem gravar

Execute:

```bash
php artisan agenda:sync --dry-run
```

O comando deve informar a janela consultada e a quantidade de eventos recebidos.

Exemplo:

```text
Consultando eventos de DD/MM/AAAA a DD/MM/AAAA...
Eventos recebidos: 7
Criados: 6
Execução em --dry-run: nada foi gravado.
```

Se aparecer:

```text
Integração com o Google Calendar não configurada
```

verifique `GOOGLE_CALENDAR_ID`, `GOOGLE_CALENDAR_CREDENTIALS`, permissões do
arquivo e cache de configuração.

Se retornar `403`, confirme o compartilhamento da agenda com o `client_email`
da Service Account.

## 14. Executar a primeira sincronização real

Depois do `--dry-run` bem-sucedido:

```bash
php artisan agenda:sync
```

Abra no navegador:

```text
https://DOMINIO-DO-MAIEUTICA/appointments
```

Confirme que:

- eventos reais foram importados;
- registros novos estão pendentes;
- data e horário estão corretos;
- nome, contato, especialidade e profissional solicitado foram interpretados;
- não existem registros artificiais `TESTE_*` em produção.

## 15. Configurar o cron da Hostinger

O Laravel agenda `agenda:sync` a cada cinco minutos. Para isso funcionar, a
Hostinger deve executar `schedule:run` uma vez por minuto.

No painel da Hostinger:

1. Acesse **Avançado > Tarefas Cron** ou a área equivalente.
2. Crie uma nova tarefa.
3. Configure a frequência para cada minuto:

```text
* * * * *
```

4. Configure o comando usando os caminhos reais:

```bash
/CAMINHO/DO/PHP /CAMINHO/REAL/DA/APLICACAO/artisan schedule:run
```

Exemplo ilustrativo:

```bash
/usr/bin/php /home/USUARIO/domains/DOMINIO/public_html/artisan schedule:run
```

O caminho do PHP pode variar. Use o resultado de `which php` ou a informação
fornecida pelo painel da Hostinger.

Depois de confirmar o funcionamento, a saída pode ser direcionada para um log:

```bash
/CAMINHO/DO/PHP /CAMINHO/REAL/DA/APLICACAO/artisan schedule:run >> /home/USUARIO/scheduler.log 2>&1
```

Não configure simultaneamente outro cron chamando `agenda:sync`, porque o
scheduler já fará isso a cada cinco minutos.

## 16. Verificar o scheduler

Pelo SSH:

```bash
php artisan schedule:list
```

Deve aparecer:

```text
*/5 * * * *  php artisan agenda:sync
```

Faça uma execução manual do scheduler:

```bash
php artisan schedule:run --verbose
```

Consulte depois:

```bash
tail -n 100 storage/logs/laravel.log
```

O comando usa `withoutOverlapping`, impedindo execuções simultâneas do mesmo
sincronizador.

## 17. Validar o webhook N8N

No EasyPanel/N8N, confirme:

- workflow **Maieutica - Notificar confirmação de agendamento** ativo;
- domínio público HTTPS configurado;
- path `maieutica/agendamentos/confirmado`;
- autenticação `Header Auth`;
- credencial com `Authorization: Bearer TOKEN`;
- node de validação sem referência a `$vars.MAIEUTICA_WEBHOOK_TOKEN`;
- nodes **Avisar paciente** e **Avisar profissional** com o token da instância
  Evolution;
- ambos usando a credencial correta `EvoGo Account`.

Respostas úteis:

- `404`: workflow inativo ou Production URL incorreta;
- `403`: Header Auth/token incorreto;
- `401` no node EvoGo: token da instância Evolution ou credencial EvoGo
  incorretos;
- `200`: webhook aceitou a requisição; confira também a execução completa no
  N8N.

## 18. Teste controlado em produção

Antes de confirmar um evento real:

1. Crie um evento específico de homologação no Google Calendar.
2. Use apenas números autorizados para o teste.
3. Execute `php artisan agenda:sync`.
4. Abra `/appointments`.
5. Confirme o evento de homologação.
6. Confira a execução no N8N.
7. Confirme a entrega ao paciente e ao profissional.
8. Verifique `storage/logs/laravel.log`.

Se for indispensável redirecionar temporariamente as duas notificações para um
único número controlado, configure:

```env
N8N_NOTIFICATION_TEST_PHONE=DDDNUMERO
```

Depois da homologação, obrigatoriamente restaure:

```env
N8N_NOTIFICATION_TEST_PHONE=
```

e execute:

```bash
php artisan config:clear
php artisan config:cache
```

Não deixe o telefone de teste ativo durante o uso normal da produção.

## 19. Confirmar que a aplicação está disponível

Se o script automático usa modo de manutenção, confirme nos logs que ele
executou `php artisan up`. Se a aplicação continuar em manutenção, execute:

```bash
php artisan up
```

Teste:

- login;
- menu **Agendamentos**;
- filtros da listagem;
- confirmação e recusa;
- visibilidade da recepção;
- visibilidade do profissional;
- recebimento das notificações;
- rota `/health`.

## 20. Monitoramento pós-publicação

Nas primeiras horas, monitore:

```bash
tail -f storage/logs/laravel.log
```

No N8N, acompanhe **Executions** e procure falhas nos nodes:

- `Webhook confirmacao`;
- `Validar e preparar mensagens`;
- `Avisar paciente`;
- `Avisar profissional`;
- `Responder ao Maieutica`.

No EasyPanel, monitore os logs dos serviços N8N e Evolution.

Também confirme periodicamente que o cron da Hostinger continua executando. Um
cron parado deixa a tela desatualizada mesmo que o Google Calendar e o N8N
estejam funcionando.

## 21. Problemas comuns

### A lista não recebe novos eventos

- execute `php artisan agenda:sync --dry-run`;
- confira a credencial JSON e o ID da agenda;
- confirme o compartilhamento do Calendar;
- confira o cron da Hostinger;
- limpe o cache de configuração.

### A lista mostra eventos antigos ou incorretos

- confirme se são registros locais de teste;
- execute a sincronização real;
- confira se o Calendar configurado é o mesmo usado pelo N8N;
- verifique a janela `GOOGLE_CALENDAR_SYNC_DAYS`.

### A confirmação é salva, mas o WhatsApp não chega

- abra a execução correspondente no N8N;
- `403` no webhook indica token Header Auth incorreto;
- `401` no EvoGo indica token da instância Evolution incorreto;
- confira os telefones cadastrados;
- confirme que a instância Evolution está conectada;
- confira se o modo de teste está vazio em produção.

### O cron informa sucesso, mas não sincroniza

Execute `agenda:sync --dry-run` manualmente. Sem as variáveis do Google, o
comando encerra com sucesso para evitar poluir os logs, mas informa que nada foi
sincronizado quando executado no terminal.

## 22. Segurança

- Chave JSON sempre fora de `public_html`.
- Arquivo da chave com permissão restrita.
- Nunca versionar `.env` ou credenciais.
- Nunca reutilizar o token do webhook como token da Evolution.
- HTTPS obrigatório entre Hostinger e N8N.
- Service Account com acesso somente leitura ao Calendar.
- Rotacionar imediatamente qualquer segredo exposto.
- Não registrar payloads completos com dados de pacientes nos logs.
- Manter `APP_DEBUG=false` em produção.
- Manter `N8N_NOTIFICATION_TEST_PHONE` vazio fora da homologação.

## 23. Checklist final

- [ ] Pull Request revisado e direcionado para a `main`.
- [ ] Merge na `main` concluído.
- [ ] Webhook GitHub -> Hostinger acionado.
- [ ] Deploy automático concluído sem erros.
- [ ] Commit publicado conferido no painel/log.
- [ ] Backup realizado.
- [ ] `composer install --no-dev --optimize-autoloader` confirmado no deploy.
- [ ] Chave JSON fora de `public_html`.
- [ ] Agenda compartilhada com a Service Account.
- [ ] Variáveis `GOOGLE_CALENDAR_*` configuradas.
- [ ] Variáveis `N8N_*` configuradas.
- [ ] `N8N_NOTIFICATION_TEST_PHONE` vazio.
- [ ] Migration de `appointments` executada.
- [ ] `RoleAndPermissionSeeder` executado.
- [ ] Caches limpos e recriados.
- [ ] `agenda:sync --dry-run` concluído.
- [ ] Primeira sincronização real concluída.
- [ ] Cron `schedule:run` ativo a cada minuto.
- [ ] Workflow N8N ativo.
- [ ] Header Auth validado.
- [ ] EvoGo validado.
- [ ] Teste controlado concluído.
- [ ] Aplicação retirada do modo de manutenção.
- [ ] Logs monitorados.

## 24. Documentos relacionados

- `docs/passo-a-passo-google-calender.md`
- `docs/passo-a-passo-n8n-confirmacao-agendamento.md`
- `docs/specs/agendamentos.md`
- `docs/MANUAL_ATUALIZACAO_PRODUCAO.md`
- `n8n/fluxo-confirmacao-agendamento.json`
