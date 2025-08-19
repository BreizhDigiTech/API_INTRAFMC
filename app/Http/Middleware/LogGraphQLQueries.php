<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogGraphQLQueries
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);
        
        $response = $next($request);
        
        $duration = microtime(true) - $start;
        
        // Log les requêtes lentes (>1 seconde)
        if ($duration > 1) {
            Log::warning('Slow GraphQL query detected', [
                'duration' => round($duration, 3),
                'query' => $this->getQueryFromRequest($request),
                'variables' => $request->input('variables'),
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString(),
            ]);
        }
        
        // Log toutes les requêtes en mode debug
        if (config('app.debug')) {
            Log::info('GraphQL query executed', [
                'duration' => round($duration, 3),
                'query_type' => $this->getQueryType($request),
                'user_id' => auth()->id(),
            ]);
        }
        
        return $response;
    }
    
    /**
     * Extraire la requête du request
     */
    private function getQueryFromRequest(Request $request): ?string
    {
        $query = $request->input('query');
        
        // Limiter la taille du log pour éviter des logs trop volumineux
        if ($query && strlen($query) > 500) {
            return substr($query, 0, 500) . '...';
        }
        
        return $query;
    }
    
    /**
     * Déterminer le type de requête (query/mutation)
     */
    private function getQueryType(Request $request): string
    {
        $query = $request->input('query', '');
        
        if (strpos($query, 'mutation') !== false) {
            return 'mutation';
        } elseif (strpos($query, 'subscription') !== false) {
            return 'subscription';
        } else {
            return 'query';
        }
    }
}
