# Passo a passo — Desistencia e encaixe manual via N8N

Este documento explica como configurar os dois workflows que complementam o
gerenciamento de agendamentos confirmados no Maieutica. Eles sao independentes
do fluxo principal e do fluxo de confirmacao existentes.

## 1. O que cada operacao faz

### Registrar desistencia

```text
Maieutica
  -> webhook appointment.cancelled
    -> N8N exclui o evento do Google Calendar
      -> responde google_event_cancelled=true
        -> Maieutica marca o agendamento como cancelado
        -> N8N continua e tenta avisar paciente e profissional
```

Arquivo: `n8n/fluxo-desistencia-agendamento.json`.

### Encaixar outro paciente

```text
Maieutica
  -> webhook appointment.replaced
    -> N8N atualiza o mesmo evento do Google Calendar
      -> responde google_event_updated=true
        -> Maieutica substitui os dados e grava o historico
        -> N8N continua e tenta avisar paciente anterior, novo paciente e profissional
```

Arquivo: `n8n/fluxo-encaixe-agendamento.json`.

O encaixe atualiza o evento existente. Ele nao apaga e recria o evento, assim o
horario nao fica temporariamente disponivel para outro agendamento.

## 2. Importar sem alterar os fluxos atuais

No N8N administrado pelo EasyPanel:

1. Use **Import from file**.
2. Importe `n8n/fluxo-desistencia-agendamento.json`.
3. Salve e mantenha inativo.
4. Volte a **Import from file**.
5. Importe `n8n/fluxo-encaixe-agendamento.json`.
6. Salve e mantenha inativo.

Nao edite `n8n/fluxo-atendimento.json` nem
`n8n/fluxo-confirmacao-agendamento.json` para fazer essa configuracao.

## 3. Configurar a autenticacao dos webhooks

Em cada node inicial (**Webhook desistencia** e **Webhook encaixe**):

1. Mantenha `Authentication: Header Auth`.
2. Selecione a mesma credencial **Maieutica Webhook Auth** usada no fluxo de
   confirmacao.
3. Confirme que a credencial possui:

```text
Name: Authorization
Value: Bearer TOKEN_GERADO
```

As Production URLs esperadas sao:

```text
https://DOMINIO-PUBLICO-N8N/webhook/maieutica/agendamentos/cancelado
https://DOMINIO-PUBLICO-N8N/webhook/maieutica/agendamentos/encaixe
```

## 4. Configurar o Google Calendar

No fluxo de desistencia, abra **Excluir evento do Calendar**. No fluxo de
encaixe, abra **Atualizar evento no Calendar**.

Nos dois nodes:

1. Selecione a credencial **Google Calendar account** ja usada pelo fluxo
   principal.
2. Confirme que a agenda selecionada e a mesma que recebe os eventos do
   WhatsApp.
3. Nao substitua as expressoes do campo `Event ID`: o Laravel envia o ID exato
   do evento a alterar.

O fluxo de desistencia precisa de permissao para excluir eventos. O fluxo de
encaixe precisa de permissao para atualizar titulo e descricao.

## 5. Configurar a Evolution API

Em todos os nodes de aviso:

1. Selecione a credencial **EvoGo Account**.
2. Substitua `CONFIGURE_O_TOKEN_DA_INSTANCIA_EVOLUTION` pelo token correto da
   instancia.
3. Salve o node.

Os envios de WhatsApp acontecem depois do node **Responder ao Maieutica** e
usam `continueOnFail`. A alteracao do Calendar e a etapa critica; uma falha
isolada de notificacao nao pode fazer o Laravel acreditar que o Calendar
permaneceu inalterado. Consulte **Executions** para identificar e reenviar
manualmente uma notificacao que falhar.

## 6. Configurar o Laravel

No `.env` local ou de producao, configure:

