<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogHttpRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2); // u milisekundama

        // Skip logging for certain routes to avoid noise
        if ($this->shouldSkipLogging($request)) {
            return $response;
        }

        // Prepare log data
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => $request->route() ? $request->route()->getName() : null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'parameters' => $this->getFilteredParameters($request),
        ];

        // Log the HTTP request
        activity('http_requests')
            ->withProperties($logData)
            ->log("HTTP {$request->method()} {$request->path()} [{$response->getStatusCode()}] {$duration}ms");

        return $response;
    }

    /**
     * Determine if request should be skipped from logging
     */
    private function shouldSkipLogging(Request $request): bool
    {
        $skipPatterns = [
            '/livewire/message/*',
            '/up',
            '/favicon.ico',
            '*.css',
            '*.js',
            '*.png',
            '*.jpg',
            '*.gif',
            '*.svg',
            '*.ico',
            '*.woff*',
            '*.ttf',
            '/build/*',
            '/vendor/*',
            '/_debugbar/*',
            '/logs/*',
            '/telescope/*',
            '/_profiler/*',
            '/clockwork/*',
        ];

        $path = $request->path();

        // Skip debugbar and development tools
        if (str_contains($path, '_debugbar') || str_contains($path, 'debugbar')) {
            return true;
        }

        foreach ($skipPatterns as $pattern) {
            if (fnmatch($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get filtered request parameters (remove sensitive data)
     */
    private function getFilteredParameters(Request $request): array
    {
        $parameters = $request->all();

        // Remove sensitive fields
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'csrf_token'];

        foreach ($sensitiveFields as $field) {
            if (isset($parameters[$field])) {
                $parameters[$field] = '[FILTERED]';
            }
        }

        return $parameters;
    }
}
