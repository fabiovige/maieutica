<?php

declare(strict_types=1);

namespace App\Enums;

enum ListActionType: string
{
    case VIEW = 'view';
    case EDIT = 'edit';
    case DELETE = 'delete';
    case CLONE = 'clone';
    case CUSTOM = 'custom';

    public function getIcon(): string
    {
        return match($this) {
            self::VIEW => 'bi-eye',
            self::EDIT => 'bi-pencil',
            self::DELETE => 'bi-trash',
            self::CLONE => 'bi-files',
            self::CUSTOM => 'bi-gear',
        };
    }

    public function getBootstrapClass(): string
    {
        return match($this) {
            self::VIEW => 'btn-outline-primary',
            self::EDIT => 'btn-outline-secondary',
            self::DELETE => 'btn-outline-danger',
            self::CLONE => 'btn-outline-info',
            self::CUSTOM => 'btn-outline-dark',
        };
    }

    public function getDefaultLabel(): string
    {
        return match($this) {
            self::VIEW => 'Visualizar',
            self::EDIT => 'Editar',
            self::DELETE => 'Excluir',
            self::CLONE => 'Clonar',
            self::CUSTOM => 'Ação',
        };
    }
}