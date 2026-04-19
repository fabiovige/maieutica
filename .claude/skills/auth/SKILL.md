---
description: Autorização (can() NUNCA hasRole()), permissions, policies, regras Professional↔User
---

Leia `docs/PROFESSIONAL_USER_RELATIONSHIP.md` na íntegra. Use-o para responder perguntas sobre autorização e relacionamentos profissional/usuário.

## Regras de Autorização — INEGOCIÁVEIS

**SEMPRE `can()`, NUNCA `hasRole()` para autorização:**
```php
$user->can('entity-action')              // ✅ Controller/Service
@can('entity-action') ... @endcan        // ✅ Blade
$this->authorize('policyMethod', $model); // ✅ Policy
$user->hasRole('admin')                   // ❌ PROIBIDO para lógica de negócio
```

**Roles existem APENAS para atribuição** — agrupar permissions automaticamente:
```php
$user->assignRole('profissional');  // ✅ Único uso válido de roles
```

**Padrão de nomes:** `{entidade}-{ação}[-all]`
- Sem `-all` = acesso aos próprios/atribuídos
- Com `-all` = acesso a TODOS (admin)
- Ex: `kid-list`, `kid-list-all`, `medical-record-edit`, `medical-record-edit-all`

**4 Roles:** admin, profissional, responsavel, paciente

**10 Policies:** Checklist, Kid, MedicalRecord, GeneratedDocument, Plane, Professional, User, Role, Responsible, Competence

## Regras de Negócio Professional ↔ User

- Relação 1:1 via pivot `user_professional` (constraint UNIQUE em `user_id`)
- Sempre usar `$user->professional->first()` (nunca sem `->first()`)
- Verificar existência: `$user->professional->count() > 0`
- Role 'profissional' é PROTEGIDO — não pode ser alterado em users com professional vinculado
- Deletar User COM professional → ambos vão para lixeira (cascata)
- Deletar Professional → User permanece ativo (assimetria intencional)
- Desativar/ativar Professional → sincroniza `allow` do User vinculado
- Não permitir delete se professional tiver kids vinculados

## Scope de Visibilidade

- Profissionais veem apenas dados dos seus pacientes atribuídos (via `kid_professional`)
- Prontuários: `forAuthProfessional()` filtra por `created_by` do profissional autenticado
- Admin vê tudo (permissions com `-all`)
