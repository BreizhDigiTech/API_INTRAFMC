<?php

// Architecture GraphQL Pure - Pas de routes API REST
// Toutes les opérations passent par GraphQL endpoint: /graphql

// Documentation:
// - Uploads: Utilisez les mutations GraphQL uploadProductImages, uploadProductAnalysisFile
// - Accès aux fichiers: Les URLs sont générées automatiquement dans les réponses GraphQL
// - Authentification: JWT via header Authorization dans GraphQL

// Application du rate limiting au niveau de la route GraphQL
Route::middleware(['throttle:graphql'])->group(function () {
    // Les routes GraphQL sont automatiquement enregistrées par Lighthouse
});

// Configuration du rate limiting pour GraphQL
Route::pattern('throttle:graphql', 'throttle:60,1');
