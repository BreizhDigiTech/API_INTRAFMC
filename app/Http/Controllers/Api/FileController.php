<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /**
     * Servir une image de produit
     */
    public function getProductImage(Request $request, string $encodedPath)
    {
        $path = base64_decode($encodedPath);
        
        // Vérification de l'existence du fichier
        if (!Storage::disk('product_images')->exists($path)) {
            abort(404, 'Image non trouvée');
        }
        
        // Contrôle d'accès (optionnel pour les images produits)
        // Images produits généralement publiques
        
        return $this->streamFile('product_images', $path);
    }

    /**
     * Servir un fichier d'analyse (sécurisé)
     */
    public function getAnalysisFile(Request $request, string $encodedPath)
    {
        $path = base64_decode($encodedPath);
        
        // Vérification de l'existence du fichier
        if (!Storage::disk('analysis')->exists($path)) {
            abort(404, 'Fichier d\'analyse non trouvé');
        }
        
        // Contrôle d'accès - authentification requise
        if (!auth()->check()) {
            abort(401, 'Authentification requise');
        }
        
        // Vérification des permissions (admin ou propriétaire du produit)
        $productId = $this->extractProductIdFromPath($path);
        if ($productId && !$this->canAccessProduct($productId)) {
            abort(403, 'Accès refusé');
        }
        
        return $this->streamFile('analysis', $path);
    }

    /**
     * Servir un avatar utilisateur
     */
    public function getAvatar(Request $request, string $encodedPath)
    {
        $path = base64_decode($encodedPath);
        
        if (!Storage::disk('avatars')->exists($path)) {
            abort(404, 'Avatar non trouvé');
        }
        
        // Contrôle d'accès - utilisateur authentifié
        if (!auth()->check()) {
            abort(401, 'Authentification requise');
        }
        
        return $this->streamFile('avatars', $path);
    }

    /**
     * Stream d'un fichier avec les headers appropriés
     */
    private function streamFile(string $disk, string $path): StreamedResponse
    {
        $file = Storage::disk($disk)->get($path);
        $mimeType = Storage::disk($disk)->mimeType($path);
        $size = Storage::disk($disk)->size($path);
        $lastModified = Storage::disk($disk)->lastModified($path);
        
        $headers = [
            'Content-Type' => $mimeType,
            'Content-Length' => $size,
            'Last-Modified' => gmdate('D, d M Y H:i:s \G\M\T', $lastModified),
            'Cache-Control' => 'public, max-age=31536000', // 1 an de cache
            'Expires' => gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000),
        ];
        
        // Headers spécifiques selon le type de fichier
        if (str_starts_with($mimeType, 'image/')) {
            $headers['Content-Disposition'] = 'inline';
        } else {
            $headers['Content-Disposition'] = 'attachment; filename="' . basename($path) . '"';
        }
        
        return response()->stream(function () use ($file) {
            echo $file;
        }, 200, $headers);
    }

    /**
     * Extraction de l'ID produit depuis un chemin
     */
    private function extractProductIdFromPath(string $path): ?int
    {
        if (preg_match('/products\/(\d+)\//', $path, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    /**
     * Vérification des permissions d'accès à un produit
     */
    private function canAccessProduct(int $productId): bool
    {
        $user = auth()->user();
        
        // Admin a accès à tout
        if ($user->role === 'admin') {
            return true;
        }
        
        // Utilisateur standard : vérifier si le produit existe et est accessible
        $product = \App\Models\ProductCBD::find($productId);
        if (!$product) {
            return false;
        }
        
        // Logique métier : qui peut accéder aux fichiers d'analyse ?
        // Par exemple, seulement les admins
        return $user->role === 'admin';
    }

    /**
     * Upload d'une image de produit (API)
     */
    public function uploadProductImage(Request $request)
    {
        $request->validate([
            'file' => 'required|file|image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max
            'product_id' => 'required|exists:cbd_products,id'
        ]);
        
        try {
            $fileManager = app(\App\Services\FileManagerService::class);
            $result = $fileManager->storeProductImage(
                $request->file('file'),
                $request->product_id
            );
            
            return response()->json([
                'success' => true,
                'data' => [
                    'path' => $result['original'],
                    'urls' => [
                        'original' => $fileManager->getProductImageUrl($result['original']),
                        'thumbnail' => $fileManager->getProductImageUrl($result['original'], 'thumbnail'),
                        'medium' => $fileManager->getProductImageUrl($result['original'], 'medium'),
                        'large' => $fileManager->getProductImageUrl($result['original'], 'large'),
                    ],
                    'size' => $result['size'],
                    'mime_type' => $result['mime_type']
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Upload d'un fichier d'analyse
     */
    public function uploadAnalysisFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,txt|max:10240', // 10MB max
            'product_id' => 'required|exists:cbd_products,id'
        ]);
        
        // Vérification des permissions
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Seuls les administrateurs peuvent uploader des fichiers d\'analyse');
        }
        
        try {
            $fileManager = app(\App\Services\FileManagerService::class);
            $result = $fileManager->storeAnalysisFile(
                $request->file('file'),
                $request->product_id
            );
            
            return response()->json([
                'success' => true,
                'data' => [
                    'path' => $result['path'],
                    'url' => $fileManager->getAnalysisFileUrl($result['path']),
                    'size' => $result['size'],
                    'mime_type' => $result['mime_type'],
                    'original_name' => $result['original_name']
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
