<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;

class FileManagerService
{
    private $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Store a product image with variants and validations.
     * Returns array with original path, variants, size and mime_type.
     */
    public function storeProductImage(UploadedFile $file, $productId = null): array
    {
        // Validate extension
        $ext = strtolower($file->getClientOriginalExtension());
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed, true)) {
            throw new \InvalidArgumentException("Format d'image non autorisé");
        }

        // Validate size (<= 5MB)
        $sizeBytes = (int) $file->getSize();
        if ($sizeBytes > 5 * 1024 * 1024) {
            throw new \InvalidArgumentException('Image trop volumineuse');
        }

        // Base filename
        $uuid = (string) Str::uuid();
        $baseDir = $productId ? trim((string) $productId, '/').'/' : '';
        $originalPath = $baseDir . $uuid . '.' . $ext;
        $thumbPath = $baseDir . $uuid . '_thumbnail.' . $ext;
        $mediumPath = $baseDir . $uuid . '_medium.' . $ext;

        // Create optimized variants
        $image = $this->imageManager->read($file->getRealPath());

        // Original: optimized but original size limit
        $originalData = $this->optimizeInterventionImage(clone $image, 1600, 1200, $ext);
        Storage::disk('product_images')->put($originalPath, $originalData);

        // Thumbnail
        $thumbData = $this->optimizeInterventionImage(clone $image, 300, 300, $ext);
        Storage::disk('product_images')->put($thumbPath, $thumbData);

        // Medium
        $mediumData = $this->optimizeInterventionImage(clone $image, 800, 600, $ext);
        Storage::disk('product_images')->put($mediumPath, $mediumData);

        return [
            'original' => $originalPath,
            'variants' => [
                $thumbPath,
                $mediumPath,
            ],
            'size' => $sizeBytes,
            'mime_type' => $file->getMimeType() ?? 'image/'.$ext,
        ];
    }

    /** Backward-compat upload method returning the original path only. */
    public function uploadProductImage(UploadedFile $file, $productId = null): string
    {
        $result = $this->storeProductImage($file, $productId);
        return $result['original'];
    }

    /**
     * Store an analysis file (PDF/any) under analysis disk.
     */
    public function storeAnalysisFile(UploadedFile $file, $productId = null): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = (string) Str::uuid() . '.' . $ext;
        $baseDir = $productId ? trim((string) $productId, '/').'/' : '';
        $path = $baseDir . $filename;

        Storage::disk('analysis')->putFileAs($baseDir, $file, $filename);

        return [
            'path' => $path,
            'size' => (int) $file->getSize(),
            'original_name' => $file->getClientOriginalName(),
        ];
    }

    /** Backward-compat upload method returning the stored path only. */
    public function uploadAnalysisImage(UploadedFile $file, $productId = null): string
    {
        $result = $this->storeAnalysisFile($file, $productId);
        return $result['path'];
    }

    /** Delete a single file from public disk (backward compat). */
    public function deleteFile($path)
    {
        if (!$path) return false;
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        // Try product_images and analysis disks as well
        if (Storage::disk('product_images')->exists($path)) {
            return Storage::disk('product_images')->delete($path);
        }
        if (Storage::disk('analysis')->exists($path)) {
            return Storage::disk('analysis')->delete($path);
        }
        // If path looks like public web path, delete there too
        if (str_starts_with($path, 'product_images/') || str_starts_with($path, 'product_analysis/')) {
            if (Storage::disk('public_web')->exists($path)) {
                return Storage::disk('public_web')->delete($path);
            }
        }
        return false;
    }

    /**
     * Delete original images and their variants based on naming convention.
     */
    public function deleteProductImages(array $originalPaths): void
    {
        foreach ($originalPaths as $original) {
            if (!$original) continue;
            $this->silentDelete('product_images', $original);

            // Delete common variants if exist
            $dotPos = strrpos($original, '.');
            if ($dotPos !== false) {
                $base = substr($original, 0, $dotPos);
                $ext = substr($original, $dotPos + 1);
                $this->silentDelete('product_images', $base . '_thumbnail.' . $ext);
                $this->silentDelete('product_images', $base . '_medium.' . $ext);
            }
        }
    }

    /**
     * Optimize image for web
     */
    private function optimizeImage(UploadedFile $file, $maxWidth = 800, $maxHeight = 600)
    {
        $image = $this->imageManager->read($file->getRealPath());
        return $this->optimizeInterventionImage($image, $maxWidth, $maxHeight, strtolower($file->getClientOriginalExtension()));
    }

    private function optimizeInterventionImage($image, int $maxWidth, int $maxHeight, string $extension): string
    {
        if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
            $image->scaleDown($maxWidth, $maxHeight);
        }
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return $image->toJpeg(85)->toString();
            case 'png':
                return $image->toPng()->toString();
            case 'webp':
                return $image->toWebp(85)->toString();
            default:
                return $image->toJpeg(85)->toString();
        }
    }

    /**
     * Get file URL
     */
    public function getFileUrl($path)
    {
        return Storage::disk('public')->url($path);
    }

    /**
     * Generate URL for product images using Laravel's storage system.
     * Replaced API gateway with direct storage URL generation.
     */
    public function getProductImageUrl(string $originalPath, ?string $variant = null): string
    {
        // For product images, use the storage disk URL
        if (Str::startsWith($originalPath, ['product_images/', '/product_images/'])) {
            return asset(ltrim($originalPath, '/'));
        }
        
        // Fallback to storage disk
        return Storage::disk('public')->url($originalPath);
    }

    /**
     * Check if file exists
     */
    public function fileExists($path)
    {
        // Check in specific disks first
        if (Storage::disk('product_images')->exists($path)) return true;
        if (Storage::disk('analysis')->exists($path)) return true;
        return Storage::disk('public')->exists($path);
    }

    /**
     * Remove product images that are no longer referenced by any ProductCBD.
     * Returns number of deleted files.
     */
    public function cleanupOrphanedFiles(): int
    {
        $referenced = collect(\App\Models\ProductCBD::query()->pluck('images')->all())
            ->filter()
            ->flatMap(function ($arr) { return (array) $arr; })
            ->values()
            ->all();
        $referencedSet = array_flip($referenced);

        $allFiles = Storage::disk('product_images')->allFiles();
        $deleted = 0;
        foreach ($allFiles as $file) {
            if (!isset($referencedSet[$file])) {
                if (Storage::disk('product_images')->delete($file)) {
                    $deleted++;
                }
            }
        }
        return $deleted;
    }

    private function silentDelete(string $disk, string $path): void
    {
        try {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        } catch (\Throwable $e) {
            // no-op
        }
    }
}
