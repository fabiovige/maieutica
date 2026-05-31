#!/bin/bash
# Entrypoint idempotente para containers PHP do Maiêutica.
# - Setup completo (composer, key, migrate, seed) roda apenas quando RUN_SETUP=true (serviço `app`).
# - Demais serviços (queue, scheduler) apenas aguardam o DB e executam o CMD.
#
# Privilégios: o container inicia como root (necessário para o master do php-fpm
# e para ajustar permissions). TODO comando PHP de aplicação (composer/artisan,
# queue:work, schedule:run e o CMD final) é rebaixado para www-data via `gosu`,
# garantindo que qualquer arquivo criado pelo Laravel — inclusive o log diário
# storage/logs/laravel-YYYY-MM-DD.log — pertença a www-data, o mesmo usuário dos
# workers do php-fpm. Isso evita o conflito de permissão "could not be opened in
# append mode" quando um processo root cria o log do dia.

set -e

APP_DIR=/var/www/html
cd "$APP_DIR"

log() { echo "[entrypoint] $*"; }

# Executa um comando como www-data quando estamos como root; caso contrário,
# executa direto (container já iniciado sem privilégio).
as_web() {
    if [ "$(id -u)" = "0" ]; then
        gosu www-data "$@"
    else
        "$@"
    fi
}

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

    # 2. Permissions em storage, bootstrap/cache e vendor (como root, ANTES de
    #    qualquer artisan, para normalizar arquivos criados em execuções antigas).
    log "Ajustando permissions de storage/, bootstrap/cache/ e vendor/..."
    chown -R www-data:www-data storage bootstrap/cache vendor 2>/dev/null || true
    chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

    # 3. Dependências PHP (como www-data)
    if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then
        log "Instalando dependências PHP (composer install)..."
        as_web composer install --no-interaction --prefer-dist --optimize-autoloader
    fi

    # 4. APP_KEY (como www-data)
    if ! grep -qE "^APP_KEY=base64:" .env; then
        log "Gerando APP_KEY..."
        as_web php artisan key:generate --force
    fi

    # 5. Storage link (como www-data)
    if [ ! -L public/storage ]; then
        log "Criando symlink public/storage..."
        as_web php artisan storage:link || true
    fi

    # 6. Esperar DB
    wait_for_db

    # 7. Migrate + seed (somente se banco estiver vazio) — como www-data
    TABLE_COUNT=$(php -r "
        try {
            \$pdo = new PDO('mysql:host=${DB_HOST:-db};port=${DB_PORT:-3306};dbname=${DB_DATABASE:-maieutica}', '${DB_USERNAME:-maieutica}', '${DB_PASSWORD:-secret}');
            \$stmt = \$pdo->query('SHOW TABLES');
            echo count(\$stmt->fetchAll());
        } catch (Exception \$e) { echo 0; }
    ")

    if [ "$TABLE_COUNT" = "0" ]; then
        log "Banco vazio — rodando migrate + db:seed..."
        as_web php artisan migrate --force
        as_web php artisan db:seed --force
    else
        log "Banco já populado (${TABLE_COUNT} tabelas) — pulando migrate/seed."
    fi

    # 8. Clears de cache (dev) — como www-data
    as_web php artisan config:clear || true
    as_web php artisan route:clear || true
    as_web php artisan view:clear || true

    log "Setup concluído."
else
    # Serviços queue/scheduler — apenas aguardam DB
    wait_for_db
fi

# ── Exec do CMD ──────────────────────────────────────────────────
# php-fpm precisa do master como root (os workers caem para www-data via
# www.conf). Qualquer outro comando (queue:work, schedule:run, etc.) roda
# como www-data para que os arquivos criados tenham o dono correto.
if [ "$1" = "php-fpm" ]; then
    exec "$@"
elif [ "$(id -u)" = "0" ]; then
    exec gosu www-data "$@"
else
    exec "$@"
fi
