<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case SUPERADMIN = 'superadmin';
    case ADMIN = 'admin';
    case PROFESSIONAL = 'professional';
    case PARENT = 'pais';

    public function label(): string
    {
        return match($this) {
            self::SUPERADMIN => 'Super Administrador',
            self::ADMIN => 'Administrador',
            self::PROFESSIONAL => 'Profissional',
            self::PARENT => 'Pais/Responsável',
        };
    }

    public function hasPermission(string $permission): bool
    {
        return match($this) {
            self::SUPERADMIN => true, // Super admin tem todas as permissões
            self::ADMIN => in_array($permission, [
                'list users', 'view users', 'create users', 'edit users', 'remove users',
                'list kids', 'view kids', 'create kids', 'edit kids', 'remove kids',
                'list planes', 'view planes', 'create planes', 'edit planes', 'remove planes',
                'list roles', 'view roles', 'create roles', 'edit roles', 'remove roles',
                'list checklists', 'view checklists', 'create checklists', 'edit checklists', 'remove checklists',
                'fill checklists', 'clone checklists', 'plane automatic checklist', 'plane manual checklist', 'avaliation checklist',
                'list professionals', 'view professionals', 'create professionals', 'edit professionals', 'remove professionals',
                'activate professionals', 'deactivate professionals', 'list competences', 'edit competences', 'manage dashboard'
            ]),
            self::PROFESSIONAL => in_array($permission, [
                'list kids', 'view kids', 'create kids', 'edit kids',
                'list planes', 'view planes', 'create planes', 'edit planes', 'remove planes',
                'list checklists', 'create checklists', 'edit checklists', 'clone checklists',
                'plane automatic checklist', 'plane manual checklist', 'avaliation checklist',
                'list competences', 'edit competences'
            ]),
            self::PARENT => in_array($permission, [
                'list kids', 'view kids', 'edit kids', 'list checklists'
            ]),
        };
    }

    public function canManageAllKids(): bool
    {
        return in_array($this, [self::SUPERADMIN, self::ADMIN]);
    }

    public function canManageOwnKids(): bool
    {
        return $this === self::PARENT;
    }

    public function canManageAssociatedKids(): bool
    {
        return $this === self::PROFESSIONAL;
    }

    public static function getByName(string $name): ?self
    {
        return match($name) {
            'superadmin' => self::SUPERADMIN,
            'admin' => self::ADMIN,
            'professional' => self::PROFESSIONAL,
            'pais' => self::PARENT,
            default => null,
        };
    }

    public static function all(): array
    {
        return [
            self::SUPERADMIN,
            self::ADMIN,
            self::PROFESSIONAL,
            self::PARENT,
        ];
    }
}
