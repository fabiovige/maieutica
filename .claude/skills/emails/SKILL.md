---
description: Mail classes, notifications, filas de email, templates, fluxos de comunicação
---

Leia `docs/emails.md` na íntegra. Use-o para responder perguntas sobre envio de emails, notificações, templates e filas de email.

## Classes Mail (`app/Mail/`)

| Classe | Evento | View | Assunto |
|--------|--------|------|---------|
| UserCreatedMail | Criação de usuário | `emails.user_created` | "Bem-vindo ao [app]" |
| UserUpdatedMail | Atualização de usuário | `emails.user_updated` | "Sua conta foi atualizada" |
| UserDeletedMail | Desativação de conta | `emails.user_deleted` | "Sua conta foi desativada" |

Todas usam `ShouldQueue` — envio assíncrono.

## Notifications (`app/Notifications/`)

- **WelcomeNotification** — boas-vindas com senha temporária (fila: `emails`)
- **KidUpdateNotification** — aviso de atualização de paciente ao admin

## Job

- **SendKidUpdateJob** — disparado por KidObserver, notifica admin (User ID 2 hardcoded)

## Fluxo por Evento

- **Criar usuário:** UserCreatedMail + WelcomeNotification + UserObserver log
- **Atualizar usuário:** UserUpdatedMail + UserObserver log
- **Desativar usuário:** UserDeletedMail + UserObserver log
- **Atualizar paciente:** KidObserver → SendKidUpdateJob → KidUpdateNotification

## Regras

- Sempre usar `ShouldQueue` — nunca enviar síncrono
- Templates em `resources/views/emails/`
- Dev: usar driver `log` (emails em `storage/logs/`)
- Monitorar `failed_jobs` — emails falhos ficam lá
- 7 registros em `failed_jobs` existentes — investigar

## Configuração

- Driver: `.env` (`MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`)
- Fila: `ShouldQueue` em todas as classes
- Templates: `resources/views/emails/` (Blade, pt-BR)
