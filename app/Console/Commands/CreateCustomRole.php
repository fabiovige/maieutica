<?php

namespace App\Console\Commands;

use App\Services\RoleService;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateCustomRole extends Command
{
    protected $signature = 'role:create {name} {--permissions=*}';
    protected $description = 'Criar uma nova role personalizada no sistema';

    private RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        parent::__construct();
        $this->roleService = $roleService;
    }

    public function handle(): int
    {
        $roleName = $this->argument('name');
        $permissions = $this->option('permissions') ?? [];

        if (empty($roleName)) {
            $this->error('Nome da role é obrigatório');
            return self::FAILURE;
        }

        // Verificar se a role já existe
        if ($this->roleService->getRoleByName($roleName)) {
            $this->error("Role '{$roleName}' já existe");
            return self::FAILURE;
        }

        // Verificar se não é uma role do sistema
        if ($this->roleService->isSystemRole($roleName)) {
            $this->error("'{$roleName}' é uma role do sistema e não pode ser criada");
            return self::FAILURE;
        }

        try {
            // Se não foram fornecidas permissões, perguntar interativamente
            if (empty($permissions)) {
                $permissions = $this->askForPermissions();
            }

            $role = $this->roleService->createRole($roleName, $roleName, $permissions);

            $this->info("Role '{$roleName}' criada com sucesso!");
            
            if (!empty($permissions)) {
                $this->info('Permissões atribuídas:');
                foreach ($permissions as $permission) {
                    $this->line("  - {$permission}");
                }
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Erro ao criar role: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function askForPermissions(): array
    {
        $availablePermissions = Permission::all()->pluck('name')->toArray();
        
        $this->info('Permissões disponíveis:');
        foreach ($availablePermissions as $permission) {
            $this->line("  - {$permission}");
        }

        $selectedPermissions = [];
        
        while (true) {
            $permission = $this->ask('Digite uma permissão para adicionar (ou "done" para finalizar)');
            
            if ($permission === 'done') {
                break;
            }

            if (in_array($permission, $availablePermissions)) {
                if (!in_array($permission, $selectedPermissions)) {
                    $selectedPermissions[] = $permission;
                    $this->info("Permissão '{$permission}' adicionada");
                } else {
                    $this->warn("Permissão '{$permission}' já foi adicionada");
                }
            } else {
                $this->warn("Permissão '{$permission}' não existe");
            }
        }

        return $selectedPermissions;
    }
}