<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductCBD extends Model
{
    use HasFactory;
    
    protected $table = 'cbd_products'; // Nom de la table en base de données

    protected $fillable = [
        'name',
        'description',
        'price',
        'images',
        'stock',
        'analysis_file', // Fichier d'analyse
        'category_id', // Référence à la catégorie
    ];

    protected $casts = [
        'images' => 'array', // Cast JSON en tableau PHP
    ];

    protected static function boot()
    {
        parent::boot();
        
        // Supprime automatiquement le fichier d'analyse lors de la suppression du produit
        static::deleting(function ($product) {
            if ($product->analysis_file && Storage::disk('analysis')->exists($product->analysis_file)) {
                Storage::disk('analysis')->delete($product->analysis_file);
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

    public function getAnalysisFileUrlAttribute()
    {
        if (!$this->analysis_file) {
            return null;
        }
        
        // Vérifier si le fichier existe avant de générer l'URL
        if (Storage::disk('analysis')->exists($this->analysis_file)) {
            return Storage::disk('analysis')->url($this->analysis_file);
        }
        
        return null;
    }
}