<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogLivewireRequests
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

        // Only log Livewire requests
        if (! $this->isLivewireRequest($request)) {
            return $response;
        }

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2);

        $this->logLivewireActivity($request, $response, $duration);

        return $response;
    }

    /**
     * Check if this is a Livewire request
     */
    private function isLivewireRequest(Request $request): bool
    {
        return $request->hasHeader('X-Livewire') ||
               str_contains($request->path(), 'livewire/message') ||
               str_contains($request->path(), 'livewire/upload-file');
    }

    /**
     * Log Livewire activity
     */
    private function logLivewireActivity(Request $request, Response $response, float $duration): void
    {
        $payload = $request->input();

        // Extract component info from Livewire payload
        $componentName = $payload['fingerprint']['name'] ?? 'Unknown';
        $method = $payload['updates'][0]['method'] ?? null;
        $componentId = $payload['fingerprint']['id'] ?? null;

        $logData = [
            'component' => $componentName,
            'method' => $method,
            'component_id' => $componentId,
            'ip' => $request->ip(),
            'duration_ms' => $duration,
            'status_code' => $response->getStatusCode(),
            'updates' => $this->getFilteredUpdates($payload['updates'] ?? []),
            'serverMemo' => $this->getFilteredServerMemo($payload['serverMemo'] ?? []),
        ];

        $logMessage = "Livewire: {$componentName}";
        if ($method) {
            $logMessage .= "::{$method}()";
        }
        $logMessage .= " [{$response->getStatusCode()}] {$duration}ms";

        activity('livewire_requests')
            ->withProperties($logData)
            ->log($logMessage);
    }

    /**
     * Filter sensitive data from updates
     */
    private function getFilteredUpdates(array $updates): array
    {
        $filtered = [];

        foreach ($updates as $update) {
            $filteredUpdate = $update;

            // Filter sensitive data from payload
            if (isset($filteredUpdate['payload']['value'])) {
                $filteredUpdate['payload']['value'] = $this->filterSensitiveData($filteredUpdate['payload']['value']);
            }

            $filtered[] = $filteredUpdate;
        }

        return $filtered;
    }

    /**
     * Filter sensitive data from server memo
     */
    private function getFilteredServerMemo(array $serverMemo): array
    {
        $filtered = $serverMemo;

        if (isset($filtered['data'])) {
            $filtered['data'] = $this->filterSensitiveData($filtered['data']);
        }

        return $filtered;
    }

    /**
     * Filter sensitive data
     */
    private function filterSensitiveData($data): mixed
    {
        if (is_array($data)) {
            $sensitiveFields = ['password', 'password_confirmation', 'token', 'csrf_token', 'remember_token'];

            foreach ($sensitiveFields as $field) {
                if (isset($data[$field])) {
                    $data[$field] = '[FILTERED]';
                }
            }

            // Recursively filter nested arrays
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $this->filterSensitiveData($value);
                }
            }
        }

        return $data;
    }
}
