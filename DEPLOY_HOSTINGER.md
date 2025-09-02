# 📦 Deploy Maiêutica - Hostinger Production

## 🔄 Processo de Deploy Automático via Webhook

Quando você atualiza a branch `main`, o webhook é acionado automaticamente. Este guia documenta o processo e comandos necessários para garantir que tudo funcione corretamente.

## 📋 Pré-requisitos

- Acesso SSH à Hostinger
- Composer instalado
- Node.js e NPM instalados
- MySQL configurado
- `.env` de produção já configurado

## 🚀 Comandos de Deploy

### 1. Atualização Inicial (executar via SSH após push para main)

```bash
# Acessar o diretório do projeto
cd /home/seu_usuario/domains/maieuticavaliacom.br/public_html

# Instalar dependências PHP
composer install --optimize-autoloader --no-dev

# Instalar dependências Node
npm ci --production

# Compilar assets para produção
npm run production

# Limpar caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Gerar cache otimizado
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Executar migrations
php artisan migrate --force

# Executar seeders de permissões (IMPORTANTE!)
php artisan db:seed --class=UpdatePermissionsSeeder --force
```

### 2. Criar Migration para Atualizar Permissões

Crie este arquivo antes do deploy: `database/migrations/2025_09_02_000000_update_permissions_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UpdatePermissionsTable extends Migration
{
    public function up()
    {
        // Resetar cache de permissões
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Criar novas permissões se não existirem
        $permissions = [
            'manage all resources',
            'view kids',
            'create kids',
            'edit kids',
            'delete kids',
            'view checklists',
            'create checklists',
            'edit checklists',
            'delete checklists',
            'generate reports',
            'view professionals',
            'manage professionals',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Atualizar role Admin
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Atualizar role Professional
        $professionalRole = Role::firstOrCreate(['name' => 'Professional']);
        $professionalRole->givePermissionTo([
            'view kids',
            'create kids',
            'edit kids',
            'view checklists',
            'create checklists',
            'edit checklists',
            'generate reports',
        ]);

        // Atualizar role Responsible
        $responsibleRole = Role::firstOrCreate(['name' => 'Responsible']);
        $responsibleRole->givePermissionTo([
            'view kids',
            'view checklists',
        ]);
    }

    public function down()
    {
        // Reverter se necessário
    }
}
```

### 3. Criar Seeder para Permissões

Crie este arquivo: `database/seeders/UpdatePermissionsSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UpdatePermissionsSeeder extends Seeder
{
    public function run()
    {
        // Resetar cache de permissões
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Mesma lógica da migration para garantir consistência
        $permissions = [
            'manage all resources',
            'view kids',
            'create kids',
            'edit kids',
            'delete kids',
            'view checklists',
            'create checklists',
            'edit checklists',
            'delete checklists',
            'generate reports',
            'view professionals',
            'manage professionals',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Atualizar roles
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->syncPermissions(Permission::all());

        $professionalRole = Role::firstOrCreate(['name' => 'Professional']);
        $professionalRole->syncPermissions([
            'view kids',
            'create kids',
            'edit kids',
            'view checklists',
            'create checklists',
            'edit checklists',
            'generate reports',
        ]);

        $responsibleRole = Role::firstOrCreate(['name' => 'Responsible']);
        $responsibleRole->syncPermissions([
            'view kids',
            'view checklists',
        ]);
    }
}
```

## 🔧 Configurações de Permissões de Arquivos

```bash
# Ajustar permissões das pastas storage e bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Garantir que o usuário web seja o proprietário
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

## 📝 Script de Deploy Automático

Crie um arquivo `deploy.sh` no servidor:

```bash
#!/bin/bash

# Diretório do projeto
PROJECT_DIR="/home/seu_usuario/domains/maieuticavaliacom.br/public_html"

cd $PROJECT_DIR

# Pull das alterações
git pull origin main

# Instalar dependências
composer install --optimize-autoloader --no-dev
npm ci --production

# Compilar assets
npm run production

# Laravel optimizations
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart

# Atualizar permissões
php artisan db:seed --class=UpdatePermissionsSeeder --force

# Ajustar permissões de arquivos
chmod -R 775 storage
chmod -R 775 bootstrap/cache

echo "Deploy concluído com sucesso!"
```

Torne o script executável:
```bash
chmod +x deploy.sh
```

## 🔍 Verificações Pós-Deploy

1. **Testar Login**
   - Admin
   - Professional
   - Responsible

2. **Verificar Componentes Vue**
   - Dashboard com toggle de visualização (cards/tabela)
   - Gráficos funcionando
   - Formulários de endereço

3. **Testar Geração de PDF**
   - Acessar página de overview
   - Gerar relatório PDF

4. **Verificar Logs**
   ```bash
   tail -f storage/logs/laravel-$(date +%Y-%m-%d).log
   ```

## ⚠️ Troubleshooting

### Erro de Permissão em Logs
```bash
# Corrigir proprietário
chown -R www-data:www-data storage/logs
chmod -R 775 storage/logs
```

### Erro 500 após deploy
```bash
# Limpar todos os caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Recriar caches
php artisan config:cache
php artisan route:cache
```

### Assets não carregando
```bash
# Recompilar assets
npm run production

# Verificar mix-manifest.json
cat public/mix-manifest.json
```

## 📊 Monitoramento

- Verificar logs de erro regularmente
- Monitorar uso de disco em `storage/`
- Verificar performance das queries no MySQL
- Acompanhar logs de acesso do Apache/Nginx

## 🔐 Segurança

- Manter `.env` sempre fora do controle de versão
- Usar HTTPS em produção
- Configurar CORS adequadamente
- Manter dependências atualizadas

## 📌 Notas Importantes

1. **Sempre faça backup do banco antes de rodar migrations**
2. **Teste localmente antes de fazer push para main**
3. **Mantenha o `.env` de produção seguro e atualizado**
4. **Monitore os logs após cada deploy**

## 🆘 Suporte

Em caso de problemas:
1. Verificar logs em `storage/logs/`
2. Verificar logs do servidor web
3. Testar comandos individualmente via SSH
4. Rollback via Git se necessário

---

**Última atualização:** 02/09/2025
**Versão:** 1.0.0