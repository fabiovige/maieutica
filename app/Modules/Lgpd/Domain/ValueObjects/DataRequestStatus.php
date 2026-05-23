<?php

namespace App\Modules\Lgpd\Domain\ValueObjects;

enum DataRequestStatus: string
{
    case ABERTA = 'aberta';
    case EM_ANDAMENTO = 'em_andamento';
    case CONCLUIDA = 'concluida';
    case VENCIDA = 'vencida';

    public function label(): string
    {
        return match ($this) {
            self::ABERTA => 'Aberta',
            self::EM_ANDAMENTO => 'Em andamento',
            self::CONCLUIDA => 'Concluída',
            self::VENCIDA => 'Vencida',
        };
    }
}
