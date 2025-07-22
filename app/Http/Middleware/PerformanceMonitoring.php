<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PerformanceMonitoring
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        // Activation du logging des requêtes pour cette requête
        DB::enableQueryLog();

        $response = $next($request);

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $executionTime = ($endTime - $startTime) * 1000; // en millisecondes
        $memoryUsage = $endMemory - $startMemory;
        $queryCount = count(DB::getQueryLog());

        // Log des métriques de performance
        $performanceData = [
            'url' => $request->url(),
            'method' => $request->method(),
            'execution_time_ms' => round($executionTime, 2),
            'memory_usage_mb' => round($memoryUsage / 1024 / 1024, 2),
            'query_count' => $queryCount,
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        // Log d'alerte si la performance est dégradée
        if ($executionTime > 1000) { // > 1 seconde
            Log::warning('Slow request detected', $performanceData);
        } elseif ($executionTime > 500) { // > 500ms
            Log::info('Performance monitoring', $performanceData);
        }

        // Ajout des headers de performance
        $response->headers->set('X-Execution-Time', $executionTime);
        $response->headers->set('X-Memory-Usage', $memoryUsage);
        $response->headers->set('X-Query-Count', $queryCount);

        return $response;
    }
}
