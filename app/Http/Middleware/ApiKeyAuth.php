<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApiKeyAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get API key from request header
        $apiKey = $request->header('X-API-Key');

        // If no API key provided
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required',
                'error' => 'Missing API key in request header'
            ], 401);
        }

        // Validate API key against stored keys
        $validApiKeys = config('api.valid_keys', []);

        if (!in_array($apiKey, $validApiKeys)) {
            // Log unauthorized access attempt
            Log::warning('Unauthorized API access attempt', [
                'api_key' => $apiKey,
                'ip' => $request->ip(),
                'endpoint' => $request->fullUrl(),
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid API key',
                'error' => 'The provided API key is not authorized'
            ], 403);
        }

        // Optional: Check IP whitelist
        if (config('api.ip_whitelist_enabled', false)) {
            $allowedIps = config('api.allowed_ips', []);
            $clientIp = $request->ip();

            if (!in_array($clientIp, $allowedIps)) {
                Log::warning('API access from unauthorized IP', [
                    'ip' => $clientIp,
                    'api_key' => $apiKey,
                    'endpoint' => $request->fullUrl()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Access denied',
                    'error' => 'Your IP address is not authorized'
                ], 403);
            }
        }

        // Optional: Rate limiting check
        if (config('api.rate_limit.enabled', true)) {
            $rateLimitKey = 'api_rate_limit:' . $apiKey;
            $maxRequests = (int) config('api.rate_limit.max_requests', 1000);
            $perMinutes = (int) config('api.rate_limit.per_minutes', 60);

            $requestCount = (int) Cache::get($rateLimitKey, 0);

            if ($requestCount >= $maxRequests) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rate limit exceeded',
                    'error' => "Maximum {$maxRequests} requests per {$perMinutes} minutes"
                ], 429);
            }

            // Increment request count with proper integer value
            Cache::put($rateLimitKey, $requestCount + 1, now()->addMinutes($perMinutes));
        }

        // Log successful API access
        Log::info('API access granted', [
            'api_key' => substr($apiKey, 0, 10) . '...',
            'ip' => $request->ip(),
            'endpoint' => $request->path(),
            'method' => $request->method()
        ]);

        // Add API key info to request for later use
        $request->merge(['authenticated_api_key' => $apiKey]);

        return $next($request);
    }
}
