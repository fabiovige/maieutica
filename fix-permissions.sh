#!/bin/bash

echo "🔧 Corrigindo permissões do Laravel no Docker..."

# Corrigir ownership dos diretórios críticos
docker compose exec app chown -R www-data:www-data /var/www/storage
docker compose exec app chown -R www-data:www-data /var/www/bootstrap/cache

# Definir permissões adequadas
docker compose exec app chmod -R 775 /var/www/storage
docker compose exec app chmod -R 775 /var/www/bootstrap/cache

# Limpar todos os caches para forçar recriação com permissões corretas
echo "🧹 Limpando caches..."
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear

# Recriar cache de configuração
echo "♻️ Recriando cache de configuração..."
docker compose exec app php artisan config:cache

echo "✅ Permissões corrigidas com sucesso!"