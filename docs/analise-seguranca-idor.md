# Análise de Segurança: IDOR em URLs com IDs sequenciais

**Data:** 2026-06-23
**Contexto:** URL `/checklists/45/edit?kidId=75` expõe IDs numéricos sequenciais. A dúvida é se um usuário mal-intencionado pode manipular esses IDs para acessar ou editar dados de outros pacientes.

---

## O que é IDOR?

**IDOR (Insecure Direct Object Reference)** é uma vulnerabilidade da categoria [OWASP Top 10 (A01 - Broken Access Control)](https://owasp.org/Top10/A01_2021-Broken_Access_Control/).

Ocorre quando uma aplicação usa um identificador previsível (como um ID numérico na URL) para acessar um objeto, sem verificar se o usuário autenticado tem **permissão** para acessar aquele objeto específico.

**Exemplo de IDOR real:**
```
# Usuário A acessa seu próprio checklist
GET /checklists/45/edit   → ✅ autorizado

# Usuário A troca o ID manualmente para acessar checklist de outro paciente
GET /checklists/46/edit   → ❌ deveria ser bloqueado — mas em sistemas sem policy, não é
```

---

## Diagnóstico do Maiêutica

### O sistema está protegido? ✅ SIM

Toda rota sensível de checklist passa pelo `ChecklistPolicy` via `$this->authorize()`:

```php
// ChecklistController.php
public function edit($id)
{
    $this->authorize('update', Checklist::findOrFail($id)); // ← policy executada aqui
    ...
}

public function update(ChecklistRequest $request, $id)
{
    $checklist = Checklist::findOrFail($id);
    $this->authorize('update', $checklist); // ← policy executada aqui
    ...
}
```

### O que a policy verifica? (`ChecklistPolicy::update`)

```php
public function update(User $user, Checklist $checklist): bool
{
    // Admin com permissão global
    if ($user->can('checklist-edit-all')) return true;

    // Profissional que criou o checklist
    if ($user->can('checklist-edit') && $checklist->created_by === $user->id) return true;

    // Profissional vinculado à criança do checklist
    if ($user->can('checklist-edit')) {
        $professional = $user->professional->first();
        if ($professional && $checklist->kid->professionals->contains($professional->id)) return true;
    }

    return false; // ← qualquer outro caso: 403 Forbidden
}
```

**Conclusão:** Se um profissional tentar acessar `/checklists/99/edit` onde o checklist 99 pertence a uma criança que **não é paciente dele**, o Laravel lança `AuthorizationException` → resposta HTTP 403 Forbidden. O dado nunca é exibido.

### E o parâmetro `?kidId=75`?

Esse parâmetro é usado **apenas para navegação de retorno** após salvar (redireciona para a lista da criança). Não controla qual checklist é carregado — esse vem do segmento de rota `{id}`. Alterar `kidId` na URL não concede acesso a nenhum dado.

---

## IDs sequenciais vs. UUIDs — qual a diferença real?

| | IDs sequenciais (`/45`) | UUIDs (`/550e8400-e29b-41d4...`) |
|---|---|---|
| **Segurança real** | Igual, se houver policy correta | Igual, se houver policy correta |
| **Enumeração** | Fácil (`45`, `46`, `47`...) | Inviável (2¹²² combinações) |
| **Proteção sem policy** | Nenhuma | Obscuridade — não é segurança |
| **Performance** | Índice inteiro, mais rápido | Índice UUID, levemente mais lento |
| **Vazamento de volume** | Sim (dá para estimar qtd. de registros) | Não |

**Conclusão:** UUIDs dificultam a *descoberta* de IDs válidos, mas **não substituem uma policy de autorização**. Um sistema com UUIDs mas sem policy ainda é vulnerável. Um sistema com IDs sequenciais e policy bem implementada (como o Maiêutica) é seguro.

---

## Proteções adicionais que o Maiêutica já tem

1. **ChecklistPolicy** em todas as ações CRUD ✅
2. **AclMiddleware** — bloqueia usuários com flag `allow=false` antes de qualquer rota ✅
3. **Audit logging** — todas as ações são registradas por Observers + Domain Loggers ✅
4. **Scope de listagem** — a listagem já filtra por profissional vinculado, então IDs de outros pacientes nem aparecem na UI ✅
5. **Autenticação obrigatória** — rotas protegidas por middleware `auth` ✅

---

## Quando UUIDs fariam sentido no Maiêutica?

Considerar apenas se houver:
- **Links compartilháveis publicamente** (ex: relatório enviado por email com token de acesso temporário)
- **APIs públicas ou parceiros externos** onde esconder o volume de dados seja relevante
- **Novo projeto** onde a migração não tem custo

Para o sistema atual, **a relação custo/benefício não justifica a migração** — exigiria nova migration em todas as tabelas, reescrita de rotas e queries, e não aumentaria a segurança real dado que as policies já estão implementadas.

---

## Padrão de mercado recomendado (indústria)

O padrão definitivo contra IDOR, recomendado pela OWASP, é:

> **"Verificar autorização em cada acesso ao objeto, no servidor, independente de como o identificador chegou."**

Isso é exatamente o que `$this->authorize('update', $checklist)` faz. IDs na URL são **aceitáveis e comuns** em sistemas como GitHub, Linear, Jira, Notion — todos usam IDs numéricos ou slugs — desde que a autorização server-side esteja em vigor.

---

## Resumo

| Pergunta | Resposta |
|---|---|
| O sistema é vulnerável a IDOR? | **Não** — ChecklistPolicy bloqueia acessos não autorizados |
| IDs na URL são um problema? | **Não por si só** — problema seria ausência de policy, não o ID |
| Devo migrar para UUIDs? | **Não há necessidade** no estado atual do sistema |
| O que devo monitorar? | Picos de 403 nos logs podem indicar tentativa de enumeração |
