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
        $professional = Role::firstOrCreate(['name' => 'professional']);

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

            // Planes
            'list planes',
            'view planes',
            'create planes',
            'edit planes',
            'remove planes',

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

            // Competences
            'list competences',
            'edit competences',
            
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

            // Planes
            'list planes',
            'view planes',
            'create planes',
            'edit planes',
            'remove planes',

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

            // Competences
            'list competences',
            'edit competences',
            
            // Permissões Adicionais
            'manage dashboard',
        ]);

        // professional tem permissões limitadas
        $professional->syncPermissions([
            'list kids',
            'view kids',
            'create kids',
            'edit kids',

            'list checklists',
            'view checklists',
            'create checklists',
            'edit checklists',
            'fill checklists',

            'list planes',
            'view planes',
            'create planes',
            'edit planes',
            'remove planes',

            // Competences
            'list competences',
            'edit competences',
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
