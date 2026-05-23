<?php

namespace App\Modules\Lgpd\Infrastructure\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class LgpdPermissionSeeder extends Seeder
{
    /**
     * Permissões do módulo LGPD.
     *
     * @var array<string, string>
     */
    private array $permissions = [
        'lgpd-consent-manage' => 'Criar e revogar consentimentos',
        'lgpd-consent-list' => 'Listar consentimentos',
        'lgpd-consent-show' => 'Visualizar detalhes de consentimento',
        'lgpd-access-log-view' => 'Visualizar logs de acesso',
        'lgpd-request-manage' => 'Criar e processar requisições de direitos',
        'lgpd-request-list' => 'Listar requisições de direitos',
        'lgpd-request-show' => 'Visualizar detalhes de requisição',
        'lgpd-report-generate' => 'Gerar relatório de conformidade',
        'lgpd-retention-manage' => 'Configurar políticas de retenção',
        'lgpd-retention-list' => 'Listar políticas de retenção',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web']
            );
        }
    }
}
