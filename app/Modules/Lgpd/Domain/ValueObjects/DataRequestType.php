<?php

namespace App\Modules\Lgpd\Domain\ValueObjects;

enum DataRequestType: string
{
    case ACESSO = 'acesso';
    case RETIFICACAO = 'retificacao';
    case ELIMINACAO = 'eliminacao';
    case PORTABILIDADE = 'portabilidade';
    case REVOGACAO = 'revogacao';

    public function label(): string
    {
        return match ($this) {
            self::ACESSO => 'Acesso aos dados',
            self::RETIFICACAO => 'Retificação',
            self::ELIMINACAO => 'Eliminação',
            self::PORTABILIDADE => 'Portabilidade',
            self::REVOGACAO => 'Revogação de consentimento',
        };
    }
}
