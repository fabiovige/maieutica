<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SanitizeInput
{
    private ?HTMLPurifier $purifier = null;
    private ?array $config = null;
    private ?array $xssPatterns = null;

    public function handle(Request $request, Closure $next)
    {
        $this->initializeConfig();

        if (!$this->shouldSanitize($request)) {
            return $next($request);
        }

        $this->sanitizeRequest($request);

        return $next($request);
    }

    private function shouldSanitize(Request $request): bool
    {
        if (!($this->config['enabled'] ?? true)) {
            return false;
        }

        if ($this->isFileUpload($request)) {
            return false;
        }

        if ($this->isSkippedRoute($request)) {
            return false;
        }

        return $request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH');
    }

    private function sanitizeRequest(Request $request): void
    {
        $input = $request->all();
        $sanitized = $this->sanitizeArray($input);

        $request->replace($sanitized);
    }

    private function sanitizeArray(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if ($this->shouldSkipField($key)) {
                $sanitized[$key] = $value;
                continue;
            }

            $sanitized[$key] = match (true) {
                is_array($value) => $this->sanitizeArray($value),
                is_string($value) => $this->sanitizeString($key, $value),
                default => $value
            };
        }

        return $sanitized;
    }

    private function sanitizeString(string $fieldName, string $value): string
    {
        if (trim($value) === '') {
            return $value;
        }

        $this->detectAndLogXss($fieldName, $value);

        return $this->isRichTextField($fieldName)
            ? $this->sanitizeRichText($value)
            : $this->sanitizePlainText($value);
    }

    private function sanitizeRichText(string $value): string
    {
        $this->initializePurifier();
        return $this->getCachedPurifiedContent($value);
    }

    private function sanitizePlainText(string $value): string
    {
        $sanitized = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);

        $sanitized = preg_replace('/javascript:/i', '', $sanitized);
        $sanitized = preg_replace('/vbscript:/i', '', $sanitized);
        $sanitized = preg_replace('/data:/i', '', $sanitized);

        return trim($sanitized);
    }

    private function getCachedPurifiedContent(string $value): string
    {
        $cacheKey = 'sanitized_' . md5($value);

        return Cache::remember($cacheKey, 3600, function () use ($value) {
            return $this->purifier->purify($value);
        });
    }

    private function detectAndLogXss(string $fieldName, string $value): void
    {
        if (!($this->config['log_xss_attempts'] ?? true)) {
            return;
        }

        foreach ($this->xssPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $this->logXssAttempt($fieldName, $value, $pattern);
                break;
            }
        }
    }

    private function logXssAttempt(string $fieldName, string $value, string $pattern): void
    {
        Log::warning('Potencial tentativa de XSS detectada', [
            'field' => $fieldName,
            'pattern_matched' => $pattern,
            'user_agent' => request()->userAgent(),
            'ip' => request()->ip(),
            'url' => request()->fullUrl(),
            'user_id' => auth()->id(),
            'suspicious_content' => Str::limit($value, 200),
            'timestamp' => now()->toISOString(),
        ]);
    }

    private function isRichTextField(string $fieldName): bool
    {
        $richTextFields = $this->config['rich_text_fields'] ?? [];

        return in_array($fieldName, $richTextFields) ||
               $this->matchesRichTextPattern($fieldName);
    }

    private function matchesRichTextPattern(string $fieldName): bool
    {
        $patterns = [
            '*_description',
            '*_content',
            '*_details',
            '*_note',
            '*_notes',
            'description_*',
            'content_*',
            'details_*',
            'note_*',
            'notes_*',
        ];

        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $fieldName)) {
                return true;
            }
        }

        return false;
    }

    private function shouldSkipField(string $fieldName): bool
    {
        $skipFields = $this->config['skip_fields'] ?? [];

        return in_array($fieldName, $skipFields) ||
               Str::startsWith($fieldName, ['_', 'csrf']);
    }

    private function isSkippedRoute(Request $request): bool
    {
        $skipRoutes = $this->config['skip_routes'] ?? [];
        $currentPath = $request->path();

        foreach ($skipRoutes as $pattern) {
            if (Str::is($pattern, $currentPath)) {
                return true;
            }
        }

        return false;
    }

    private function isFileUpload(Request $request): bool
    {
        return $request->hasFile('*') ||
               Str::contains($request->header('Content-Type', ''), 'multipart/form-data');
    }

    private function initializeConfig(): void
    {
        if ($this->config !== null) {
            return;
        }

        $this->config = config('sanitize', []);
        $this->xssPatterns = $this->config['xss_patterns'] ?? [];
    }

    private function initializePurifier(): void
    {
        if ($this->purifier !== null) {
            return;
        }

        $config = HTMLPurifier_Config::createDefault();
        $purifierConfig = $this->config['purifier_config'] ?? [];

        foreach ($purifierConfig as $key => $value) {
            $config->set($key, $value);
        }

        $cacheDir = storage_path('framework/cache/htmlpurifier');
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $this->purifier = new HTMLPurifier($config);
    }
}