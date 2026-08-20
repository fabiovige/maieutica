# Passo a passo — Google Calendar no Maiêutica

Este documento explica como configurar a sincronização de agendamentos entre o
Google Calendar e o Maiêutica.

O fluxo implementado é:

```text
WhatsApp -> N8N -> Google Calendar -> agenda:sync -> Maiêutica
```

O Google Calendar é a fonte da existência e dos horários dos eventos. O
Maiêutica mantém o estado de confirmação e o vínculo do agendamento com o
profissional cadastrado no sistema.

## 1. Pré-requisitos

- Acesso ao projeto no Google Cloud.
- Acesso de proprietário ou administrador à agenda usada pela clínica.
- Google Calendar API habilitada no projeto.
- Uma Service Account exclusiva para o Maiêutica.
- Acesso ao `.env` do ambiente que será configurado.

> Não use um arquivo OAuth do tipo `web`, que possui apenas `client_id` e
> `client_secret`. O sincronizador do Maiêutica exige uma chave JSON de
> **Service Account**, contendo `type: service_account`, `client_email` e
> `private_key`.

## 2. Ativar a Google Calendar API

1. Acesse o [Google Cloud Console](https://console.cloud.google.com/).
2. Selecione o projeto correto.
3. Abra **APIs e serviços > Biblioteca**.
4. Procure por **Google Calendar API**.
5. Clique em **Ativar**.

## 3. Criar a Service Account

1. No Google Cloud Console, abra **IAM e administrador > Contas de serviço**.
2. Clique em **Criar conta de serviço**.
3. Informe um nome, por exemplo, `maieutica-agendamento`.
4. Use uma descrição como `Leitura da agenda pelo sistema Maiêutica`.
5. Clique em **Criar e continuar**.
6. Não conceda papéis administrativos desnecessários ao projeto.
7. Clique em **Concluir**.

A conta criada terá um e-mail semelhante a:

```text
maieutica-agendamento@nome-do-projeto.iam.gserviceaccount.com
```

## 4. Criar a chave JSON

1. Abra a Service Account criada.
2. Entre na aba **Chaves**.
3. Clique em **Adicionar chave > Criar nova chave**.
4. Selecione **JSON**.
5. Clique em **Criar**.
6. Guarde o arquivo baixado em local seguro.

O arquivo correto possui uma estrutura semelhante a:

```json
{
  "type": "service_account",
  "project_id": "nome-do-projeto",
  "private_key_id": "...",
  "private_key": "-----BEGIN PRIVATE KEY-----...",
  "client_email": "maieutica-agendamento@nome-do-projeto.iam.gserviceaccount.com",
  "token_uri": "https://oauth2.googleapis.com/token"
}
```

Nunca envie o conteúdo desse arquivo em mensagens, não o coloque em
`public_html` e não o adicione ao Git. Se a chave for exposta, exclua-a no
Google Cloud e crie outra.

## 5. Compartilhar a agenda com a Service Account

1. Acesse o [Google Calendar](https://calendar.google.com/) pelo computador.
2. Em **Minhas agendas**, localize a agenda usada pelo N8N.
3. Passe o mouse sobre ela e clique nos três pontos.
4. Selecione **Configurações e compartilhamento**.
5. Localize **Compartilhar com pessoas ou grupos específicos**.
6. Clique em **Adicionar pessoas e grupos**.
7. Informe o `client_email` existente no arquivo JSON da Service Account.
8. Selecione **Ver todos os detalhes de eventos**.
9. Clique em **Enviar** ou **Compartilhar**.

O Maiêutica usa o escopo `calendar.readonly`; portanto, não é necessário dar
permissão para alterar ou excluir eventos.

## 6. Obter o ID da agenda

Na mesma página de configurações da agenda:

1. Localize a seção **Integrar agenda**.
2. Copie o valor de **ID da agenda**.

Em agendas compartilhadas, o valor normalmente termina com:

```text
@group.calendar.google.com
```

Não confunda o ID da agenda com o ID exclusivo da Service Account.

## 7. Configuração no ambiente local com Docker

O Docker é usado somente no desenvolvimento local.

Crie o diretório privado e coloque a credencial nele:

```text
storage/app/private/google-calendar.json
```

O diretório `storage` está ignorado pelo Git. Restrinja a leitura do arquivo:

```bash
chmod 600 storage/app/private/google-calendar.json
```

Configure o `.env` local:

```env
GOOGLE_CALENDAR_ID=ID_DA_AGENDA
GOOGLE_CALENDAR_CREDENTIALS=/var/www/html/storage/app/private/google-calendar.json
GOOGLE_CALENDAR_SYNC_DAYS=60
```

O caminho `/var/www/html` é o caminho do projeto dentro do contêiner e só deve
ser usado no ambiente local Docker.

Limpe o cache de configuração:

```bash
docker compose exec app php artisan config:clear
```

Teste sem gravar dados:

```bash
docker compose exec app php artisan agenda:sync --dry-run
```

Se o teste estiver correto, faça a sincronização real:

```bash
docker compose exec app php artisan agenda:sync
```

## 8. Configuração em produção na Hostinger

A Hostinger não usa os contêineres Docker deste projeto. Os comandos e caminhos
de produção são nativos da hospedagem.

1. Crie um diretório privado fora de `public_html`, por exemplo:

```text
/home/USUARIO/secure
```

2. Envie o JSON da Service Account para:

```text
/home/USUARIO/secure/google-calendar.json
```

3. Restrinja as permissões do arquivo, quando houver acesso SSH:

```bash
chmod 600 /home/USUARIO/secure/google-calendar.json
```

4. Configure o `.env` da aplicação:

```env
GOOGLE_CALENDAR_ID=ID_DA_AGENDA
GOOGLE_CALENDAR_CREDENTIALS=/home/USUARIO/secure/google-calendar.json
GOOGLE_CALENDAR_SYNC_DAYS=60
```

5. Limpe o cache de configuração:

```bash
php artisan config:clear
```

6. Teste a integração:

```bash
php artisan agenda:sync --dry-run
```

7. Execute a primeira sincronização real:

```bash
php artisan agenda:sync
```

O caminho exato do PHP e da aplicação depende da conta e da estrutura criada
pela Hostinger. Confirme esses caminhos no painel ou pelo terminal SSH.

## 9. Configurar a execução automática

O comando `agenda:sync` está registrado no scheduler do Laravel para execução a
cada cinco minutos. Em produção, o cron precisa chamar o scheduler a cada
minuto:

```bash
cd /home/USUARIO/CAMINHO_DA_APLICACAO && php artisan schedule:run
```

Configure a frequência do cron como:

```text
* * * * *
```

Se não for possível usar o scheduler geral, configure diretamente o comando:

```bash
cd /home/USUARIO/CAMINHO_DA_APLICACAO && php artisan agenda:sync
```

Nesse caso, use a frequência:

```text
*/5 * * * *
```

Não configure as duas alternativas simultaneamente, pois isso causaria
execuções duplicadas.

## 10. Como a sincronização funciona

Por padrão, cada execução consulta os eventos entre o início do dia atual e os
próximos 60 dias. O período é controlado por
`GOOGLE_CALENDAR_SYNC_DAYS`.

Para cada evento:

- evento novo: cria um agendamento pendente;
- evento já importado: atualiza os dados espelhados do Calendar;
- evento cancelado: marca o agendamento como cancelado;
- horário de evento confirmado alterado no Calendar: retorna o agendamento
  para pendente e exige nova confirmação;
- confirmação e profissional escolhido no Maiêutica não são sobrescritos por
  uma sincronização comum.

A correlação é feita pelo campo único `google_event_id`.

## 11. Resultado esperado

Uma execução válida apresenta uma saída semelhante a:

```text
Consultando eventos de 20/08/2026 a 19/10/2026...

Eventos recebidos:  7
Criados:            6
Atualizados:        0
Cancelados:         0
Reabertos:          0
Ignorados:          0
```

Depois da execução real, os eventos ficam disponíveis em:

```text
/appointments
```

Usuários com `appointment-list-all` veem toda a fila. Profissionais com
`appointment-list` veem apenas seus próprios agendamentos confirmados.

## 12. Solução de problemas

### “Integração com o Google Calendar não configurada”

Confira se as duas variáveis estão preenchidas:

```env
GOOGLE_CALENDAR_ID=
GOOGLE_CALENDAR_CREDENTIALS=
```

Depois execute `php artisan config:clear` no ambiente correspondente.

### Credencial não encontrada ou sem permissão de leitura

- Confirme que o caminho é absoluto.
- No Docker, confirme que o caminho existe dentro do contêiner.
- Na Hostinger, não use `/var/www/html`; use o caminho nativo da hospedagem.
- Confirme a permissão de leitura do usuário que executa o PHP.

### Erro 403 ao consultar o Calendar

- Confirme que a Google Calendar API está habilitada.
- Confirme que a agenda foi compartilhada com o `client_email` correto.
- Confirme a permissão **Ver todos os detalhes de eventos**.
- Confirme que o `GOOGLE_CALENDAR_ID` pertence à agenda compartilhada.

### O scheduler mostra `DONE`, mas nada é importado

Quando as variáveis não estão configuradas, o comando encerra sem erro para não
poluir os logs a cada cinco minutos. Execute manualmente
`agenda:sync --dry-run` para ver a mensagem completa.

### A tela mostra registros que não existem no Calendar

Confira se são registros locais de teste ou se o sincronizador estava sem
configuração. Registros locais não podem ser reconciliados enquanto o Google
Calendar não estiver acessível.

### Alguns eventos não aparecem

- Confirme se estão entre hoje e o limite de dias configurado.
- Confira se pertencem à agenda indicada por `GOOGLE_CALENDAR_ID`.
- Execute `agenda:sync --dry-run` e compare a quantidade recebida.
- Eventos sem horário de início utilizável são ignorados.

## 13. Segurança e rotação

- Nunca versionar o JSON da Service Account.
- Nunca armazenar a chave dentro de `public_html`.
- Nunca enviar `private_key` ou Client Secret em mensagens.
- Usar somente leitura no compartilhamento da agenda.
- Criar uma Service Account exclusiva para essa integração.
- Se houver exposição, excluir a chave em **Service Account > Chaves** e gerar
  uma nova.
- Depois da rotação, substituir o arquivo nos ambientes e limpar o cache de
  configuração do Laravel.

## 14. Arquivos relacionados

- `app/Console/Commands/SyncGoogleCalendarAppointments.php`
- `app/Services/Calendar/GoogleCalendarClient.php`
- `app/Services/Calendar/AppointmentMapper.php`
- `app/Console/Kernel.php`
- `config/services.php`
- `.env.example`
- `docs/specs/agendamentos.md`
