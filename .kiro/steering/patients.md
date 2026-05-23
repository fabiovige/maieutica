---
inclusion: auto
---

# Modelo de Pacientes

## Tabela Única: `kids`

**Todos os pacientes — crianças e adultos — são armazenados na tabela `kids` e representados pelo model `Kid`.** Não existe tabela separada para adultos. A classificação é calculada dinamicamente a partir de `birth_date`.

---

## Classificações

### 1. Paciente Adulto (`is_adult = true`)

Idade >= **13 anos** (`Kid::ADULT_AGE_YEARS = 13`).

```php
$kid->is_adult   // true se TIMESTAMPDIFF(YEAR, birth_date, NOW()) >= 13
```

- Não participa de checklists Denver.
- Aparece na aba "Pacientes Adultos" na listagem de kids.
- Pode ter prontuários médicos (`MedicalRecord`).
- Pode ter documentos gerados (`GeneratedDocument`).

### 2. Criança (`is_adult = false`)

Idade < **13 anos**, ou `birth_date` nulo.

```php
$kid->is_adult   // false
```

Crianças se subdividem em dois grupos:

#### 2a. Elegível ao Denver (até 60 meses / 5 anos)

Idade <= **60 meses** — participam da avaliação cognitiva Denver.

```php
Kid::scopeDenverEligible()  // TIMESTAMPDIFF(MONTH, birth_date, CURDATE()) <= 60
Kid::getDenverEligibleKids() // helper que aplica denverEligible() + filtros de permissão
```

- Podem ter checklists criados.
- Aparecem no select de criação de checklist.
- São o foco principal da plataforma.

#### 2b. Criança não elegível ao Denver (entre 5 e 13 anos)

Idade entre **61 meses e 12 anos** — são crianças, mas **não participam** da avaliação Denver.

- Não aparecem no formulário de criação de checklist.
- Podem ter prontuários médicos e documentos gerados.
- Aparecem nas abas de crianças na listagem de kids.

---

## Resumo Visual

```
Todos os pacientes (tabela kids)
│
├── is_adult = false  →  Criança (birth_date < 13 anos ou nulo)
│   │
│   ├── age <= 60 meses  →  Denver Elegível  ✅ checklists, prontuários, documentos
│   │
│   └── age > 60 meses   →  Criança não Denver  ❌ checklists, ✅ prontuários, documentos
│
└── is_adult = true   →  Adulto (birth_date >= 13 anos)
                                 ❌ checklists, ✅ prontuários, documentos
```

---

## Scopes do Model `Kid`

```php
Kid::scopeAdults($query)
// WHERE TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= 13
// Retorna pacientes adultos

Kid::scopeChildren($query)
// WHERE birth_date IS NULL OR TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) < 13
// Retorna crianças (inclui birth_date nulo)

Kid::scopeDenverEligible($query)
// WHERE TIMESTAMPDIFF(MONTH, birth_date, CURDATE()) <= 60
// Retorna apenas elegíveis ao Denver (até 5 anos)

Kid::scopeForAuthProfessional($query)
// Filtra pelos kids vinculados ao profissional autenticado
```

---

## Helpers Estáticos

```php
// Retorna todos os kids visíveis para o usuário autenticado (respeita permissões)
Kid::getKids()

// Retorna apenas kids elegíveis ao Denver visíveis para o usuário autenticado
Kid::getDenverEligibleKids()
// Equivalente a: Kid::getKids(denverOnly: true)
```

`getKids()` aplica filtros de visibilidade por perfil:
- `kid-list-all` → todos os kids.
- `kid-list` (profissional) → kids vinculados ao profissional + kids que ele criou.
- Responsável → apenas kids onde `responsible_id = auth()->id()`.

---

## Accessors Úteis

```php
$kid->is_adult          // bool — calculado de birth_date em tempo real
$kid->age               // string — ex: '3a 2m', '8 meses', null se sem birth_date
$kid->months            // int — total de meses de vida
$kid->birth_date        // string d/m/Y (accessor formata automaticamente)
$kid->initials          // string — iniciais do nome (ex: 'JS'), usado em avatares e logs
$kid->full_name_months  // string — 'Cod. 5 - Nascido em: 12/03/2021 (38 meses)'
```

**Atenção com `birth_date`:** o accessor retorna `d/m/Y`. Para cálculos de data, usar `$kid->getRawOriginal('birth_date')` que retorna `Y-m-d`.

---

## Regras de Negócio Críticas

1. **Nunca criar tabela separada para adultos** — a classificação é dinâmica via `birth_date`.
2. **`is_adult` é calculado em tempo real** — não é um campo no banco. Não cachear nem persistir.
3. **Elegibilidade Denver = até 60 meses** — usar sempre `Kid::getDenverEligibleKids()` para popular selects de checklist. Nunca usar `Kid::getKids()` nesses contextos.
4. **`Kid::ADULT_AGE_YEARS = 13`** — usar a constante, nunca hardcodar o número 13.
5. **`birth_date` nulo = tratado como criança** — o scope `children()` inclui `birth_date IS NULL`.
6. **Ao deletar um Kid, seus checklists são deletados em cascata** — definido no `boot()` do model.
7. **Prontuários e documentos** são acessíveis para todos os tipos de paciente (crianças Denver, crianças não-Denver e adultos).
8. **`$kid->age`** retorna `null` se `birth_date` for nulo — sempre tratar o null antes de exibir.
