# Seeds — Dados de Desenvolvimento

> Referencia dos seeders para popular o banco de dados em ambiente de desenvolvimento.

---

## Comandos

```bash
# Popular banco (uso normal — NAO apaga dados existentes)
php artisan db:seed

# Fresh + seed (SOMENTE se pedido explicitamente)
composer fresh    # = migrate:fresh --seed

# Rodar seeder específico
php artisan db:seed --class=NomeDoSeeder
```

**REGRA CRITICA:** Nunca rodar `migrate:fresh` ou esvaziar o banco sem solicitacao explicita do usuario.

---

## Ordem de Execucao (`DatabaseSeeder.php`)

A ordem importa — seeders posteriores dependem dos anteriores.

| # | Seeder | Descricao | Dependencias |
|---|--------|-----------|-------------|
| 1 | RoleAndPermissionSeeder | 4 roles + ~30 permissions | Nenhuma |
| 2 | UserSeeder | 4 usuarios de teste | Roles |
| 3 | SpecialtySeeder | Especialidades medicas | Nenhuma |
| 4 | ProfessionalSeeder | Profissionais vinculados | Users, Specialties |
| 5 | ResponsibleSeeder | Responsaveis legais | Nenhuma |
| 6 | KidSeeder | 2 pacientes crianca | Responsibles |
| 7 | DomainSeeder | Dominios de desenvolvimento | Nenhuma |
| 8 | LevelSeeder | Niveis desenvolvimentais | Nenhuma |
| 9 | CompetenceSeeder | Competencias por dominio/nivel | Domains, Levels |
| 10 | ChecklistSeeder | 2 checklists por crianca | Kids, Competences |
| 11 | ReleaseSeeder | Notas de versao | Nenhuma |
| — | ~~MedicalRecordSeeder~~ | *(comentado)* | — |
| — | ~~PlaneSeeder~~ | *(comentado)* | — |

---

## Dados Criados

### Roles e Permissions (RoleAndPermissionSeeder)

**4 Roles:**
| Role | Descricao |
|------|-----------|
| admin | Acesso total |
| profissional | Profissional de saude |
| responsavel | Responsavel legal |
| paciente | Paciente (uso futuro) |

**~30+ Permissions:** Padrao `{entidade}-{acao}[-all]`
- `kid-list`, `kid-create`, `kid-edit`, `kid-delete`, `kid-list-all`, etc.
- `checklist-*`, `user-*`, `role-*`, `professional-*`, `medical-record-*`, etc.

### Usuarios (UserSeeder)

| Email | Role | Tipo |
|-------|------|------|
| user01@gmail.com | admin | Admin principal |
| user02@gmail.com | admin | Admin secundario |
| user03@gmail.com | profissional | Profissional |
| user04@gmail.com | responsavel | Responsavel |

**Senha padrao:** `password` (para todos)

### Pacientes (KidSeeder)

| Nome | Idade | Tipo |
|------|-------|------|
| Ana Silva | ~6 anos | Crianca |
| Pedro Santos | ~4 anos | Crianca |

**Nota:** Idades calculadas com `now()->subYears()` — mudam com o tempo.

### Checklists (ChecklistSeeder)

- 2 checklists por crianca (4 total)
- Notas aleatorias (1-3) para competencias
- Vinculados a dominios e niveis existentes

---

## Seeders Desabilitados

### MedicalRecordSeeder
- **Status:** Comentado no `DatabaseSeeder`
- **Motivo:** Prontuarios sao criados manualmente pelo profissional
- **Para habilitar:** Descomentar em `database/seeders/DatabaseSeeder.php`

### PlaneSeeder
- **Status:** Comentado no `DatabaseSeeder`
- **Motivo:** Planos dependem de contexto clinico especifico
- **Para habilitar:** Descomentar em `database/seeders/DatabaseSeeder.php`

---

## Factories (`database/factories/`)

Factories existem para os models principais e sao usados pelos seeders e testes.

**Uso em testes:**
```php
$kid = Kid::factory()->create();
$user = User::factory()->create();
$checklist = Checklist::factory()->create(['kid_id' => $kid->id]);
```

---

## Dicas de Desenvolvimento

- **Banco vazio?** Rode `php artisan db:seed` — nunca `migrate:fresh` sem pedir
- **Seeder especifico:** `php artisan db:seed --class=ChecklistSeeder`
- **Dados inconsistentes?** Verifique a ordem dos seeders (dependencias)
- **Novo seeder:** Adicione ao `DatabaseSeeder` na posicao correta
- **Testes:** Usam o banco real (`.env`) — seeders populam dados de teste
