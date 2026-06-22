# Manual do Módulo LGPD — Guia para Funcionários

## O que é a LGPD?

A Lei Geral de Proteção de Dados (Lei nº 13.709/2018) garante aos pacientes e responsáveis o direito de saber como seus dados pessoais são tratados, solicitar acesso, correção ou exclusão, e revogar consentimentos. A clínica, como controladora dos dados, precisa documentar e responder a essas solicitações dentro de prazos legais.

O módulo LGPD da plataforma Maiêutica ajuda a clínica a cumprir essas obrigações de forma organizada.

---

## Como acessar

No menu lateral, clique em **LGPD** (ícone de cadeado). Você verá as opções disponíveis conforme suas permissões:

- Consentimentos
- Requisições
- Logs de Acesso
- Retenção de Dados
- Relatório

---

## 1. Consentimentos

### O que é?

Registro formal de que o paciente (ou responsável) autorizou o tratamento de seus dados para uma finalidade específica.

### Quando registrar?

- Quando o paciente assina um termo de consentimento (na primeira consulta, por exemplo)
- Quando o paciente autoriza compartilhamento de dados com escola, convênio, etc.
- Quando há uma nova finalidade de uso dos dados

### Como registrar um consentimento

1. Acesse **LGPD → Consentimentos**
2. No formulário "Registrar Consentimento":
   - **Titular:** Busque o paciente pelo nome
   - **Tipo:** Selecione "Criança/Paciente" ou "Responsável"
   - **Finalidade:** Descreva para que os dados serão usados (ex: "Acompanhamento terapêutico")
   - **Base Legal:** Selecione o fundamento jurídico (na maioria dos casos será "Consentimento do titular" ou "Tutela da saúde")
   - **Versão do Termo:** Número da versão do documento assinado (comece com 1)
3. Clique em **Registrar Consentimento**

### Como revogar um consentimento

Quando o paciente solicita a retirada do consentimento:

1. Acesse **LGPD → Consentimentos**
2. Encontre o consentimento na lista (use os filtros se necessário)
3. Clique no botão vermelho **Revogar** (ícone X)
4. Confirme a revogação

> **Importante:** A revogação não apaga o registro — ele fica marcado como "Revogado" para fins de histórico e auditoria.

### Bases legais disponíveis

| Base Legal | Quando usar |
|---|---|
| Consentimento do titular | Paciente/responsável assinou termo autorizando |
| Tutela da saúde | Tratamento necessário para proteção da saúde do paciente |
| Execução de contrato | Dados necessários para cumprir o contrato de prestação de serviço |
| Obrigação legal | Dados exigidos por lei ou regulamento (ex: notificação compulsória) |
| Legítimo interesse | Uso razoável dos dados que não prejudica o titular |
| Proteção da vida | Situação de emergência para proteger a vida |
| Exercício regular de direitos | Dados necessários para defesa em processo judicial |
| Estudos por órgão de pesquisa | Pesquisa acadêmica com anonimização |

---

## 2. Requisições de Direitos

### O que é?

Quando um paciente ou responsável entra em contato exercendo seus direitos previstos na LGPD (pedir acesso aos dados, correção, exclusão, etc.), a clínica tem **15 dias úteis** para responder.

### Tipos de requisição

| Tipo | O que o titular está pedindo |
|---|---|
| Acesso aos dados | "Quero saber quais dados vocês têm sobre mim/meu filho" |
| Retificação | "Quero corrigir um dado que está errado" |
| Eliminação | "Quero que apaguem meus dados" |
| Portabilidade | "Quero levar meus dados para outra clínica" |
| Revogação | "Quero retirar meu consentimento" |

### Como registrar uma requisição

1. Acesse **LGPD → Requisições**
2. No formulário "Nova Requisição de Direitos":
   - **Tipo:** Selecione o que o titular está pedindo
   - **Nome do Solicitante:** Nome completo de quem está pedindo
   - **CPF:** CPF do solicitante (com pontos e traço)
   - **Meio de Contato:** E-mail ou telefone para resposta
3. Clique em **Registrar Requisição**

O sistema calcula automaticamente o prazo de 15 dias úteis.

### Como processar uma requisição

1. Acesse **LGPD → Requisições**
2. Clique na requisição com status "Aberta"
3. Clique em **Assumir Requisição** — o status muda para "Em andamento"
4. Realize o que foi solicitado (compilar dados, corrigir informação, etc.)
5. Volte à requisição e preencha:
   - **Resposta ao Titular:** Descreva o que foi feito
   - **Justificativa de Retenção** (apenas para eliminação): Se não puder apagar tudo, explique por quê (ex: "Prontuários devem ser mantidos por 20 anos conforme CFM")
6. Clique em **Concluir Requisição**

### Status das requisições

