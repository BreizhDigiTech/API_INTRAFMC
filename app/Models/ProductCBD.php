<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Services\FileManagerService;

class ProductCBD extends Model
{
    use HasFactory;
    
    protected $table = 'cbd_products'; // Nom de la table en base de données

    protected $fillable = [
        'name',
        'description',
        'price',
        'images', // Array de chemins d'images
        'stock',
        'analysis_file', // Chemin du fichier d'analyse
        'category_id', // Référence à la catégorie
    ];

    protected $casts = [
        'images' => 'array', // Cast JSON en tableau PHP
        'price' => 'decimal:2',
    ];

    protected $appends = [
        'images_urls',
        'analysis_file_url'
    ];

    protected static function boot()
    {
        parent::boot();
        
        // Supprime automatiquement les fichiers lors de la suppression du produit
        static::deleting(function ($product) {
            $fileManager = app(FileManagerService::class);
            
            // Suppression des images
            if ($product->images && is_array($product->images)) {
                $fileManager->deleteProductImages($product->images);
            }
            
            // Suppression du fichier d'analyse
            if ($product->analysis_file) {
                $fileManager->deleteAnalysisFile($product->analysis_file);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product', 'product_id', 'category_id');
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'product_supplier', 'product_id', 'supplier_id');
    }

    public function getCartQuantity()
    {
        return $this->pivot ? $this->pivot->quantity : null;
    }

    /**
     * URLs des images avec toutes les variantes
     */
    public function getImagesUrlsAttribute(): array
    {
        if (!$this->images || !is_array($this->images)) {
            return [];
        }
        
        $fileManager = app(FileManagerService::class);
        $imageUrls = [];
        
        foreach ($this->images as $imagePath) {
            $imageUrls[] = [
                'original' => $fileManager->getProductImageUrl($imagePath),
                'thumbnail' => $fileManager->getProductImageUrl($imagePath, 'thumbnail'),
                'medium' => $fileManager->getProductImageUrl($imagePath, 'medium'),
                'large' => $fileManager->getProductImageUrl($imagePath, 'large'),
                'path' => $imagePath
            ];
        }
        
        return $imageUrls;
    }

    /**
     * URL du fichier d'analyse
     */
    public function getAnalysisFileUrlAttribute(): ?string
    {
        if (!$this->analysis_file) {
            return null;
        }
        
        $fileManager = app(FileManagerService::class);
        return $fileManager->getAnalysisFileUrl($this->analysis_file);
    }

    /**
     * Ajouter une image au produit
     */
    public function addImage(string $imagePath): void
    {
        $images = $this->images ?? [];
        $images[] = $imagePath;
        $this->images = $images;
        $this->save();
    }

    /**
     * Supprimer une image du produit
     */
    public function removeImage(string $imagePath): void
    {
        if (!$this->images) {
            return;
        }
        
        $images = array_filter($this->images, function($path) use ($imagePath) {
            return $path !== $imagePath;
        });
        
        $this->images = array_values($images);
        $this->save();
        
        // Suppression physique du fichier
        $fileManager = app(FileManagerService::class);
        $fileManager->deleteProductImages([$imagePath]);
    }

    /**
     * Définir le fichier d'analyse
     */
    public function setAnalysisFile(string $filePath): void
    {
        // Suppression de l'ancien fichier
        if ($this->analysis_file) {
            $fileManager = app(FileManagerService::class);
            $fileManager->deleteAnalysisFile($this->analysis_file);
        }
        
        $this->analysis_file = $filePath;
        $this->save();
    }
}