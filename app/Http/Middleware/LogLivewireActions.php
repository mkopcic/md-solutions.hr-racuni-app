<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogLivewireActions
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

        // Only log if this is a Livewire request
        if ($this->isLivewireRequest($request)) {
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);

            $payload = $request->json()->all();
            $component = $payload['components'][0]['calls'][0]['path'] ?? 'unknown';
            $method = $payload['components'][0]['calls'][0]['method'] ?? 'unknown';

            $logData = [
                'component' => $component,
                'method' => $method,
                'duration_ms' => $duration,
                'fingerprint' => $payload['fingerprint'] ?? null,
                'updates' => $payload['components'][0]['updates'] ?? [],
            ];

            activity('livewire_actions')
                ->withProperties($logData)
                ->log("Livewire action: {$component}::{$method} [{$duration}ms]");
        }

        return $response;
    }

    /**
     * Check if this is a Livewire request
     */
    private function isLivewireRequest(Request $request): bool
    {
        return $request->is('livewire/message/*') && $request->isMethod('POST');
    }
}
