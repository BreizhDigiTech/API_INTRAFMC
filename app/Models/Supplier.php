<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name', 'email', 'phone'];

    public function products()
    {
        return $this->belongsToMany(ProductCBD::class, 'product_supplier', 'supplier_id', 'product_id');
    }
}