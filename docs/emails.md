# Emails e Notificacoes

> Referencia do sistema de comunicacao por email e notificacoes do Maieutica.

---

## Visao Geral

O sistema envia emails em 2 cenarios:
1. **Lifecycle do usuario** — criacao, atualizacao, desativacao (via Mail classes)
2. **Atualizacao de paciente** — notificacao ao admin (via Job + Notification)

Todos os envios usam `ShouldQueue` — processamento assincrono via fila.

---

## Classes Mail (`app/Mail/`)

### UserCreatedMail

**Trigger:** Criacao de novo usuario  
**Destinatario:** O usuario criado  
**Dados:** `$user`, `$password` (opcional — senha temporaria)  
**View:** `resources/views/emails/user_created.blade.php`  
**Assunto:** "Bem-vindo ao [nome do app]"

```php
Mail::to($user)->send(new UserCreatedMail($user, $temporaryPassword));
```

### UserUpdatedMail

**Trigger:** Atualizacao de dados do usuario  
**Destinatario:** O usuario atualizado  
**Dados:** `$user`  
**View:** `resources/views/emails/user_updated.blade.php`  
**Assunto:** "Sua conta foi atualizada"

```php
Mail::to($user)->send(new UserUpdatedMail($user));
```

### UserDeletedMail

**Trigger:** Desativacao de conta  
**Destinatario:** O usuario desativado  
**Dados:** `$user`  
**View:** `resources/views/emails/user_deleted.blade.php`  
**Assunto:** "Sua conta foi desativada"

```php
Mail::to($user)->send(new UserDeletedMail($user));
```

---

## Notifications (`app/Notifications/`)

### WelcomeNotification

**Canal:** Mail (fila: `emails`)  
**Trigger:** Criacao de usuario  
**Destinatario:** O novo usuario  
**Conteudo:**
- Saudacao: "Ola [nome]"
- Mensagem de conta criada
- Senha temporaria
- Aviso para trocar senha
- Link de login

```php
$user->notify(new WelcomeNotification($user, $password));
```

### KidUpdateNotification

**Canal:** Mail  
**Trigger:** `SendKidUpdateJob` (disparado por Observer)  
**Destinatario:** Admin com ID 2 (hardcoded)  
**Conteudo:** "Crianca atualizada com sucesso. [nome]"

---

## Job de Notificacao

### SendKidUpdateJob (`app/Jobs/SendKidUpdateJob.php`)

**Fluxo:**
```
Kid atualizado -> KidObserver -> SendKidUpdateJob::dispatch($kid) -> KidUpdateNotification -> Email
```

**Nota:** Destinatario hardcoded (User ID 2). Deveria ser configuravel.

---

## Configuracao

### Driver de Email (`config/mail.php`)
- Configurado via `.env`: `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, etc.
- Producao: SMTP configurado
- Desenvolvimento: `log` driver (emails gravados em `storage/logs/`)

### Fila (`config/queue.php`)
- Todas as classes usam `ShouldQueue`
- WelcomeNotification usa fila nomeada: `emails`
- Demais usam fila `default`
- **Nota:** 7 registros em `failed_jobs` — verificar antes de usar workers

### Templates
- **Localizacao:** `resources/views/emails/`
- **Formato:** Blade templates
- **Layout:** Usa layout padrao do Laravel Notifications (ou customizado)

---

## Fluxo Completo por Evento

### Criacao de Usuario
```
UserController@store
  -> UserCreatedMail (email de boas-vindas)
  -> WelcomeNotification (notificacao com senha)
  -> UserObserver (log de criacao)
```

### Atualizacao de Usuario
```
UserController@update
  -> UserUpdatedMail (email de aviso)
  -> UserObserver (log de atualizacao)
```

### Desativacao de Usuario
```
UserController@destroy (soft delete)
  -> UserDeletedMail (email de aviso)
  -> UserObserver (log de desativacao)
```

### Atualizacao de Paciente
```
KidController@update
  -> KidObserver
     -> SendKidUpdateJob::dispatch($kid)
        -> KidUpdateNotification -> Email ao admin
     -> KidLogger (log de atualizacao)
```

---

## Regras

- **Nunca enviar email sincrono** — sempre usar `ShouldQueue`
- **Nunca enviar dados sensiveis** — senha temporaria apenas no email de criacao
- **Testar com driver `log`** — verificar em `storage/logs/laravel.log`
- **Monitorar `failed_jobs`** — emails que falharam ficam la
- **Templates em pt-BR** — manter consistencia de idioma
