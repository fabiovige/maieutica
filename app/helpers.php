<?php

use App\Enums\ProgressColors;

if (!function_exists('label_case')) {
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

if (!function_exists('safe_html')) {
    function safe_html(?string $string): string
    {
        if (empty($string)) {
            return '';
        }
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
    }
}

if (!function_exists('safe_attribute')) {
    function safe_attribute(?string $string): string
    {
        if (empty($string)) {
            return '';
        }
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
    }
}

if (!function_exists('safe_js')) {
    function safe_js($value): string
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }
}

if (!function_exists('strip_all_tags')) {
    function strip_all_tags(?string $string): string
    {
        if (empty($string)) {
            return '';
        }
        return strip_tags($string);
    }
}
