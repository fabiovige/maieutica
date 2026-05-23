<?php

namespace App\Modules\Lgpd\Domain\ValueObjects;

enum OperationType: string
{
    case VIEW = 'view';
    case DOWNLOAD_PDF = 'download_pdf';
    case EDIT = 'edit';
    case DELETE = 'delete';
    case RESTORE = 'restore';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'Visualização',
            self::DOWNLOAD_PDF => 'Download de PDF',
            self::EDIT => 'Edição',
            self::DELETE => 'Exclusão',
            self::RESTORE => 'Restauração',
        };
    }
}
