<?php

return [
    'default' => env('CACHE_STORE', 'redis'),

    'stores' => [
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'database' => [
            'driver' => 'database',
            'table' => env('DB_CACHE_TABLE', 'cache'),
            'connection' => env('DB_CACHE_CONNECTION'),
            'lock_connection' => env('DB_CACHE_LOCK_CONNECTION'),
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
            'lock_path' => storage_path('framework/cache/data'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('CACHE_REDIS_CONNECTION', 'cache'),
            'lock_connection' => env('CACHE_REDIS_LOCK_CONNECTION', 'default'),
        ],

        // Cache spécialisé pour GraphQL
        'graphql' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'prefix' => 'graphql_cache',
        ],

        // Cache pour les données statiques
        'static_data' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'prefix' => 'static_data',
        ],

        // Cache pour les sessions
        'sessions' => [
            'driver' => 'redis',
            'connection' => 'sessions',
            'prefix' => 'session',
        ],
    ],

    'prefix' => env('CACHE_PREFIX', 'intrafmc_cache'),

    // Configuration des TTL par type de données
    'ttl' => [
        'categories' => 3600, // 1 heure
        'products' => 1800,   // 30 minutes
        'suppliers' => 7200,  // 2 heures
        'user_profile' => 900, // 15 minutes
        'graphql_schema' => 86400, // 24 heures
        'permissions' => 1800, // 30 minutes
    ],
];
