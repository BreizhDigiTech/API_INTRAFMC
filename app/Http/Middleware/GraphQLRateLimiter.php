<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class GraphQLRateLimiter
{
    /**
     * Handle GraphQL rate limiting with different limits per user type
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->getRateLimitKey($request);
        $maxAttempts = $this->getMaxAttempts($request);
        $decayMinutes = 1;

        // Check if rate limit exceeded
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $this->logRateLimitExceeded($request, $key);
            
            return response()->json([
                'errors' => [[
                    'message' => 'Too many requests. Please slow down.',
                    'extensions' => [
                        'category' => 'rate-limit',
                        'retry_after' => RateLimiter::availableIn($key)
                    ]
                ]]
            ], 429);
        }

        // Increment rate limit counter
        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttempts - RateLimiter::attempts($key)));
        $response->headers->set('X-RateLimit-Reset', now()->addMinutes($decayMinutes)->timestamp);

        return $response;
    }

    /**
     * Get rate limit key based on user authentication
     */
    private function getRateLimitKey(Request $request): string
    {
        if (auth()->check()) {
            return 'graphql_user_' . auth()->id();
        }
        
        return 'graphql_ip_' . $request->ip();
    }

    /**
     * Get max attempts based on user role
     */
    private function getMaxAttempts(Request $request): int
    {
        if (!auth()->check()) {
            return 60; // Anonymous users: 60/minute
        }

        $user = auth()->user();
        
        if ($user->is_admin) {
            return 1000; // Admins: 1000/minute
        }

        return 200; // Authenticated users: 200/minute
    }

    /**
     * Log rate limit exceeded events for security monitoring
     */
    private function logRateLimitExceeded(Request $request, string $key): void
    {
        Log::channel('security')->warning('GraphQL rate limit exceeded', [
            'key' => $key,
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'query' => $request->input('query', 'No query'),
            'timestamp' => now()->toISOString()
        ]);
    }
}
