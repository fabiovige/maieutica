<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run()
    {
        // Criação de Roles
        $superAdmin = Role::firstOrCreate(['name' => 'superadmin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $pais = Role::firstOrCreate(['name' => 'pais']);
        $profissional = Role::firstOrCreate(['name' => 'profissional']);

        // Criação de Permissões
        $permissions = [
            // Usuários
            'list users',
            'view users',
            'create users',
            'edit users',
            'remove users',

            // Crianças
            'list kids',
            'view kids',
            'create kids',
            'edit kids',
            'remove kids',

            // Roles
            'list roles',
            'view roles',
            'create roles',
            'edit roles',
            'remove roles',

            // Checklists
            'list checklists',
            'view checklists',
            'create checklists',
            'edit checklists',
            'remove checklists',
            'fill checklists',

            // Permissões Adicionais
            'manage dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Atribuição de Permissões aos Roles
        $superAdmin->syncPermissions($permissions);
        
        // Admin tem todas as permissões relacionadas a usuários, roles, kids e checklists
        $admin->syncPermissions([
            'list users',
            'view users',
            'create users',
            'edit users',
            'remove users',
            'list roles',
            'view roles',
            'create roles',
            'edit roles',
            'remove roles',
            'list kids',
            'view kids',
            'create kids',
            'edit kids',
            'remove kids',
            'list checklists',
            'view checklists',
            'create checklists',
            'edit checklists',
            'remove checklists',
            'fill checklists',
            'manage dashboard',
        ]);

        // Profissional tem permissões limitadas
        $profissional->syncPermissions([
            'list kids',
            'view kids',
            'create kids',
            'edit kids',
            'list checklists',
            'view checklists',
            'create checklists',
            'edit checklists',
            'fill checklists',
        ]);

        // Pais tem permissões limitadas aos seus filhos e checklists
        $pais->syncPermissions([
            'list kids',
            'view kids',
            'edit kids',
            'list checklists',
            'view checklists',
        ]);
    }
}