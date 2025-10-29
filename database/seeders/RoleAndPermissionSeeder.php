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
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $responsavel = Role::firstOrCreate(['name' => 'responsavel']);
        $profissional = Role::firstOrCreate(['name' => 'profissional']);

        // Criação de Permissões
        $permissions = [
            // Usuários
            'user-list',
            'user-show',
            'user-create',
            'user-edit',
            'user-delete',
            'user-list-all',
            'user-show-all',
            'user-edit-all',
            'user-delete-all',

            // Crianças
            'kid-list',
            'kid-show',
            'kid-create',
            'kid-edit',
            'kid-delete',
            'kid-list-all',
            'kid-show-all',
            'kid-edit-all',
            'kid-delete-all',

            // Planos
            'plan-list',
            'plan-show',
            'plan-create',
            'plan-edit',
            'plan-delete',
            'plan-list-all',
            'plan-show-all',
            'plan-edit-all',
            'plan-delete-all',

            // Perfis (Roles)
            'role-list',
            'role-show',
            'role-create',
            'role-edit',
            'role-delete',
            'role-list-all',
            'role-show-all',
            'role-edit-all',
            'role-delete-all',

            // Checklists
            'checklist-list',
            'checklist-show',
            'checklist-create',
            'checklist-edit',
            'checklist-delete',
            'checklist-list-all',
            'checklist-show-all',
            'checklist-edit-all',
            'checklist-delete-all',
            'checklist-fill',
            'checklist-clone',
            'checklist-plane-automatic',
            'checklist-plane-manual',
            'checklist-avaliation',

            // Profissionais
            'professional-list',
            'professional-show',
            'professional-create',
            'professional-edit',
            'professional-delete',
            'professional-list-all',
            'professional-show-all',
            'professional-edit-all',
            'professional-delete-all',
            'professional-activate',
            'professional-deactivate',

            // Competências
            'competence-list',
            'competence-edit',
            'competence-list-all',
            'competence-edit-all',

            // Permissões adicionais / administrativas
            'dashboard-manage',
        ];


        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin->syncPermissions($permissions);

        $permissionsProfissional = [
            // Usuários
            'user-list',
            'user-show',
            'user-create',
            'user-edit',

            // Crianças
            'kid-list',
            'kid-show',
            'kid-create',
            'kid-edit',

            // Planos
            'plan-list',
            'plan-show',
            'plan-create',
            'plan-edit',

            // Checklists
            'checklist-list',
            'checklist-show',
            'checklist-create',
            'checklist-edit',
            'checklist-fill',
            'checklist-clone',
            'checklist-plane-automatic',
            'checklist-plane-manual',
            'checklist-avaliation',

            // Profissionais
            'professional-list',
            'professional-show',
            'professional-create',
            'professional-edit',
            'professional-activate',
            'professional-deactivate',
        ];
        $profissional->syncPermissions($permissionsProfissional);

        $responsavel->syncPermissions([
            'kid-list',
            'kid-show',
            'kid-edit',
        ]);
    }
}
