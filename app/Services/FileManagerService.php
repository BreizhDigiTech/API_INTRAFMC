<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class FileManagerService
{
    // Types de fichiers autorisés
    const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
    const ALLOWED_ANALYSIS_EXTENSIONS = ['pdf', 'doc', 'docx', 'txt'];
    
    // Tailles maximales (en bytes)
    const MAX_IMAGE_SIZE = 5 * 1024 * 1024; // 5MB
    const MAX_ANALYSIS_SIZE = 10 * 1024 * 1024; // 10MB
    
    // Variantes d'images
    const IMAGE_SIZES = [
        'thumbnail' => ['width' => 150, 'height' => 150],
        'medium' => ['width' => 400, 'height' => 400],
        'large' => ['width' => 800, 'height' => 600],
    ];

    /**
     * Upload et traitement d'une image produit
     */
    public function storeProductImage(UploadedFile $file, int $productId): array
    {
        $this->validateImage($file);
        
        $folder = 'products/' . $productId;
        $filename = $this->generateUniqueFilename($file);
        $path = $folder . '/' . $filename;
        
        // Stockage de l'image originale
        Storage::disk('product_images')->put($path, file_get_contents($file));
        
        // Génération des variantes
        $variants = $this->generateImageVariants($file, 'product_images', $path);
        
        return [
            'original' => $path,
            'variants' => $variants,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'original_name' => $file->getClientOriginalName(),
        ];
    }

    /**
     * Upload d'un fichier d'analyse
     */
    public function storeAnalysisFile(UploadedFile $file, int $productId): array
    {
        $this->validateAnalysisFile($file);
        
        $folder = 'products/' . $productId;
        $filename = $this->generateUniqueFilename($file);
        $path = $folder . '/' . $filename;
        
        Storage::disk('analysis')->put($path, file_get_contents($file));
        
        return [
            'path' => $path,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'original_name' => $file->getClientOriginalName(),
        ];
    }

    /**
     * Suppression des images d'un produit
     */
    public function deleteProductImages(array $imagePaths): void
    {
        foreach ($imagePaths as $imagePath) {
            // Suppression image originale
            Storage::disk('product_images')->delete($imagePath);
            
            // Suppression des variantes
            foreach (array_keys(self::IMAGE_SIZES) as $size) {
                $variantPath = $this->getVariantPath($imagePath, $size);
                Storage::disk('product_images')->delete($variantPath);
            }
        }
    }

    /**
     * Suppression d'un fichier d'analyse
     */
    public function deleteAnalysisFile(string $filePath): void
    {
        Storage::disk('analysis')->delete($filePath);
    }

    /**
     * Génération d'URL sécurisée pour une image
     */
    public function getProductImageUrl(string $imagePath, string $size = 'original'): string
    {
        $path = $size === 'original' ? $imagePath : $this->getVariantPath($imagePath, $size);
        
        return route('api.files.product-image', [
            'path' => base64_encode($path)
        ]);
    }

    /**
     * Génération d'URL sécurisée pour un fichier d'analyse
     */
    public function getAnalysisFileUrl(string $filePath): string
    {
        return route('api.files.analysis', [
            'path' => base64_encode($filePath)
        ]);
    }

    /**
     * Validation d'une image
     */
    private function validateImage(UploadedFile $file): void
    {
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS)) {
            throw new \InvalidArgumentException(
                'Format d\'image non autorisé. Formats acceptés: ' . implode(', ', self::ALLOWED_IMAGE_EXTENSIONS)
            );
        }
        
        if ($file->getSize() > self::MAX_IMAGE_SIZE) {
            throw new \InvalidArgumentException(
                'Image trop volumineuse. Taille maximale: ' . (self::MAX_IMAGE_SIZE / 1024 / 1024) . 'MB'
            );
        }
        
        if (!$file->isValid()) {
            throw new \InvalidArgumentException('Fichier image corrompu ou invalide');
        }

        // Validation que c'est vraiment une image
        $imageInfo = getimagesize($file->getPathname());
        if ($imageInfo === false) {
            throw new \InvalidArgumentException('Le fichier n\'est pas une image valide');
        }
    }

    /**
     * Validation d'un fichier d'analyse
     */
    private function validateAnalysisFile(UploadedFile $file): void
    {
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, self::ALLOWED_ANALYSIS_EXTENSIONS)) {
            throw new \InvalidArgumentException(
                'Format de fichier d\'analyse non autorisé. Formats acceptés: ' . implode(', ', self::ALLOWED_ANALYSIS_EXTENSIONS)
            );
        }
        
        if ($file->getSize() > self::MAX_ANALYSIS_SIZE) {
            throw new \InvalidArgumentException(
                'Fichier d\'analyse trop volumineux. Taille maximale: ' . (self::MAX_ANALYSIS_SIZE / 1024 / 1024) . 'MB'
            );
        }
        
        if (!$file->isValid()) {
            throw new \InvalidArgumentException('Fichier d\'analyse corrompu ou invalide');
        }
    }

    /**
     * Génération d'un nom de fichier unique
     */
    private function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $uuid = Str::random(8);
        
        return $timestamp . '_' . $uuid . '.' . $extension;
    }

    /**
     * Génération des variantes d'images
     */
    private function generateImageVariants(UploadedFile $file, string $disk, string $originalPath): array
    {
        $variants = [];
        
        foreach (self::IMAGE_SIZES as $sizeName => $dimensions) {
            try {
                $variantPath = $this->getVariantPath($originalPath, $sizeName);
                
                $manager = new ImageManager(new Driver());
                $image = $manager->read($file->getPathname())
                    ->cover($dimensions['width'], $dimensions['height'])
                    ->toWebp(85); // Conversion en WebP avec compression
                
                Storage::disk($disk)->put($variantPath, $image->toString());
                $variants[$sizeName] = $variantPath;
                
            } catch (\Exception $e) {
                \Log::warning("Erreur génération variante {$sizeName}", [
                    'error' => $e->getMessage(),
                    'path' => $originalPath
                ]);
            }
        }
        
        return $variants;
    }

    /**
     * Génération du chemin d'une variante
     */
    private function getVariantPath(string $originalPath, string $size): string
    {
        $pathInfo = pathinfo($originalPath);
        return $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $size . '.webp';
    }

    /**
     * Nettoyage des fichiers orphelins
     */
    public function cleanupOrphanedFiles(): int
    {
        $cleanedCount = 0;
        
        // Nettoyage des images produits orphelines
        $productImages = Storage::disk('product_images')->allFiles();
        $existingProducts = \App\Models\ProductCBD::pluck('id')->toArray();
        
        foreach ($productImages as $imagePath) {
            $productId = $this->extractProductIdFromPath($imagePath);
            if ($productId && !in_array($productId, $existingProducts)) {
                Storage::disk('product_images')->delete($imagePath);
                $cleanedCount++;
            }
        }
        
        // Nettoyage des fichiers d'analyse orphelins
        $analysisFiles = Storage::disk('analysis')->allFiles();
        
        foreach ($analysisFiles as $filePath) {
            $productId = $this->extractProductIdFromPath($filePath);
            if ($productId && !in_array($productId, $existingProducts)) {
                Storage::disk('analysis')->delete($filePath);
                $cleanedCount++;
            }
        }
        
        return $cleanedCount;
    }

    /**
     * Extraction de l'ID produit depuis un chemin de fichier
     */
    private function extractProductIdFromPath(string $path): ?int
    {
        if (preg_match('/products\/(\d+)\//', $path, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }
}
