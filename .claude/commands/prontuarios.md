Leia `docs/medical-records.md` na íntegra. Use-o para responder perguntas sobre prontuários.

## Modelo Polimórfico

**Tabela:** `medical_records` com `patient_type` + `patient_id` (morphMany)
- `patient_type = App\Models\Kid` — prontuário de paciente (criança ou adulto, ambos na tabela `kids`)
- Prontuários de adultos também usam `App\Models\Kid` como patient_type (todos na mesma tabela)

**Campos:** `session_date`, `complaint`, `history`, `observations`, `procedures`, `referral_closure`, `created_by`

## Versionamento (Self-Referencing)

- `parent_id` — referência ao prontuário original
- `version` — número da versão (incrementa a cada edição)
- `is_current_version` — boolean, marca a versão ativa
- Ao editar: cria nova versão, marca anterior como `is_current_version = false`

## Regras de Autorização — CRÍTICAS

**Profissionais:**
- **LISTAR:** apenas prontuários que **eles próprios criaram** (scope `forAuthProfessional` filtra por `created_by`)
- **CRIAR:** apenas para pacientes atribuídos a eles (via `kid_professional`)
- **EDITAR/DELETAR:** apenas prontuários que eles criaram (verificação de `created_by`)
- **VER (direto via URL):** a Policy `view()` permite acesso — avaliar restrição futura

**Admin:** acesso total a todos os prontuários

**Scope no Controller:**
```php
// Profissional vê apenas seus prontuários
MedicalRecord::forAuthProfessional()->get();
```

## Permissões

- `medical-record-list`, `medical-record-show`, `medical-record-create`, `medical-record-edit`, `medical-record-delete`
- `medical-record-list-all`, `medical-record-show-all`, `medical-record-edit-all`, `medical-record-delete-all` (admin)
- `medical-record-view-own` (paciente — apenas visualizar seus próprios)
- `medical-record-create-all` (admin — criar para qualquer paciente)

Para tipos de pacientes, consulte `/pacientes`. Para autorização geral, consulte `/auth`.
