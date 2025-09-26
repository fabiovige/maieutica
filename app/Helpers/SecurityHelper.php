<?php

namespace App\Helpers;

class SecurityHelper
{
    public static function escapeHtml(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
    }

    public static function escapeHtmlAttribute(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
    }

    public static function sanitizeForSafeDisplay(string $string): string
    {
        $string = strip_tags($string, '<br><p><strong><em><i><b><u>');

        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
    }

    public static function escapeJs(string $string): string
    {
        return json_encode($string, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }

    public static function stripAllTags(string $string): string
    {
        return strip_tags($string);
    }

    public static function getRequiredIndicator(): string
    {
        return '<span class="text-danger">*</span>';
    }
}