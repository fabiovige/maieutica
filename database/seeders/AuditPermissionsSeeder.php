<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuditPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view-audit-logs' => 'Visualizar logs de auditoria',
            'view-own-audit-logs' => 'Visualizar próprios logs de auditoria',
            'export-audit-logs' => 'Exportar logs de auditoria',
            'view-audit-stats' => 'Visualizar estatísticas de auditoria',
            'delete-audit-logs' => 'Excluir logs de auditoria',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        $superAdminRole = Role::where('name', 'SuperAdmin')->first();
        $adminRole = Role::where('name', 'Admin')->first();

        if ($superAdminRole) {
            $superAdminRole->givePermissionTo(array_keys($permissions));
        }

        if ($adminRole) {
            $adminRole->givePermissionTo([
                'view-audit-logs',
                'export-audit-logs',
                'view-audit-stats',
            ]);
        }

        $professionalRole = Role::where('name', 'Professional')->first();
        if ($professionalRole) {
            $professionalRole->givePermissionTo([
                'view-own-audit-logs',
            ]);
        }
    }
}
