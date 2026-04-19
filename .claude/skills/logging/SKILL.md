---
description: Duas camadas (Observer + Domain Logger), LGPD, armazenamento de logs
---

Leia `docs/logging.md` na íntegra. Use-o para responder perguntas sobre o sistema de logging.

## Arquitetura de Duas Camadas

**Camada 1 — Observer (automática, nível de modelo):**
Dispara nos eventos Eloquent (`created`, `updated`, `deleted`, `restored`).

| Observer | Modelo |
|----------|--------|
| `ChecklistObserver` | Checklist |
| `KidObserver` | Kid |
| `UserObserver` | User |
| `ProfessionalObserver` | Professional |
| `RoleObserver` | Role |
| `ResponsibleObserver` | Responsible |

**Camada 2 — Domain Logger (explícita, nível de negócio):**
Chamado nos controllers via injeção de dependência.

| Logger | Entidade |
|--------|----------|
| `ChecklistLogger` | Checklists |
| `KidLogger` | Kids |
| `UserLogger` | Usuários |
| `ProfessionalLogger` | Profissionais |
| `RoleLogger` | Roles/Perfis |
| `MedicalRecordLogger` | Prontuários |

Todos em `app/Services/Logging/`.

## Padrão de Uso

```php
class ExemploController extends Controller
{
    private ExemploLogger $logger;

    public function __construct(ExemploLogger $logger) {
        $this->logger = $logger;
    }

    public function store(Request $request) {
        // ... lógica ...
        $this->logger->created($model, ['source' => 'controller']);
    }
}
```

## LGPD — OBRIGATÓRIO

- Nomes de crianças: registrar como **iniciais** (ex: "T.R.M.M.")
- Campos sensíveis: mascarar com `[CHANGED]`
- Conteúdo de prontuários: NUNCA logar completo

## Armazenamento

- **Arquivo:** `storage/logs/laravel.log` (rotação diária, 60 dias)
- **Banco:** Tabela `logs` (operações CRUD de models)
- **Browser:** `/log-viewer` (requer autenticação)

## Ao Criar Novo Controller

Se o controller gerencia uma entidade com CRUD, criar um Domain Logger correspondente em `app/Services/Logging/` seguindo o padrão dos existentes. Injetar via construtor.
