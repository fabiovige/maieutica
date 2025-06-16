<?php

namespace App\Enums;

enum ProgressColors: string
{
    case PERCENT_0 = '#6a2046';    // Mais escuro para 0%
    case PERCENT_10 = '#8a2e5c';   // Escuro
    case PERCENT_20 = '#a34677';
    case PERCENT_30 = '#a7527f';
    case PERCENT_40 = '#ab5e88';
    case PERCENT_50 = '#af6a90';
    case PERCENT_60 = '#b37698';
    case PERCENT_70 = '#bb8ea9';
    case PERCENT_80 = '#bf9ab1';
    case PERCENT_90 = '#c3a6ba';
    case PERCENT_100 = '#f7e6f2';  // Mais claro para 100%

    public static function getColorForPercentage(float $percentage): string
    {
        $roundedPercentage = (int) round($percentage / 10) * 10;
        $roundedPercentage = max(0, min(100, $roundedPercentage));

        return match($roundedPercentage) {
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

    public static function getGradientForPercentage(float $percentage): string
    {
        $percentage = max(0, min(100, $percentage));

        // Encontrar as duas cores mais próximas para o gradiente
        $lowerBound = floor($percentage / 10) * 10;
        $upperBound = min(100, $lowerBound + 10);

        $lowerColor = self::getColorForPercentage($lowerBound);
        $upperColor = self::getColorForPercentage($upperBound);

        // Calcular a posição do gradiente baseado na porcentagem entre os dois pontos
        $position = ($percentage - $lowerBound) / 10;

        return "linear-gradient(to right, {$lowerColor}, {$upperColor})";
    }

    public static function getGradientForChart(float $percentage): string
    {
        $percentage = max(0, min(100, $percentage));

        // Encontrar as duas cores mais próximas para o gradiente
        $lowerBound = floor($percentage / 10) * 10;
        $upperBound = min(100, $lowerBound + 10);

        $lowerColor = self::getColorForPercentage($lowerBound);
        $upperColor = self::getColorForPercentage($upperBound);

        // Calcular a posição do gradiente baseado na porcentagem entre os dois pontos
        $position = ($percentage - $lowerBound) / 10;

        return "linear-gradient(180deg, {$lowerColor}, {$upperColor})";
    }
}