| Status | Significado | Cor |
|---|---|---|
| Aberta | Aguardando alguém assumir | Azul |
| Em andamento | Alguém está processando | Amarelo |
| Concluída | Respondida ao titular | Verde |
| Vencida | Prazo de 15 dias úteis expirou sem resposta | Vermelho |

### Alertas automáticos

- Com **5 dias úteis** restantes, o sistema gera um alerta
- Se o prazo **expirar**, o sistema marca como "Vencida" automaticamente

> **Atenção:** Requisições vencidas representam descumprimento da LGPD. Priorize sempre as requisições com prazo próximo do vencimento.

---

## 3. Logs de Acesso

### O que é?

Registro automático de todos os acessos a prontuários médicos. Você **não precisa fazer nada** — o sistema registra sozinho.

### O que é registrado automaticamente?

- Quem acessou (nome do profissional)
- Qual prontuário foi acessado
- Quando (data e hora exata)
- O que fez (visualizou, baixou PDF, editou, excluiu, restaurou)
- De onde (endereço IP)

### Para que serve?

- Demonstrar em auditoria que a clínica controla quem acessa dados sensíveis
- Investigar acessos indevidos
- Responder ao titular quando ele pergunta "quem viu meus dados?"

### Como consultar

1. Acesse **LGPD → Logs de Acesso**
2. Use os filtros para encontrar o que precisa:
   - Por operador (quem acessou)
   - Por prontuário (ID)
   - Por período (data inicial e final)
   - Por tipo de operação

> **Nota:** Estes registros são imutáveis — não podem ser editados ou excluídos por ninguém.

---

## 4. Retenção de Dados

### O que é?

Configuração de quanto tempo cada tipo de dado deve ser mantido antes de ser revisado ou anonimizado.

### Prazos legais mínimos

| Categoria | Prazo mínimo | Fundamento |
|---|---|---|
| Prontuários | 20 anos | CFM Resolução 1.821/2007 |
| Consentimentos | 5 anos | LGPD Art. 16 |
| Logs de acesso | 5 anos | Marco Civil da Internet |
| Dados cadastrais | 5 anos | Código Civil Art. 206 |

> **Importante:** O sistema não permite configurar um prazo menor que o mínimo legal. Se tentar, será rejeitado.

### Quem configura?

Apenas o administrador da clínica. Na prática, configure uma vez e o sistema monitora automaticamente.

---

## 5. Relatório de Conformidade

### O que é?

Documento PDF que consolida o estado de conformidade da clínica com a LGPD em um período. Útil para auditorias, reuniões de compliance ou demonstrar adequação à lei.

### O que contém?

- Total de consentimentos ativos
- Requisições de direitos por status
- Total de acessos a prontuários
- Tempo médio de resposta às requisições
- Políticas de retenção configuradas

### Como gerar

1. Acesse **LGPD → Relatório**
2. Selecione a **Data Inicial** e a **Data Final** (máximo 365 dias)
3. Clique em **Gerar Relatório PDF**
4. O PDF será baixado automaticamente

---

## Perguntas Frequentes

**P: Preciso registrar consentimento de todos os pacientes antigos?**
R: Idealmente sim, mas pode ser feito gradualmente. Priorize novos pacientes e renove o consentimento dos antigos conforme retornam às consultas.

**P: O que faço se um paciente pedir para apagar todos os dados?**
R: Registre uma requisição de "Eliminação". Na resposta, informe que prontuários médicos devem ser mantidos por 20 anos (CFM). Dados cadastrais e fotos podem ser eliminados. Use o campo "Justificativa de Retenção" para documentar.

**P: Quem pode ver os logs de acesso?**
R: Apenas usuários com a permissão específica de visualização de logs LGPD.

**P: O que acontece se uma requisição vencer?**
R: O sistema marca como "Vencida" automaticamente. Isso indica descumprimento do prazo legal. Processe a requisição o mais rápido possível e documente o motivo do atraso.

**P: Preciso gerar o relatório todo mês?**
R: Não há obrigação de periodicidade. Gere quando precisar para auditorias ou revisões internas. Uma boa prática é gerar trimestralmente.

**P: O consentimento precisa ser renovado?**
R: Não automaticamente. Mas se a clínica mudar a finalidade do tratamento ou publicar uma nova versão do termo, é recomendável coletar novo consentimento.

---

## Resumo de Responsabilidades

| Ação | Quem faz | Quando |
|---|---|---|
| Registrar consentimento | Recepção/Profissional | Ao coletar assinatura do termo |
| Revogar consentimento | Profissional/Admin | Quando titular solicita |
| Criar requisição de direitos | Recepção/Admin | Quando titular entra em contato |
| Processar requisição | Profissional/Admin | Dentro de 15 dias úteis |
| Consultar logs de acesso | Admin/DPO | Quando necessário (auditoria, investigação) |
| Configurar retenção | Admin | Uma vez (e revisar anualmente) |
| Gerar relatório | Admin/DPO | Sob demanda (auditorias, revisões) |
