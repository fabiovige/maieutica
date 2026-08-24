# Passo a passo — Confirmação de agendamento via N8N e WhatsApp

Este documento explica como configurar o fluxo que recebe a confirmação de um
agendamento no Maiêutica e envia mensagens de WhatsApp ao paciente e ao
profissional por meio do N8N e da Evolution API.

## 1. Arquitetura

Os serviços estão separados da seguinte forma:

```text
Hostinger
└── Maiêutica / Laravel
    └── POST HTTPS para o webhook público do N8N

EasyPanel
├── N8N
│   ├── recebe a confirmação
│   └── chama a Evolution API
├── Evolution API
│   └── envia as mensagens pelo WhatsApp
└── PostgreSQL
    └── persistência usada pelo N8N
```

O Maiêutica está fora da rede interna do EasyPanel. Por isso, ele deve usar o
domínio público HTTPS do N8N, nunca endereços internos como `http://n8n:5678`.

## 2. Responsabilidades

- Google Calendar: existência e horário original do evento.
- Maiêutica: confirmação clínica e vínculo com o profissional real.
- N8N: adiciona o profissional como convidado no evento e orquestra as notificações.
- Evolution API: envio das mensagens ao WhatsApp.

A confirmação é salva no Maiêutica antes da chamada ao N8N. Uma indisponibilidade
do webhook não desfaz a confirmação clínica; nesse caso, a tela avisa que será
necessário notificar os envolvidos manualmente.

## 3. Workflow utilizado

O arquivo importável está em:

```text
n8n/fluxo-confirmacao-agendamento.json
```

Esse workflow é independente do fluxo principal de atendimento:

```text
n8n/fluxo-atendimento.json
```

O fluxo principal não deve ser alterado durante a configuração do webhook de
confirmação.

O novo workflow contém:

```text
Webhook confirmacao
  -> Validar e preparar mensagens
    -> Adicionar profissional ao Calendar
      -> Avisar paciente
        -> Avisar profissional
          -> Responder ao Maieutica
```

## 4. Importar o workflow

1. Entre no N8N administrado pelo EasyPanel.
2. Escolha **Import from file**.
3. Selecione `n8n/fluxo-confirmacao-agendamento.json`.
4. Salve o workflow.
5. Mantenha-o inativo durante a configuração inicial.

Se uma versão anterior já estiver ativa, desative-a antes de importar ou
substituir o fluxo. Configure as credenciais no arquivo atualizado e mantenha
somente um workflow ativo para o path `maieutica/agendamentos/confirmado`.

## 5. Gerar o token do webhook

Gere um valor aleatório em um terminal seguro:

```bash
openssl rand -hex 32
```

O mesmo valor será usado em dois lugares:

1. Credencial Header Auth do N8N.
2. Variável `N8N_WEBHOOK_TOKEN` do Maiêutica.

Não coloque esse token no Git, em documentação ou em mensagens.

## 6. Criar a credencial Header Auth no N8N

Como essa instalação do N8N não disponibiliza Variables pela interface, a
autenticação usa uma credencial nativa do tipo **Header Auth**.

1. No N8N, abra **Credentials**.
2. Crie uma credencial do tipo **Header Auth**.
3. Configure:

```text
Name: Authorization
Value: Bearer TOKEN_GERADO
```

É obrigatório manter o espaço entre `Bearer` e o token.

4. Salve a credencial com um nome descritivo, por exemplo:

```text
Maieutica Webhook Auth
```

## 7. Configurar o node do webhook

Abra o node **Webhook confirmacao** e confira:

```text
Method: POST
Path: maieutica/agendamentos/confirmado
Authentication: Header Auth
Credential: Maieutica Webhook Auth
Response: Using Respond to Webhook node
```

O endereço de produção terá formato semelhante a:

```text
https://DOMINIO-PUBLICO-N8N/webhook/maieutica/agendamentos/confirmado
```

Use a **Production URL**, não a URL `/webhook-test/`.

## 8. Configurar a validação do payload

O node **Validar e preparar mensagens** deve começar com:

```javascript
const payload = $json.body || {};
```

Ele não deve consultar `$vars.MAIEUTICA_WEBHOOK_TOKEN`, porque a autenticação é
feita pelo próprio node Webhook através da credencial Header Auth.

Esse node:

