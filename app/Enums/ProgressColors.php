<?php

namespace App\Enums;

enum ProgressColors: string
{
    case PERCENT_0 = '#a84a69';    // Mais escuro para 0%
    case PERCENT_10 = '#a34677';
    case PERCENT_20 = '#a7527f';
    case PERCENT_30 = '#ab5e88';
    case PERCENT_40 = '#af6a90';
    case PERCENT_50 = '#b37698';
    case PERCENT_60 = '#b782a1';
    case PERCENT_70 = '#bb8ea9';
    case PERCENT_80 = '#bf9ab1';
    case PERCENT_90 = '#c3a6ba';
    case PERCENT_100 = '#c7b2c2';  // Mais claro para 100%

    public static function getColorForPercentage(float $percentage): string
    {
        $percentage = round($percentage / 10) * 10; // Arredonda para o múltiplo de 10 mais próximo
        $percentage = max(0, min(100, $percentage)); // Garante que está entre 0 e 100

        return match ($percentage) {
            0 => self::PERCENT_0->value,
            10 => self::PERCENT_10->value,
            20 => self::PERCENT_20->value,
            30 => self::PERCENT_30->value,
            40 => self::PERCENT_40->value,
            50 => self::PERCENT_50->value,
            60 => self::PERCENT_60->value,
            70 => self::PERCENT_70->value,
            80 => self::PERCENT_80->value,
            90 => self::PERCENT_90->value,
            100 => self::PERCENT_100->value,
            default => self::PERCENT_0->value,
        };
    }
}
