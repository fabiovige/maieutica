<?php

namespace App\Modules\Lgpd\Domain\ValueObjects;

enum ConsentStatus: string
{
    case ATIVO = 'ativo';
    case REVOGADO = 'revogado';

    public function label(): string
    {
        return match ($this) {
            self::ATIVO => 'Ativo',
            self::REVOGADO => 'Revogado',
        };
    }
}
