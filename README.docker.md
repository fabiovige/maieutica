# Maiêutica — Docker (WSL2 + Debian)

Guia para rodar o projeto **100% em Docker** dentro do WSL2 (Debian).

> **Por que WSL2 e não Windows direto?** File system nativo do WSL2 é ~10× mais rápido que `/mnt/c/...` para bind-mounts. O projeto DEVE ficar em `~/projetos/` (dentro do WSL), nunca em `/mnt/c/`.

---

## 1. Pré-requisitos (no WSL2 Debian)

```bash
# Docker Engine + Compose plugin (nativo, não Docker Desktop)
sudo apt update
sudo apt install -y docker.io docker-compose-plugin git

# Permitir uso sem sudo
sudo usermod -aG docker $USER
newgrp docker

# Verificar
docker --version
docker compose version
```

## 2. Clonar o projeto (dentro do WSL2, NUNCA em /mnt/c/)

```bash
mkdir -p ~/projetos && cd ~/projetos
git clone <URL_DO_REPO> maieutica
cd maieutica
git checkout develop
```

## 3. Configurar `.env`

```bash
cp .env.docker .env
```

O `.env.docker` já vem com:
- `DB_HOST=db` (nome do serviço no compose)
- `APP_KEY` preenchida
- `QUEUE_CONNECTION=database`

Se quiser usar MailHog, adicione/ajuste no `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_FROM_ADDRESS=noreply@maieutica.local
MAIL_FROM_NAME="${APP_NAME}"
```

## 4. Subir os containers

```bash
# UID/GID do usuário WSL — evita problemas de permissão nos volumes
# (APP_UID/APP_GID porque UID é readonly em bash)
export APP_UID=$(id -u)
export APP_GID=$(id -g)

# Opcional: persistir no shell
echo 'export APP_UID=$(id -u)' >> ~/.bashrc
echo 'export APP_GID=$(id -g)' >> ~/.bashrc

# Build + up (primeira vez)
docker compose up -d --build

# Acompanhar setup inicial (composer install, migrate, seed)
docker compose logs -f app
```

O entrypoint do `app` roda automaticamente na primeira subida:
1. Cria `.env` se não existir
2. `composer install` se `vendor/` não existir
3. `php artisan key:generate` se `APP_KEY` vazia
4. Ajusta permissões de `storage/` e `bootstrap/cache/`
5. `php artisan storage:link`
6. Aguarda o DB ficar healthy
7. Se o banco estiver vazio: `migrate --force` + `db:seed --force`

## 5. Acessar

| Serviço        | URL                          |
|----------------|------------------------------|
| App Laravel    | http://localhost:8080        |
| MailHog (emails) | http://localhost:8025      |
| MariaDB (HeidiSQL/DBeaver) | `localhost:3306` · user `maieutica` · pass `secret` |
| Log Viewer     | http://localhost:8080/log-viewer |
| Health check   | http://localhost:8080/health |

## 6. Serviços do compose

| Serviço      | Descrição                                       |
|--------------|-------------------------------------------------|
| `app`        | PHP-FPM 8.2 — Laravel. Roda setup inicial.      |
| `nginx`      | Web server na porta 8080.                       |
| `db`         | MariaDB 10.11 com healthcheck.                  |
| `node`       | Node 18 — roda `npm run watch` (Laravel Mix).   |
| `queue`      | `php artisan queue:work` (processa jobs).       |
| `scheduler`  | `php artisan schedule:run` a cada minuto.       |
| `mailhog`    | Captura emails em dev (SMTP 1025 · UI 8025).    |

## 7. Comandos úteis

```bash
# Entrar no container PHP
docker compose exec app bash

# Rodar artisan
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan tinker

# Rodar composer
docker compose exec app composer install
docker compose exec app composer require <pacote>

# Rodar npm (no container node)
docker compose exec node npm install
docker compose exec node npm run production

# Rodar testes
docker compose exec app php artisan test

# Lint (Laravel Pint)
docker compose exec app ./vendor/bin/pint

# Limpar caches
docker compose exec app composer clear
```

## 8. Logs

```bash
docker compose logs -f app        # PHP
docker compose logs -f nginx      # Web server
docker compose logs -f queue      # Worker
docker compose logs -f node       # Build de assets
docker compose logs -f db         # MariaDB
docker compose logs -f mailhog    # Emails

# Laravel log
docker compose exec app tail -f storage/logs/laravel.log
```

## 9. Parar / Reiniciar

```bash
docker compose stop              # Para (preserva containers e volume)
docker compose start             # Sobe novamente
docker compose down              # Remove containers (preserva volume DB)
docker compose down -v           # Remove containers + volume DB (APAGA DADOS)
docker compose restart app       # Reinicia um serviço
```

## 10. Troubleshooting

### Porta 8080, 3306 ou 8025 em uso
Altere em `docker-compose.yml` a porta do lado do host (esquerda do `:`).

### Permissões em `storage/` após subir
```bash
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R ug+rwx storage bootstrap/cache
```

### `node_modules` com permissão errada
```bash
docker compose exec node chown -R node:node node_modules
```

### Recriar tudo do zero (PERDE DADOS DO BANCO)
```bash
docker compose down -v
docker compose up -d --build
```

### Rebuild da imagem após mudar Dockerfile
```bash
docker compose build --no-cache app
docker compose up -d
```

### Browser do Windows não abre `localhost:8080`
WSL2 faz forward automático. Se falhar, descobrir o IP do WSL:
```bash
ip addr show eth0 | grep inet
```
E acessar por esse IP.

### `mix` falha com permissão
O container `node` roda como root por padrão. Se precisar:
```bash
docker compose exec node sh -c "chown -R root:root node_modules"
```

---

## 11. Diferenças do ambiente Wamp (Windows)

| Aspecto         | Wamp Windows              | Docker WSL2               |
|-----------------|---------------------------|---------------------------|
| PHP             | 8.x do Wamp               | 8.2-fpm (paridade prod)   |
| DB              | MariaDB do Wamp           | MariaDB 10.11             |
| Node            | Instalado no host         | Container `node`          |
| Queue           | `sync` (sem worker)       | Worker dedicado           |
| Schedule        | Manual                    | Container `scheduler`     |
| Mail            | Sem captura               | MailHog em `:8025`        |
| URL             | http://maieutica.test     | http://localhost:8080     |