- aceita somente o evento `appointment.confirmed`;
- exige identificador local, `google_event_id` e horário do agendamento;
- exige nomes do paciente e profissional;
- exige um e-mail válido no cadastro do profissional;
- valida e normaliza os telefones brasileiros;
- formata data e hora em `America/Sao_Paulo`;
- prepara uma mensagem para o paciente;
- prepara outra mensagem para o profissional.

### 8.1. Configurar o convite do Google Calendar

Abra o node **Adicionar profissional ao Calendar** e selecione a mesma
credencial **Google Calendar account** usada pelos outros workflows.

O node atualiza o evento existente pelo `google_event_id`, mantém eventuais
convidados já cadastrados e adiciona o e-mail do profissional. A opção **Send
Updates: All** faz o Google enviar o convite. O evento continua pertencendo à
agenda central `Atendimento - TESTE`; não é criada uma cópia independente.

O e-mail do usuário vinculado ao profissional precisa ser uma conta capaz de
receber convites do Google Calendar. Conforme a configuração pessoal dessa
conta, o compromisso pode aparecer automaticamente ou após a aceitação.

Os workflows de desistência e encaixe também mantêm esse convite consistente:
a desistência cancela o evento para os convidados e o encaixe substitui o
convidado pelo profissional selecionado.

## 9. Configurar a Evolution API

Abra os nodes:

- **Avisar paciente**;
- **Avisar profissional**.

Nos dois nodes:

1. Selecione a credencial existente **EvoGo Account**.
2. Preencha **Instance API Key** com o token da instância Evolution usada pelo
   fluxo principal.
3. Não deixe o placeholder abaixo:

```text
CONFIGURE_O_TOKEN_DA_INSTANCIA_EVOLUTION
```

O PostgreSQL e o fluxo principal não precisam ser alterados para essa etapa.

## 10. Configurar o domínio no EasyPanel

No serviço N8N do EasyPanel:

1. Confirme que há um domínio público configurado.
2. Confirme que HTTPS está ativo e com certificado válido.
3. Verifique se `WEBHOOK_URL` aponta para o domínio público do N8N, quando essa
   variável fizer parte da implantação.
4. Depois de alterar configurações de ambiente, salve e faça o redeploy.

O Laravel hospedado na Hostinger precisa conseguir acessar esse domínio pela
internet.

## 11. Configurar o Laravel

No `.env` do Maiêutica:

```env
N8N_APPOINTMENT_CONFIRMED_WEBHOOK=https://DOMINIO-PUBLICO-N8N/webhook/maieutica/agendamentos/confirmado
N8N_WEBHOOK_TOKEN=TOKEN_GERADO
N8N_WEBHOOK_TIMEOUT=10
# SOMENTE HOMOLOGACAO: redireciona as duas mensagens para um numero controlado
N8N_NOTIFICATION_TEST_PHONE=
```

Regras importantes:

- usar a URL real em texto puro;
- não usar colchetes ou sintaxe Markdown;
- não usar o domínio de exemplo;
- não adicionar barra extra depois de `confirmado`;
- usar o mesmo token da credencial Header Auth;
- nunca enviar o token como parâmetro da URL.

### Ambiente local Docker

```bash
docker compose exec app php artisan config:clear
```

### Produção Hostinger

A Hostinger não usa os contêineres Docker do projeto:

```bash
php artisan config:clear
```

Se houver cache de configuração em produção, ele precisa ser limpo depois de
qualquer alteração no `.env`.

## 12. Ativar o workflow

Depois de configurar todos os nodes:

1. Salve o workflow.
2. Use o botão no canto superior direito para mudar de **Inactive** para
   **Active**.
3. Confirme que o workflow permanece ativo depois de recarregar a página.

A Production URL só é registrada enquanto o workflow está ativo.

## 13. Testes seguros realizados

Antes de enviar qualquer WhatsApp real, foram realizados dois testes com o
evento inválido `configuration.test`.

### Token correto

Resultado observado:

```text
HTTP 200
```

Isso confirmou:

- domínio público acessível;
- HTTPS funcionando;
- Production URL registrada;
- token correto aceito pelo Header Auth.

O evento inválido não passa pelo node de validação e não chega aos nodes de
WhatsApp. Dependendo da configuração do N8N, o webhook pode devolver `200`
mesmo quando a execução registra internamente o erro esperado de evento
inválido.

### Token incorreto

Resultado observado:

```text
HTTP 403
```

