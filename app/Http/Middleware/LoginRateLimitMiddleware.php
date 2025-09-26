<?php

namespace App\Http\Middleware;

use App\Services\Security\LoginRateLimiterService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LoginRateLimitMiddleware
{
    public function __construct(
        private readonly LoginRateLimiterService $rateLimiterService
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->rateLimiterService->canAttemptLogin($request)) {
            return $this->buildThrottledResponse($request);
        }

        return $next($request);
    }

    private function buildThrottledResponse(Request $request): Response
    {
        $summary = $this->rateLimiterService->getLoginAttemptsSummary($request);
        $remainingTime = $summary['remaining_time'];

        $response = response()->json([
            'message' => $summary['throttle_message'],
            'errors' => [
                'email' => [$summary['throttle_message']]
            ],
            'retry_after' => $remainingTime,
            'rate_limit_info' => [
                'ip_attempts' => $summary['ip_attempts'],
                'ip_remaining' => $summary['ip_remaining'],
                'email_attempts' => $summary['email_attempts'],
                'email_remaining' => $summary['email_remaining'],
            ]
        ], 429);

        return $this->addRateLimitHeaders($response, $summary);
    }

    private function addRateLimitHeaders(Response $response, array $summary): Response
    {
        $headers = [
            'X-RateLimit-Limit-IP' => '5',
            'X-RateLimit-Remaining-IP' => (string) $summary['ip_remaining'],
            'X-RateLimit-Limit-Email' => '3',
            'X-RateLimit-Remaining-Email' => (string) $summary['email_remaining'],
            'Retry-After' => (string) $summary['remaining_time'],
        ];

        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }
}