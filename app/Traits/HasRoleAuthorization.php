<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\UserRole;
use App\Models\Kid;

trait HasRoleAuthorization
{
    public function getUserRole(): ?UserRole
    {
        $roleName = $this->roles->first()?->name;
        return $roleName ? UserRole::getByName($roleName) : null;
    }

    public function isSuperAdmin(): bool
    {
        return $this->getUserRole() === UserRole::SUPERADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->getUserRole() === UserRole::ADMIN;
    }

    public function isProfessional(): bool
    {
        return $this->getUserRole() === UserRole::PROFESSIONAL;
    }

    public function isParent(): bool
    {
        return $this->getUserRole() === UserRole::PARENT;
    }

    // Alias para compatibilidade
    public function isPais(): bool
    {
        return $this->isParent();
    }

    public function canManageAllKids(): bool
    {
        return $this->getUserRole()?->canManageAllKids() ?? false;
    }

    public function canViewKid(Kid $kid): bool
    {
        $role = $this->getUserRole();
        
        if (!$role) {
            return false;
        }

        // Admin e SuperAdmin podem ver todas as crianças
        if ($role->canManageAllKids()) {
            return true;
        }

        // Pais podem ver apenas suas crianças
        if ($role === UserRole::PARENT) {
            return $kid->responsible_id === $this->id;
        }

        // Profissionais podem ver apenas crianças associadas a eles
        if ($role === UserRole::PROFESSIONAL) {
            return $this->isAssociatedWithKid($kid);
        }

        return false;
    }

    public function canEditKid(Kid $kid): bool
    {
        $role = $this->getUserRole();
        
        if (!$role) {
            return false;
        }

        // Admin e SuperAdmin podem editar todas as crianças
        if ($role->canManageAllKids()) {
            return true;
        }

        // Pais podem editar suas crianças
        if ($role === UserRole::PARENT) {
            return $kid->responsible_id === $this->id;
        }

        // Profissionais podem editar crianças associadas a eles ou criadas por eles
        if ($role === UserRole::PROFESSIONAL) {
            return $this->isAssociatedWithKid($kid) || $kid->created_by === $this->id;
        }

        return false;
    }

    public function isAssociatedWithKid(Kid $kid): bool
    {
        if (!$this->isProfessional()) {
            return false;
        }

        $professional = $this->professional->first();
        
        if (!$professional) {
            return false;
        }

        return $kid->professionals->contains('id', $professional->id);
    }

    public function getAccessibleKidsQuery()
    {
        $role = $this->getUserRole();

        if (!$role) {
            return Kid::whereRaw('1 = 0'); // Não retorna nenhuma criança
        }

        // Admin e SuperAdmin veem todas as crianças
        if ($role->canManageAllKids()) {
            return Kid::query();
        }

        // Pais veem apenas suas crianças
        if ($role === UserRole::PARENT) {
            return Kid::where('responsible_id', $this->id);
        }

        // Profissionais veem apenas crianças associadas a eles
        if ($role === UserRole::PROFESSIONAL) {
            $professional = $this->professional->first();
            
            if (!$professional) {
                return Kid::whereRaw('1 = 0');
            }

            return Kid::where(function ($query) use ($professional) {
                $query->whereHas('professionals', function ($q) use ($professional) {
                    $q->where('professional_id', $professional->id);
                })->orWhere('created_by', $this->id);
            });
        }

        return Kid::whereRaw('1 = 0');
    }
}