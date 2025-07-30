<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'total', 'status'];

    public function products()
    {
        return $this->belongsToMany(ProductCBD::class, 'order_product', 'order_id', 'product_id')
            ->withPivot('quantity', 'unit_price')
            ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour filtrer les commandes de l'utilisateur connecté
     */
    public function scopeForUser($query)
    {
        return $query->where('user_id', auth()->id());
    }
}