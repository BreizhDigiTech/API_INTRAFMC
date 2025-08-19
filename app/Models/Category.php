<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'description'];

    public function products(): HasMany
    {
        return $this->hasMany(ProductCBD::class, 'category_id');
    }

    public function allProducts(): BelongsToMany
    {
        return $this->belongsToMany(ProductCBD::class, 'category_product', 'category_id', 'product_id');
    }
}