Isso confirmou que requisições sem a credencial correta são bloqueadas antes
da execução do workflow.

## 14. Significado dos códigos de resposta

### HTTP 404

Mensagem comum:

```text
The requested webhook is not registered.
```

Causas prováveis:

- workflow inativo;
- Production URL incorreta;
- uso de `/webhook-test/` sem o modo de teste ativo;
- alterações ainda não salvas no N8N.

### HTTP 403

Causas prováveis:

- `N8N_WEBHOOK_TOKEN` diferente do Header Auth;
- ausência do prefixo `Bearer `;
- credencial errada selecionada no node Webhook.

Uma requisição propositalmente feita com token incorreto deve retornar `403`.

### HTTP 200

Indica que o N8N aceitou a chamada HTTP. Para um evento real, verifique também
a execução no N8N e o retorno dos dois nodes EvoGo.

### Timeout ou erro de conexão

- confirme o domínio público e o HTTPS;
- confira o status do serviço N8N no EasyPanel;
- confirme que a Hostinger consegue acessar o domínio;
- verifique `N8N_WEBHOOK_TIMEOUT`;
- consulte os logs e executions do N8N.

## 15. Teste real controlado

O primeiro teste real envia mensagens. Use somente números autorizados e
controlados.

1. Garanta que o paciente do evento tenha um telefone válido.
2. Garanta que o usuário vinculado ao profissional tenha um telefone válido.
3. Abra `/appointments` no Maiêutica.
4. Escolha um agendamento pendente de teste controlado.
5. Selecione o profissional.
6. Clique em confirmar.
7. Abra **Executions** no N8N.
8. Confirme a execução dos nodes:
   - `Webhook confirmacao`;
   - `Validar e preparar mensagens`;
   - `Adicionar profissional ao Calendar`;
   - `Avisar paciente`;
   - `Avisar profissional`;
   - `Responder ao Maieutica`.
9. Confirme o recebimento das duas mensagens.

Não use dados de pacientes reais no primeiro teste da integração.

Para direcionar as duas mensagens ao mesmo número controlado durante a
homologação, preencha temporariamente no `.env` local:

```env
N8N_NOTIFICATION_TEST_PHONE=DDDNUMERO
```

Com essa opção preenchida, o payload substitui tanto o telefone do paciente
quanto o do profissional, sem modificar seus cadastros. Depois do teste, deixe
a variável vazia e execute `php artisan config:clear`. Essa opção deve sempre
permanecer vazia em produção.

## 16. Payload enviado pelo Maiêutica

O evento possui estrutura semelhante a:

```json
{
  "event": "appointment.confirmed",
  "event_id": "appointment.confirmed.123.0000000000",
  "occurred_at": "2026-08-20T12:00:00-03:00",
  "appointment": {
    "id": 123,
    "google_event_id": "identificador-do-evento",
    "starts_at": "2026-08-21T10:00:00-03:00",
    "ends_at": "2026-08-21T10:50:00-03:00",
    "patient": {
      "name": "Nome do paciente",
      "phone": "11999999999",
      "email": "paciente@example.com"
    },
    "professional": {
      "id": 10,
      "name": "Nome do profissional",
      "phone": "11988888888",
      "email": "profissional@example.com",
      "specialty": "Especialidade"
    },
    "confirmed_by": {
      "id": 1,
      "name": "Usuário da recepção"
    },
    "confirmed_at": "2026-08-20T12:00:00-03:00"
  }
}
```

Os exemplos acima são fictícios.

## 17. Segurança

- Manter o webhook protegido por Header Auth.
- Usar HTTPS obrigatoriamente.
- Não colocar tokens no JSON exportado, no Git ou em documentação.
- Não registrar o payload completo nos logs, pois contém dados pessoais e de
  saúde.
- Rotacionar imediatamente qualquer token exposto.
- Restringir o acesso ao workflow e às credenciais no N8N.
- Usar números controlados nos testes.
- Manter o N8N e a Evolution API atualizados.

## 18. Arquivos relacionados

- `n8n/fluxo-confirmacao-agendamento.json`
- `n8n/fluxo-atendimento.json`
- `app/Services/Integrations/N8nAppointmentNotifier.php`
- `app/Http/Controllers/AppointmentController.php`
- `config/services.php`
- `.env.example`
- `docs/specs/agendamentos.md`
- `docs/passo-a-passo-google-calender.md`
