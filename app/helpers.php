<?php

use App\Enums\ProgressColors;

if (! function_exists('label_case')) {

    function label_case($text)
    {
        $order = ['_', '-'];
        $replace = ' ';

        $new_text = trim(\Illuminate\Support\Str::title(str_replace('"', '', $text)));
        $new_text = trim(\Illuminate\Support\Str::title(str_replace($order, $replace, $text)));
        $new_text = preg_replace('!\s+!', ' ', $new_text);

        return $new_text;
    }
}

if (!function_exists('get_progress_color')) {
    function get_progress_color(float $percentage): string
    {
        return ProgressColors::getColorForPercentage($percentage);
    }
}

if (!function_exists('get_progress_gradient')) {
    function get_progress_gradient(float $percentage): string
    {
        return ProgressColors::getGradientForPercentage($percentage);
    }
}

if (!function_exists('get_chart_gradient')) {
    function get_chart_gradient(float $percentage): string
    {
        return ProgressColors::getGradientForChart($percentage);
    }
}
