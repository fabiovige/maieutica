<?php

declare(strict_types=1);

namespace App\Services\UserCreationStrategies;

use App\Models\Professional;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProfessionalUserCreationStrategy implements UserCreationStrategyInterface
{
    public function createUser(User $user, array $data): void
    {
        // NÃO CRIAR PROFESSIONAL AQUI - apenas criar usuário
        // O Professional será criado pelo ProfessionalService
        // Esta strategy é apenas para vincular usuários existentes
    }

    public function updateUser(User $user, array $data): void
    {
        // Para update, apenas sincronizar se não existe Professional associado
        $existingProfessional = $user->professionals()->first();
        
        if (!$existingProfessional) {
            // Se não existe, criar como no create
            $this->createUser($user, $data);
        }
        // Se já existe, não fazer nada ou atualizar dados específicos do Professional
    }

    public function supports(string $roleName): bool
    {
        return $roleName === 'professional';
    }
}