```env
N8N_APPOINTMENT_CANCELLED_WEBHOOK=https://DOMINIO-PUBLICO-N8N/webhook/maieutica/agendamentos/cancelado
N8N_APPOINTMENT_REPLACED_WEBHOOK=https://DOMINIO-PUBLICO-N8N/webhook/maieutica/agendamentos/encaixe
N8N_WEBHOOK_TOKEN=O_MESMO_TOKEN_DO_HEADER_AUTH
N8N_WEBHOOK_TIMEOUT=10
```

Nao coloque `Bearer` no valor do Laravel. O sistema adiciona esse prefixo.

Durante a homologacao, `N8N_NOTIFICATION_TEST_PHONE` redireciona todas as
mensagens desses fluxos para o telefone controlado ja configurado. Mantenha-o
conforme a estrategia de teste atual e deixe-o vazio em producao.

Depois de alterar o `.env`:

```bash
php artisan config:clear
```

No desenvolvimento local com Docker:

```bash
docker compose exec app php artisan config:clear
```

## 7. Ativar os workflows

Para cada workflow:

1. Salve todas as alteracoes.
2. Troque de **Inactive** para **Active**.
3. Recarregue a pagina e confirme que continua ativo.
4. Copie a Production URL e confira se ela coincide com o `.env` do Laravel.

## 8. Preparar o banco e as permissoes

Execute no ambiente correspondente:

```bash
php artisan migrate
php artisan db:seed --class=RoleAndPermissionSeeder
php artisan cache:clear
```

Em producao, use `--force` nas migrations e no seeder. O deploy automatico da
Hostinger deve executar esses comandos depois do merge na `main`.

## 9. Teste controlado de desistencia

1. Use um agendamento confirmado criado especificamente para teste.
2. Na lista, clique em **Registrar desistência**.
3. Informe um motivo opcional e confirme.
4. Confira no Google Calendar que o evento foi excluido.
5. Confira a execucao completa no N8N.
6. Confira as duas mensagens de WhatsApp.
7. Confira no Maieutica que a situacao mudou para **Cancelado**.

Se o N8N nao devolver `ok: true` e `google_event_cancelled: true`, o Maieutica
nao altera o registro local.

## 10. Teste controlado de encaixe

1. Use outro agendamento confirmado de teste.
2. Clique em **Encaixar paciente**.
3. Preencha nome, WhatsApp, profissional e os campos opcionais.
4. Confirme o encaixe.
5. Confira que o mesmo evento foi atualizado no Google Calendar.
6. Confira as tres tentativas de notificacao no N8N.
7. Confira que a lista mostra o novo paciente e continua como **Confirmado**.

Se o N8N nao devolver `ok: true` e `google_event_updated: true`, o Maieutica
nao substitui os dados locais.

## 11. Diagnostico rapido

- `404` no webhook: workflow inativo ou URL errada.
- `403` no webhook: credencial Header Auth ou token divergente.
- `401` no EvoGo: credencial ou token da instancia Evolution incorreto.
- erro no Calendar: confira credencial, agenda selecionada e Event ID.
- mensagem de webhook nao configurado: confira as duas novas variaveis no
  `.env` e limpe o cache de configuracao.
- Calendar alterado, mas Laravel informou falha: consulte a execucao do N8N e
  o node **Responder ao Maieutica** antes de repetir a operacao.

## 12. Seguranca e auditoria

- Nao grave tokens nos arquivos JSON, no Git ou na documentacao.
- Use HTTPS e Header Auth nos dois webhooks.
- Nao registre payloads completos nos logs; eles contem dados pessoais e de
  saude.
- O historico de desistencias e substituicoes fica em
  `appointment_replacements`.
- Os logs do Laravel registram somente IDs, horario e metadados operacionais.
- Cada formulario gera um `operation_id` unico. O mesmo valor segue no
  `event_id` e no header `X-Idempotency-Key`; repetir o mesmo formulario nao
  dispara novamente a operacao no Laravel.
- Nao habilite retry automatico nos nodes de WhatsApp enquanto o N8N nao
  possuir armazenamento persistente de chaves processadas. Uma resposta
  perdida depois do envio poderia gerar mensagem duplicada.
