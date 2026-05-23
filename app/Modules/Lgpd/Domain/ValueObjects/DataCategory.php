<?php

namespace App\Modules\Lgpd\Domain\ValueObjects;

enum DataCategory: string
{
    case PRONTUARIOS = 'prontuarios';
    case CONSENTIMENTOS = 'consentimentos';
    case ACCESS_LOGS = 'access_logs';
    case DADOS_CADASTRAIS = 'dados_cadastrais';

    public function label(): string
    {
        return match ($this) {
            self::PRONTUARIOS => 'Prontuários',
            self::CONSENTIMENTOS => 'Consentimentos',
            self::ACCESS_LOGS => 'Logs de acesso',
            self::DADOS_CADASTRAIS => 'Dados cadastrais',
        };
    }
}
