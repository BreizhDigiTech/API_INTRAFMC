<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name'];

    public function products()
    {
        return $this->belongsToMany(ProductCBD::class, 'category_product', 'category_id', 'product_id');
    }
}
