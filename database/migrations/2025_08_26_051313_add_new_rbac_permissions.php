<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Criar novas permissões (apenas se não existirem)
        $newPermissions = [
            'view logs',
            'view competences',
            'bypass-all-checks',
            'manage-system', 
            'override-checklist-status',
            'attach-to-kids-as-professional',
            'view-all-kids',
            'view-all-users',
        ];

        foreach ($newPermissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // Atribuir novas permissões às roles existentes (APENAS ADICIONAR, não remover)
        $superAdmin = Role::where('name', 'superadmin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($newPermissions);
        }

        $admin = Role::where('name', 'admin')->first(); 
        if ($admin) {
            $admin->givePermissionTo([
                'view logs',
                'view competences', 
                'manage-system',
                'override-checklist-status',
                'view-all-kids',
                'view-all-users',
            ]);
        }

        $professional = Role::where('name', 'professional')->first();
        if ($professional) {
            $professional->givePermissionTo([
                'view competences',
                'attach-to-kids-as-professional',
                'view checklists',  // Garantir que profissional tem essa permissão
                'fill checklists',  // Garantir que profissional tem essa permissão
            ]);
        }

        $pais = Role::where('name', 'pais')->first();
        if ($pais) {
            $pais->givePermissionTo([
                'view checklists',  // Garantir que pais pode ver checklists
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Remover apenas as novas permissões criadas nesta migration
        $permissionsToRemove = [
            'view logs',
            'view competences',
            'bypass-all-checks',
            'manage-system',
            'override-checklist-status', 
            'attach-to-kids-as-professional',
            'view-all-kids',
            'view-all-users',
        ];

        foreach ($permissionsToRemove as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission) {
                $permission->delete();
            }
        }

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};