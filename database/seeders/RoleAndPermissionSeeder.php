<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

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
            'clone checklists',
            'plane automatic checklist',
            'plane manual checklist',
            'avaliation checklist',

            // Professionals
            'list professionals',
            'view professionals',
            'create professionals',
            'edit professionals',
            'remove professionals',
            'activate professionals',
            'deactivate professionals',

            // Competences
            'list competences',
            'edit competences',

            // Permissões Adicionais
            'manage dashboard',
            'view logs',
            'view competences',
            
            // Permissões Administrativas Especiais
            'bypass-all-checks',  // Para superadmin ter acesso total
            'manage-system',      // Para admin gerenciar o sistema
            'override-checklist-status', // Permite editar checklists fechados
            'attach-to-kids-as-professional', // Permite se auto-adicionar como profissional
            'view-all-kids',      // Ver todas as crianças sem restrição
            'view-all-users',     // Ver todos os usuários sem restrição
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Atribuição de Permissões aos Roles
        $superAdmin->syncPermissions($permissions);

        // Admin tem todas as permissões relacionadas a usuários, roles, kids e checklists
        $admin->syncPermissions($permissions);

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
            'clone checklists',
            'plane automatic checklist',
            'plane manual checklist',
            'avaliation checklist',
            'fill checklists',

            'list planes',
            'view planes',
            'create planes',
            'edit planes',
            'remove planes',

            // Competences
            'list competences',
            'view competences',
            'edit competences',
            
            // Permissão especial para profissionais
            'attach-to-kids-as-professional',
        ]);

        // Pais tem permissões limitadas aos seus filhos e checklists
        $pais->syncPermissions([
            'list kids',
            'view kids',
            'edit kids',
            'list checklists',
        ]);
    }
}
