<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): BaseResponse
    {
        $response = $next($request);

        $this->setSecurityHeaders($response, $request);

        return $response;
    }

    private function setSecurityHeaders(BaseResponse $response, Request $request): void
    {
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        $csp = $this->buildContentSecurityPolicy();
        $response->headers->set('Content-Security-Policy', $csp);

        if ($this->shouldSetHSTS($request)) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
    }

    private function buildContentSecurityPolicy(): string
    {
        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.jsdelivr.net cdnjs.cloudflare.com www.google.com www.gstatic.com www.recaptcha.net",
            "style-src 'self' 'unsafe-inline' fonts.googleapis.com cdn.jsdelivr.net cdnjs.cloudflare.com",
            "font-src 'self' fonts.gstatic.com",
            "img-src 'self' data: blob: www.google.com www.gstatic.com www.recaptcha.net",
            "connect-src 'self' www.google.com www.gstatic.com www.recaptcha.net",
            "frame-src 'self' www.google.com www.recaptcha.net",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'"
        ];

        return implode('; ', $directives);
    }

    private function shouldSetHSTS(Request $request): bool
    {
        return $request->isSecure() ||
               app()->environment('production') ||
               $request->server('HTTPS') === 'on';
    }
}
