# Servicos — Camada de Logica de Negocio

> Referencia dos Services, Helpers, Enums e Jobs do sistema Maieutica.

---

## Services (`app/Services/`)

### ChecklistService

**Arquivo:** `app/Services/ChecklistService.php`

**Metodo principal:** `percentualDesenvolvimento($checklistId, $withTrashed = false)`

**Logica:**
1. Itera todos os dominios do sistema
2. Para cada dominio, busca competencias associadas
3. Filtra avaliacoes do checklist (nota != 0 = testado)
4. Conta itens validos (nota == 3 = "Consistente")
5. Calcula: `(itens_validos / itens_testados) * 100`

**Retorno:** `float` (0-100) ou `0` se nenhum item testado

**Escala de Notas:**
| Nota | Significado |
|------|-------------|
| 0 | Nao testado |
| 1 | Nao consegue |
| 2 | Inconsistente |
| 3 | Consistente (valido) |

**Uso:** Barras de progresso, graficos, overview de checklists

---

### OverviewService

**Arquivo:** `app/Services/OverviewService.php`

**Metodo principal:** `getOverviewData($kidId, $levelId = null, $checklistId = null)`

**Logica complexa:**
1. Carrega dados da crianca (Kid)
2. Calcula idade em meses (`birth_date` -> `diffInMonths`)
3. Para cada dominio:
   - Conta competencias totais, testadas e validas
   - Calcula percentual por dominio
   - Calcula media das notas (escala 0-3)
4. Calcula percentual global
5. Determina idade desenvolvimental vs cronologica
6. Identifica atraso em meses
7. Lista areas fracas (<=50% e testadas)

**Retorno (array):**
```php
[
    'kid'              => Kid,           // Objeto do paciente
    'ageInMonths'      => int,           // Idade cronologica em meses
    'domains'          => array,         // Dados por dominio
    'totalPercentage'  => float,         // % global de desenvolvimento
    'developmentalAge' => int,           // Idade desenvolvimental em meses
    'delay'            => int,           // Atraso em meses
    'weakAreas'        => array,         // Dominios com <= 50%
    'checklists'       => Collection,    // Checklists disponiveis
    'levels'           => Collection,    // Niveis disponiveis
]
```

**Uso:** Pagina de overview do paciente, graficos radar, relatorios

**Metodos privados:**
- `getDomainsByLevel($levelId)` — filtra dominios por nivel
- `prepareDomainData($domains, $checklist, $levelId)` — agrega avaliacoes

---

### Domain Loggers (`app/Services/Logging/`)

6 loggers especializados por entidade. Documentados em `/logging`.

| Logger | Entidade | Acoes registradas |
|--------|----------|-------------------|
| ChecklistLogger | Checklist | create, update, delete, restore, fill, clone |
| KidLogger | Kid | create, update, delete, restore, photo |
| UserLogger | User | create, update, delete, restore, allow |
| ProfessionalLogger | Professional | create, update, delete, activate, deactivate, assign |
| RoleLogger | Role | create, update, delete, permission_sync |
| MedicalRecordLogger | MedicalRecord | create, update, delete, restore |

### Database Logger (`app/Services/Log/`)

Custom Monolog handler que grava logs na tabela `logs`. Documentado em `/logging`.

---

## Helpers (`app/helpers.php`)

Funcoes globais autoloaded via Composer.

### `label_case(string $text): string`
Converte snake_case/kebab-case para Title Case.
```php
label_case('first_name');      // "First Name"
label_case('birth-date');      // "Birth Date"
```

### `get_progress_color(float $percentage): string`
Retorna cor hex para percentual de progresso.
```php
get_progress_color(0);    // "#dc3545" (vermelho)
get_progress_color(50);   // "#ffc107" (amarelo)
get_progress_color(100);  // "#28a745" (verde)
```

### `get_progress_gradient(float $percentage): string`
Retorna gradiente CSS horizontal para barras de progresso.
```php
get_progress_gradient(75);
// "linear-gradient(to right, #17a2b8, #28a745)"
```

### `get_chart_gradient(float $percentage): string`
Retorna gradiente CSS vertical (180deg) para graficos.
```php
get_chart_gradient(50);
// "linear-gradient(180deg, #ffc107, #fd7e14)"
```

---

## Enums (`app/Enums/`)

### ProgressColors (`app/Enums/ProgressColors.php`)

Enum com 11 cases mapeando percentuais a cores.

**Paleta:**
| Percentual | Cor | Hex |
|------------|-----|-----|
| 0% | Vermelho escuro | `#dc3545` |
| 10% | Vermelho claro | `#e74c3c` |
| 20% | Laranja | `#fd7e14` |
| 30% | Laranja claro | `#f39c12` |
| 40% | Amarelo escuro | `#e2c800` |
| 50% | Amarelo | `#ffc107` |
| 60% | Ciano | `#17a2b8` |
| 70% | Azul claro | `#20c997` |
| 80% | Verde claro | `#27ae60` |
| 90% | Verde | `#2ecc71` |
| 100% | Verde escuro | `#28a745` |

**Metodos estaticos:**
- `getColorForPercentage($pct)` — arredonda para dezena, retorna hex
- `getGradientForPercentage($pct)` — gradiente horizontal entre 2 cores adjacentes
- `getGradientForChart($pct)` — gradiente vertical (180deg)

---

## Jobs (`app/Jobs/`)

### SendKidUpdateJob

**Arquivo:** `app/Jobs/SendKidUpdateJob.php`

**Funcao:** Notifica admin quando dados de paciente sao atualizados.

**Comportamento:**
1. Recebe modelo `Kid` no construtor
2. No `handle()`, busca usuario com ID 2 (admin hardcoded)
3. Envia `KidUpdateNotification` via mail

**Fila:** Usa `ShouldQueue` — processamento em background

**Nota:** User ID hardcoded (ID 2) — deveria usar configuracao

---

## Notifications (`app/Notifications/`)

### KidUpdateNotification

**Canal:** Mail  
**Trigger:** `SendKidUpdateJob`  
**Conteudo:** "Crianca atualizada com sucesso. [nome]"

### WelcomeNotification

**Canal:** Mail (fila `emails`)  
**Trigger:** Criacao de usuario  
**Conteudo:** Mensagem de boas-vindas com senha temporaria e link de login

---

## Mail (`app/Mail/`)

3 classes Mailable, todas com `ShouldQueue`:

| Classe | Evento | View | Assunto |
|--------|--------|------|---------|
| UserCreatedMail | Criacao de usuario | `emails.user_created` | "Bem-vindo ao [app]" |
| UserUpdatedMail | Atualizacao de usuario | `emails.user_updated` | "Sua conta foi atualizada" |
| UserDeletedMail | Desativacao de usuario | `emails.user_deleted` | "Sua conta foi desativada" |

---

## Diagrama de Dependencias

```
Controller
  -> Service (logica de negocio)
     -> Model + Relationships (dados)
     -> Helpers/Enums (formatacao)
  -> Observer (side effects)
     -> Domain Logger (registro)
     -> Job (notificacao async)
        -> Notification (envio)
  -> Mail (comunicacao)
```
