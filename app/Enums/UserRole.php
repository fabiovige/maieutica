<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case SUPERADMIN = 'superadmin';
    case ADMIN = 'admin';
    case PROFESSIONAL = 'professional';
    case PARENT = 'parent';

    public static function getByName(string $name): ?self
    {
        return match (strtolower($name)) {
            'superadmin', 'super-admin', 'super_admin' => self::SUPERADMIN,
            'admin' => self::ADMIN,
            'professional', 'profissional' => self::PROFESSIONAL,
            'parent', 'pai', 'pais', 'responsible' => self::PARENT,
            default => null,
        };
    }

    public function canManageAllKids(): bool
    {
        return match ($this) {
            self::SUPERADMIN, self::ADMIN => true,
            self::PROFESSIONAL, self::PARENT => false,
        };
    }

    public function getDisplayName(): string
    {
        return match ($this) {
            self::SUPERADMIN => 'Super Administrador',
            self::ADMIN => 'Administrador',
            self::PROFESSIONAL => 'Profissional',
            self::PARENT => 'Responsável',
        };
    }

    public function getPermissions(): array
    {
        return match ($this) {
            self::SUPERADMIN => [
                'bypass-all-checks',
                'manage-system',
                'view users',
                'create users',
                'edit users',
                'remove users',
                'view roles',
                'create roles',
                'edit roles',
                'remove roles',
                'view kids',
                'create kids',
                'edit kids',
                'remove kids',
                'view checklists',
                'create checklists',
                'edit checklists',
                'remove checklists',
                'view professionals',
                'create professionals',
                'edit professionals',
                'remove professionals',
                'view competences',
                'create competences',
                'edit competences',
                'remove competences',
                'view logs',
            ],
            self::ADMIN => [
                'manage-system',
                'view users',
                'create users',
                'edit users',
                'remove users',
                'view roles',
                'view kids',
                'create kids',
                'edit kids',
                'remove kids',
                'view checklists',
                'create checklists',
                'edit checklists',
                'remove checklists',
                'view professionals',
                'create professionals',
                'edit professionals',
                'remove professionals',
                'view competences',
                'view logs',
            ],
            self::PROFESSIONAL => [
                'view kids',
                'create kids',
                'edit kids',
                'view checklists',
                'create checklists',
                'edit checklists',
                'view professionals',
                'view competences',
            ],
            self::PARENT => [
                'view kids',
                'view checklists',
            ],
        };
    }
}
