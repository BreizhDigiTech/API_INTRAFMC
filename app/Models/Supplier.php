<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name', 'email', 'phone', 'address', 'website', 
        'contact_person', 'description'
    ];

    public function products()
    {
        return $this->belongsToMany(ProductCBD::class, 'product_supplier', 'supplier_id', 'product_id');
    }
}