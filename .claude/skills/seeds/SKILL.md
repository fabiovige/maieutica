---
description: Seeders, ordem de execução, dados de teste, factories, comandos de banco
---

Leia `docs/seeds.md` na íntegra. Use-o para responder perguntas sobre seeders, dados de desenvolvimento, factories e população do banco.

## Comandos

```bash
php artisan db:seed                              # Popular banco (NÃO apaga dados)
php artisan db:seed --class=NomeDoSeeder         # Seeder específico
composer fresh                                    # migrate:fresh --seed (SOMENTE se pedido!)
```

**REGRA CRÍTICA:** Nunca rodar `migrate:fresh` sem solicitação explícita.

## Ordem de Execução (DatabaseSeeder)

1. **RoleAndPermissionSeeder** — 4 roles (admin, profissional, responsavel, paciente) + ~30 permissions
2. **UserSeeder** — 4 usuários (user01-04@gmail.com, senha: "password")
3. **SpecialtySeeder** — Especialidades médicas
4. **ProfessionalSeeder** — Profissionais vinculados
5. **ResponsibleSeeder** — Responsáveis legais
6. **KidSeeder** — 2 crianças (Ana Silva ~6a, Pedro Santos ~4a)
7. **DomainSeeder** — Domínios de desenvolvimento
8. **LevelSeeder** — Níveis desenvolvimentais
9. **CompetenceSeeder** — Competências por domínio/nível
10. **ChecklistSeeder** — 2 checklists por criança (notas aleatórias 1-3)
11. **ReleaseSeeder** — Notas de versão
12. ~~MedicalRecordSeeder~~ — *comentado*
13. ~~PlaneSeeder~~ — *comentado*

## Usuários de Teste

| Email | Senha | Role |
|-------|-------|------|
| user01@gmail.com | password | admin |
| user02@gmail.com | password | admin |
| user03@gmail.com | password | profissional |
| user04@gmail.com | password | responsavel |

## Factories

```php
Kid::factory()->create();
User::factory()->create();
Checklist::factory()->create(['kid_id' => $kid->id]);
```

Factories disponíveis para models principais — usados em seeders e testes.
