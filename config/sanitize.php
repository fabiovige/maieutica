<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Input Sanitization Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file contains settings for input sanitization to
    | protect against XSS attacks while preserving legitimate HTML content.
    |
    */

    'enabled' => env('SANITIZE_INPUT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Log XSS Attempts
    |--------------------------------------------------------------------------
    |
    | When enabled, the system will log all XSS attempts that are detected
    | and sanitized. This helps monitor security incidents.
    |
    */

    'log_xss_attempts' => env('SANITIZE_LOG_XSS', true),

    /*
    |--------------------------------------------------------------------------
    | Allowed HTML Tags
    |--------------------------------------------------------------------------
    |
    | These are the HTML tags that will be preserved when sanitizing rich
    | text fields. All other tags will be stripped or escaped.
    |
    */

    'allowed_tags' => [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote',
        'a' => ['href', 'title', 'target'],
        'img' => ['src', 'alt', 'width', 'height', 'title'],
        'span' => ['class', 'style'],
        'div' => ['class'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rich Text Fields
    |--------------------------------------------------------------------------
    |
    | These fields will be sanitized using the allowed HTML tags above.
    | Other fields will have all HTML completely stripped.
    |
    */

    'rich_text_fields' => [
        'description',
        'content',
        'details',
        'note',
        'notes',
        'observation',
        'observations',
        'comment',
        'comments',
        'bio',
        'about',
        'message',
        'body',
    ],

    /*
    |--------------------------------------------------------------------------
    | Skip Sanitization Fields
    |--------------------------------------------------------------------------
    |
    | These fields will not be sanitized at all. Use with extreme caution
    | and only for fields that are not user-facing or are validated elsewhere.
    |
    */

    'skip_fields' => [
        '_token',
        '_method',
        'password',
        'password_confirmation',
        'current_password',
    ],

    /*
    |--------------------------------------------------------------------------
    | Skip Sanitization Routes
    |--------------------------------------------------------------------------
    |
    | Routes that should be completely skipped from sanitization.
    | Useful for API routes or file upload endpoints.
    |
    */

    'skip_routes' => [
        'api/*',
        'upload/*',
        'files/*',
        'storage/*',
    ],

    /*
    |--------------------------------------------------------------------------
    | HTMLPurifier Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for HTMLPurifier library used for rich text
    | sanitization. These settings provide a balance between security
    | and functionality.
    |
    */

    'purifier_config' => [
        'HTML.Allowed' => 'p,br,strong,b,em,i,u,ul,ol,li,h1,h2,h3,h4,h5,h6,blockquote,a[href|title|target],img[src|alt|width|height|title],span[class|style],div[class]',
        'HTML.ForbiddenElements' => 'script,object,embed,form,input,button,select,textarea,iframe,frame,frameset,meta,link,style,title',
        'HTML.ForbiddenAttributes' => 'onclick,onload,onerror,onmouseover,onmouseout,onfocus,onblur,onchange,onsubmit',
        'AutoFormat.AutoParagraph' => false,
        'AutoFormat.RemoveEmpty' => true,
        'Core.Encoding' => 'UTF-8',
        'Cache.SerializerPath' => storage_path('framework/cache/htmlpurifier'),
        'URI.DisableExternalResources' => true,
        'URI.DisableResources' => false,
        'HTML.Nofollow' => true,
        'HTML.TargetBlank' => true,
        'Filter.YouTube' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | XSS Detection Patterns
    |--------------------------------------------------------------------------
    |
    | Regular expressions used to detect potential XSS attempts.
    | When these patterns are found, the attempt will be logged.
    |
    */

    'xss_patterns' => [
        '/<script[^>]*>.*?<\/script>/is',
        '/javascript:/i',
        '/vbscript:/i',
        '/on\w+\s*=/i',
        '/<iframe[^>]*>/i',
        '/<object[^>]*>/i',
        '/<embed[^>]*>/i',
        '/<form[^>]*>/i',
        '/expression\s*\(/i',
        '/import\s+/i',
        '/@import/i',
        '/document\./i',
        '/window\./i',
        '/eval\s*\(/i',
    ],
];