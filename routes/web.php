<?php

use App\Http\Controllers\Api\FileController;

// Routes pour servir les fichiers de manière sécurisée
Route::get('/api/files/product-image/{path}', [FileController::class, 'getProductImage'])
    ->name('api.files.product-image');

Route::get('/api/files/analysis/{path}', [FileController::class, 'getAnalysisFile'])
    ->name('api.files.analysis')
    ->middleware('auth:api');

Route::get('/api/files/avatar/{path}', [FileController::class, 'getAvatar'])
    ->name('api.files.avatar')
    ->middleware('auth:api');

// Routes d'upload (API)
Route::middleware(['auth:api'])->group(function () {
    Route::post('/api/upload/product-image', [FileController::class, 'uploadProductImage']);
    Route::post('/api/upload/analysis', [FileController::class, 'uploadAnalysisFile']);
});

// Fichier web.php minimal pour API GraphQL
// Pas de routes web nécessaires - utilisez GraphQL via /graphql
