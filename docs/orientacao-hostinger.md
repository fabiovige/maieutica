# Publicação dos agendamentos na Hostinger

Guia enxuto para publicar e manter a integração de agendamentos do Maiêutica.
A Hostinger não usa Docker. Todos os comandos abaixo são executados diretamente
no servidor.

## 1. Caminhos usados em produção

```text
PHP:     /usr/bin/php
Projeto: /home/u350247040/domains/maieuticavalia.com.br/public_html
Artisan: /home/u350247040/domains/maieuticavalia.com.br/public_html/artisan
```

Para executar comandos manualmente:

```bash
cd /home/u350247040/domains/maieuticavalia.com.br/public_html
```

## 2. Publicação pelo GitHub

O deploy é iniciado automaticamente quando o Pull Request é mesclado na
`main`:

```text
merge na main -> webhook do GitHub -> deploy na Hostinger
```

Antes do merge, confirme que `.env`, credenciais JSON e tokens não estão no
commit. Também faça um backup do banco de produção.

O deploy deve executar:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --class=RoleAndPermissionSeeder --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Se migrations e seeder não fizerem parte do script automático, execute-os uma
vez por SSH após o deploy.

## 3. Migrations necessárias

Não execute migrations individualmente. O comando correto é:

```bash
php artisan migrate --force
```

Ele executa somente as migrations ainda pendentes. Para esta funcionalidade,
confirme que as seguintes aparecem como `Ran`:

```text
2026_08_18_170508_create_appointments_table
2026_08_20_120000_add_cancellation_fields_to_appointments_table
2026_08_20_120100_create_appointment_replacements_table
2026_08_20_130000_create_appointment_slots_table
2026_08_20_130100_add_operation_id_to_appointment_replacements_table
2026_08_20_130200_backfill_confirmed_appointment_slots
```

Verificação:

```bash
php artisan migrate:status
```

A última migration preenche os horários já confirmados. Por isso, faça backup
do banco antes da publicação e não execute rollback automaticamente se houver
falha.

## 4. Seeder de permissões

Execute somente o seeder de papéis e permissões:

```bash
php artisan db:seed --class=RoleAndPermissionSeeder --force
php artisan permission:cache-reset
```

Não execute `php artisan db:seed` sem informar a classe, pois o
`DatabaseSeeder` também chama seeders de usuários, profissionais, pacientes e
outros dados do sistema.

Permissões adicionadas para agendamentos:

- `appointment-list`: profissional visualiza os próprios confirmados;
- `appointment-list-all`: administração visualiza todos e os pendentes;
- `appointment-confirm`: confirma ou recusa;
- `appointment-cancel`: registra desistência;
- `appointment-replace`: realiza encaixe manual.

O `RoleAndPermissionSeeder` atribui todas elas ao papel `admin` e somente
`appointment-list` ao papel `profissional`.

## 5. Variáveis do `.env`

### Google Calendar

```env
GOOGLE_CALENDAR_ID=ID_REAL_DA_AGENDA
GOOGLE_CALENDAR_CREDENTIALS=/home/u350247040/secure/google-calendar.json
GOOGLE_CALENDAR_SYNC_DAYS=60
```

A credencial da Service Account deve ficar fora de `public_html`:

```bash
mkdir -p /home/u350247040/secure
chmod 700 /home/u350247040/secure
chmod 600 /home/u350247040/secure/google-calendar.json
```

A agenda utilizada pelo N8N deve estar compartilhada com o `client_email` da
Service Account, com permissão para ver todos os detalhes dos eventos.

### N8N

```env
N8N_APPOINTMENT_CONFIRMED_WEBHOOK=https://DOMINIO-N8N/webhook/maieutica/agendamentos/confirmado
N8N_APPOINTMENT_CANCELLED_WEBHOOK=https://DOMINIO-N8N/webhook/maieutica/agendamentos/cancelado
N8N_APPOINTMENT_REPLACED_WEBHOOK=https://DOMINIO-N8N/webhook/maieutica/agendamentos/encaixe
N8N_WEBHOOK_TOKEN=TOKEN_DO_HEADER_AUTH
N8N_WEBHOOK_TIMEOUT=10
N8N_NOTIFICATION_TEST_PHONE=TELEFONE_DE_HOMOLOGACAO
```

Use as URLs de produção `/webhook/`, nunca `/webhook-test/`. Durante a
homologação, mantenha o telefone de teste atualmente configurado. Remova-o
somente quando for decidido iniciar o envio para os números reais.

