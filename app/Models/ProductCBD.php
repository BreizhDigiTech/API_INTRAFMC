<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ProductCBD extends Model
{
    use HasFactory;

    protected $table = 'cbd_products';

    protected $fillable = [
        'name',
        'description',
        'price',
        'images',
        'image_metadata',
        'stock',
        'analysis_file',
        'analysis_file_original_name',
        'analysis_file_size',
        'analysis_file_mime_type',
        'category_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'images' => 'array',
        'image_metadata' => 'array',
        'analysis_file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product', 'product_id', 'category_id');
    }

    // Accesseurs pour les URLs complètes
    public function getImageUrlsAttribute(): array
    {
        if (empty($this->images)) {
            return [];
        }

        return array_map(function ($path) {
            if (!$path) return null;
            $path = str_replace('\\', '/', $path);

            // absolute URLs
            if (preg_match('#^https?://#i', $path)) {
                return $path;
            }

            // Images gérées via disk 'public' (storage/app/public)
            if (Str::startsWith($path, ['cbd_products/', '/cbd_products/'])) {
                return Storage::disk('public')->url(ltrim($path, '/'));
            }

            // Images placées directement sous public/ (ex: public/product_images/...)
            if (Str::startsWith($path, ['product_images/', '/product_images/'])) {
                return URL::to('/' . ltrim($path, '/'));
            }

            // Fallback: considérer comme relatif au webroot
            return URL::to('/' . ltrim($path, '/'));
        }, $this->images);
    }

    // Backward-compat accessor alias used in some tests
    public function getImagesUrlsAttribute(): array
    {
        return $this->image_urls;
    }

    public function getAnalysisFileUrlAttribute(): ?string
    {
        if (empty($this->analysis_file)) {
            return null;
        }
        $path = str_replace('\\', '/', $this->analysis_file);

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        if (Str::startsWith($path, ['cbd_products/', '/cbd_products/'])) {
            return Storage::disk('public')->url(ltrim($path, '/'));
        }

        if (Str::startsWith($path, [
            'product_images/', '/product_images/',
            'product_analysis/', '/product_analysis/'
        ])) {
            return URL::to('/' . ltrim($path, '/'));
        }

        return URL::to('/' . ltrim($path, '/'));
    }

    // Helpers expected by tests
    public function addImage(string $path): void
    {
    $images = $this->images ?? [];
        $images[] = $path;
        $this->images = array_values(array_unique($images));
        $this->save();
    }

    public function removeImage(string $path): void
    {
        // Delete file (+ variants) from storage
        try {
            app(\App\Services\FileManagerService::class)->deleteProductImages([$path]);
        } catch (\Throwable $e) {
            // ignore
        }

        // Update model state
        $images = collect($this->images ?? [])->filter(fn($p) => $p !== $path)->values()->all();
        $this->images = !empty($images) ? $images : null;
        $this->save();
    }

    public function setAnalysisFile(string $path): void
    {
        $this->analysis_file = $path;
        $this->save();
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (ProductCBD $product) {
            // Detach relations
            try { $product->categories()->detach(); } catch (\Throwable $e) {}

            // Delete associated files (images + variants) and analysis file
            try {
                $images = $product->images ?? [];
                if (!empty($images)) {
                    $fm = app(\App\Services\FileManagerService::class);
                    $fm->deleteProductImages($images);
                }
            } catch (\Throwable $e) {}

            try {
                if (!empty($product->analysis_file)) {
                    app(\App\Services\FileManagerService::class)->deleteFile($product->analysis_file);
                }
            } catch (\Throwable $e) {}
        });
    }
}
