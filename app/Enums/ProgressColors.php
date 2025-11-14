<?php

namespace App\Enums;

enum ProgressColors: string
{
    case PERCENT_0 = '#dc3545';    // Vermelho escuro - Muito baixo (0%)
    case PERCENT_10 = '#e04555';   // Vermelho - Baixo (10%)
    case PERCENT_20 = '#fd7e14';   // Laranja escuro - Abaixo da média (20%)
    case PERCENT_30 = '#ff8c24';   // Laranja - Abaixo da média (30%)
    case PERCENT_40 = '#ffc107';   // Amarelo - Médio (40%)
    case PERCENT_50 = '#ffd017';   // Amarelo claro - Médio (50%)
    case PERCENT_60 = '#0dcaf0';   // Azul claro - Bom (60%)
    case PERCENT_70 = '#17d4f5';   // Azul claro 2 - Bom (70%)
    case PERCENT_80 = '#198754';   // Verde escuro - Muito bom (80%)
    case PERCENT_90 = '#20c997';   // Verde médio - Muito bom (90%)
    case PERCENT_100 = '#28a745';  // Verde claro - Excelente (100%)

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