### Aplicação e fila

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://maieuticavalia.com.br
SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=database
```

Depois de alterar o `.env`:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 6. Qual fila executar

Os e-mails usam a fila `emails`. O sistema também possui jobs que usam a fila
`default`. Portanto, o worker deve ouvir as duas, nesta ordem:

```text
emails,default
```

Os agendamentos não usam a fila para sincronizar o Google Calendar. A
sincronização é executada diretamente pelo scheduler do Laravel.

## 7. Crons da Hostinger

São necessárias duas tarefas cron separadas.

### Cron 1 — scheduler do Laravel

Frequência: a cada minuto (`* * * * *`).

```bash
/usr/bin/php /home/u350247040/domains/maieuticavalia.com.br/public_html/artisan schedule:run >> /dev/null 2>&1
```

O scheduler chama `agenda:sync` a cada cinco minutos e impede duas execuções
simultâneas. Não crie outro cron chamando `agenda:sync` diretamente.

### Cron 2 — filas

Frequência: a cada minuto (`* * * * *`).

```bash
/usr/bin/php /home/u350247040/domains/maieuticavalia.com.br/public_html/artisan queue:work database --queue=emails,default --stop-when-empty --tries=3 --timeout=90 >> /dev/null 2>&1
```

O `--stop-when-empty` é importante quando `queue:work` é iniciado por cron:
ele processa o que estiver pendente e encerra, evitando acumular workers
permanentes a cada minuto.

Esse comando substitui a cron antiga que ouvia apenas `emails`:

```text
queue:work --queue=emails --verbose
```

Se a Hostinger estiver gerenciando um worker permanente em vez de uma tarefa
cron, não use `--stop-when-empty`; nesse caso, mantenha somente um processo com
`--queue=emails,default` e reinicie-o após cada deploy.

## 8. N8N e Evolution

Confirme que estes workflows estão importados, configurados e ativos:

- confirmação: `maieutica/agendamentos/confirmado`;
- desistência: `maieutica/agendamentos/cancelado`;
- encaixe: `maieutica/agendamentos/encaixe`.

Em cada workflow:

- selecione a credencial Header Auth com o mesmo `N8N_WEBHOOK_TOKEN`;
- selecione a credencial correta do Google Calendar quando houver esse node;
- selecione a credencial EvoGo e o token da instância Evolution;
- use a Production URL;
- ative o workflow depois de salvar as credenciais.

No workflow de confirmação, confira também o node **Adicionar profissional ao
Calendar**. Ele deve usar a agenda central `Atendimento - TESTE`, adicionar o
e-mail do profissional no modo `add` e enviar atualizações para todos os
convidados. O cadastro de cada profissional precisa ter um e-mail válido.

Erro `401` no EvoGo normalmente significa credencial ou token da Evolution
incorreto. Erro `403` no webhook indica Header Auth divergente.

## 9. Validação após o deploy

Execute, na ordem:

```bash
php artisan migrate:status
php artisan route:list --path=appointments
php artisan schedule:list
php artisan agenda:sync --dry-run
php artisan agenda:sync
php artisan queue:failed
```

Em seguida:

1. Abra `/appointments` e confirme que os eventos do Calendar aparecem.
2. Confirme um agendamento de teste.
3. Confira o convite na agenda/e-mail do profissional.
4. Confira as duas mensagens no WhatsApp e a execução no N8N.
5. Teste desistência e encaixe.
6. Confira `storage/logs/laravel.log`.

O resultado esperado de `schedule:list` contém:

```text
*/5 * * * *  php artisan agenda:sync
```

## 10. Diagnóstico rápido

Lista desatualizada:

```bash
php artisan agenda:sync --dry-run
php artisan schedule:list
```

Se o evento não vier no `--dry-run`, confira o ID e o compartilhamento da
agenda. Se vier, execute `php artisan agenda:sync` e recarregue a lista sem
filtros.

Mensagens não entregues:

- confira a execução do workflow no N8N;
- confira Header Auth e `N8N_WEBHOOK_TOKEN`;
- confira a credencial EvoGo e a conexão da Evolution;
- confira o telefone recebido no payload;
- consulte `storage/logs/laravel.log`.

Fila parada:

```bash
php artisan queue:failed
php artisan queue:retry all
```

Use `queue:retry all` somente depois de corrigir a causa, pois ele pode reenviar
e-mails ou outras notificações.

## Checklist final

- [ ] Backup do banco concluído.
- [ ] Merge na `main` e deploy automático concluídos.
- [ ] `php artisan migrate --force` executado.
- [ ] `RoleAndPermissionSeeder` executado.
- [ ] `.env` de produção configurado.
- [ ] Credencial Google fora de `public_html`.
- [ ] Agenda compartilhada com a Service Account.
- [ ] Três workflows N8N ativos.
- [ ] Cron `schedule:run` ativo a cada minuto.
- [ ] Cron da fila ouvindo `emails,default`.
- [ ] `agenda:sync --dry-run` concluído.
- [ ] Confirmação, desistência e encaixe testados.
- [ ] Logs do Laravel, N8N e Evolution verificados.

## Documentos relacionados

- `docs/passo-a-passo-google-calender.md`
- `docs/passo-a-passo-n8n-confirmacao-agendamento.md`
- `docs/passo-a-passo-n8n-desistencia-encaixe.md`
- `docs/MANUAL_ATUALIZACAO_PRODUCAO.md`
