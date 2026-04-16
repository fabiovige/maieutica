# Sistema de Logging — Maiêutica

> Atualizado: 2026-04-13

## Arquitetura: Duas Camadas

Todos os loggers seguem o mesmo padrão de duas camadas:

### Camada 1 — Observer (nível de modelo)

Dispara automaticamente nos eventos Eloquent (`created`, `updated`, `deleted`, `restored`).

| Observer | Modelo | Localização |
|----------|--------|-------------|
| `ChecklistObserver` | `Checklist` | `app/Observers/ChecklistObserver.php` |
| `KidObserver` | `Kid` | `app/Observers/KidObserver.php` |
| `UserObserver` | `User` | `app/Observers/UserObserver.php` |
| `ProfessionalObserver` | `Professional` | `app/Observers/ProfessionalObserver.php` |
| `RoleObserver` | `Role` | `app/Observers/RoleObserver.php` |
| `ResponsibleObserver` | `Responsible` | `app/Observers/ResponsibleObserver.php` |

### Camada 2 — Domain Logger (nível de negócio)

Chamado explicitamente nos controllers para registrar contexto de negócio.

| Logger | Entidade | Localização |
|--------|----------|-------------|
| `ChecklistLogger` | Checklists | `app/Services/Logging/ChecklistLogger.php` |
| `KidLogger` | Kids | `app/Services/Logging/KidLogger.php` |
| `UserLogger` | Usuários | `app/Services/Logging/UserLogger.php` |
| `ProfessionalLogger` | Profissionais | `app/Services/Logging/ProfessionalLogger.php` |
| `RoleLogger` | Roles/Perfis | `app/Services/Logging/RoleLogger.php` |
| `MedicalRecordLogger` | Prontuários | `app/Services/Logging/MedicalRecordLogger.php` |

## Padrão de Uso no Controller

```php
class KidsController extends Controller
{
    private KidLogger $kidLogger;

    public function __construct(KidLogger $kidLogger) {
        $this->kidLogger = $kidLogger;
    }

    public function store(Request $request) {
        // ... lógica de criação ...
        $this->kidLogger->created($kid, ['source' => 'controller']);
    }
}
```

## LGPD

- Nomes de crianças são registrados como **iniciais** nos logs (ex: "T.R.M.M.")
- Campos sensíveis são mascarados com `[CHANGED]`
- Nunca logar conteúdo completo de prontuários

## Armazenamento

- **Arquivo:** `storage/logs/laravel.log` (rotação diária, 60 dias)
- **Banco:** Tabela `logs` (operações CRUD de models)
- **Browser:** `/log-viewer` (requer autenticação)
