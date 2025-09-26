<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditLogger
{
    private array $sensitiveRoutes = [
        'kids.*',
        'responsibles.*',
        'checklists.*',
        'professionals.*',
        'users.*',
        'analysis.*',
        'export.*',
    ];

    private array $sensitiveActions = [
        'show',
        'edit',
        'update',
        'destroy',
        'export',
        'download',
    ];

    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $response = $next($request);
        $endTime = microtime(true);

        if ($this->shouldAuditRequest($request)) {
            $this->logRequest($request, $response, $endTime - $startTime);
        }

        return $response;
    }

    private function shouldAuditRequest(Request $request): bool
    {
        if (!auth()->check()) {
            return $this->isAuthAttempt($request);
        }

        return $this->isSensitiveRoute($request) || $this->hasSensitiveParameters($request);
    }

    private function isSensitiveRoute(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        if (!$routeName) {
            return false;
        }

        foreach ($this->sensitiveRoutes as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }

    private function isAuthAttempt(Request $request): bool
    {
        return Str::contains($request->path(), ['login', 'register', 'password']);
    }

    private function hasSensitiveParameters(Request $request): bool
    {
        $route = $request->route();

        if (!$route) {
            return false;
        }

        $parameters = $route->parameters();

        return !empty(array_intersect_key($parameters, array_flip(['kid', 'responsible', 'checklist', 'user'])));
    }

    private function logRequest(Request $request, $response, float $duration): void
    {
        $route = $request->route();
        $routeName = $route?->getName() ?? 'unknown';
        $action = $this->extractAction($request);

        $context = [
            'route' => $routeName,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'duration' => round($duration * 1000, 2),
            'status_code' => $response->getStatusCode(),
            'params' => $this->getSafeParameters($request),
        ];

        if ($response->getStatusCode() >= 400) {
            $context['error'] = true;
        }

        $resourceInfo = $this->extractResourceInfo($request);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'resource' => $resourceInfo['resource'] ?? 'Request',
            'resource_id' => $resourceInfo['resource_id'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'data_before' => null,
            'data_after' => null,
            'context' => json_encode($context),
        ]);
    }

    private function extractAction(Request $request): string
    {
        $method = strtoupper($request->method());
        $route = $request->route();

        if (!$route) {
            return $method;
        }

        $action = $route->getActionName();

        if (Str::contains($action, '@')) {
            $parts = explode('@', $action);
            $controllerMethod = end($parts);

            return strtoupper($controllerMethod);
        }

        return $method;
    }

    private function extractResourceInfo(Request $request): array
    {
        $route = $request->route();

        if (!$route) {
            return [];
        }

        $parameters = $route->parameters();

        foreach (['kid', 'responsible', 'checklist', 'user', 'professional'] as $resource) {
            if (isset($parameters[$resource])) {
                return [
                    'resource' => ucfirst($resource),
                    'resource_id' => is_object($parameters[$resource])
                        ? $parameters[$resource]->getKey()
                        : $parameters[$resource],
                ];
            }
        }

        return [];
    }

    private function getSafeParameters(Request $request): array
    {
        $parameters = $request->all();
        $sensitiveKeys = ['password', 'password_confirmation', 'current_password', 'token'];

        foreach ($sensitiveKeys as $key) {
            if (isset($parameters[$key])) {
                $parameters[$key] = '[REDACTED]';
            }
        }

        return $parameters;
    }
}
