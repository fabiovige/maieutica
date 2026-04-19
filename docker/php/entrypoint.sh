#!/bin/bash
# Entrypoint idempotente para containers PHP do Maiêutica.
# - Setup completo (composer, key, migrate, seed) roda apenas quando RUN_SETUP=true (serviço `app`).
# - Demais serviços (queue, scheduler) apenas aguardam o DB e executam o CMD.

set -e

APP_DIR=/var/www/html
cd "$APP_DIR"

log() { echo "[entrypoint] $*"; }

wait_for_db() {
    log "Aguardando banco de dados em ${DB_HOST:-db}:${DB_PORT:-3306}..."
    until php -r "
        try {
            new PDO('mysql:host=${DB_HOST:-db};port=${DB_PORT:-3306};dbname=${DB_DATABASE:-maieutica}', '${DB_USERNAME:-maieutica}', '${DB_PASSWORD:-secret}');
            exit(0);
        } catch (Exception \$e) { exit(1); }
    " > /dev/null 2>&1; do
        sleep 2
    done
    log "Banco disponível."
}

# ── Setup completo (apenas no serviço app) ───────────────────────
if [ "${RUN_SETUP}" = "true" ]; then

    # 1. .env
    if [ ! -f .env ]; then
        if [ -f .env.docker ]; then
            cp .env.docker .env
            log ".env criado a partir de .env.docker"
        else
            cp .env.example .env
            log ".env criado a partir de .env.example"
        fi
    fi

    # 2. Dependências PHP
    if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then
        log "Instalando dependências PHP (composer install)..."
        composer install --no-interaction --prefer-dist --optimize-autoloader
    fi

    # 3. APP_KEY
    if ! grep -qE "^APP_KEY=base64:" .env; then
        log "Gerando APP_KEY..."
        php artisan key:generate --force
    fi

    # 4. Permissions em storage, bootstrap/cache e vendor
    log "Ajustando permissions de storage/, bootstrap/cache/ e vendor/..."
    chown -R www-data:www-data storage bootstrap/cache vendor 2>/dev/null || true
    chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

    # 5. Storage link
    if [ ! -L public/storage ]; then
        log "Criando symlink public/storage..."
        php artisan storage:link || true
    fi

    # 6. Esperar DB
    wait_for_db

    # 7. Migrate + seed (somente se banco estiver vazio)
    TABLE_COUNT=$(php -r "
        try {
            \$pdo = new PDO('mysql:host=${DB_HOST:-db};port=${DB_PORT:-3306};dbname=${DB_DATABASE:-maieutica}', '${DB_USERNAME:-maieutica}', '${DB_PASSWORD:-secret}');
            \$stmt = \$pdo->query('SHOW TABLES');
            echo count(\$stmt->fetchAll());
        } catch (Exception \$e) { echo 0; }
    ")

    if [ "$TABLE_COUNT" = "0" ]; then
        log "Banco vazio — rodando migrate + db:seed..."
        php artisan migrate --force
        php artisan db:seed --force
    else
        log "Banco já populado (${TABLE_COUNT} tabelas) — pulando migrate/seed."
    fi

    # 8. Clears de cache (dev)
    php artisan config:clear || true
    php artisan route:clear || true
    php artisan view:clear || true

    log "Setup concluído."
else
    # Serviços queue/scheduler — apenas aguardam DB
    wait_for_db
fi

exec "$@"
