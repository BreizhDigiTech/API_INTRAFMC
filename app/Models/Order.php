<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id', 
        'total', 
        'status'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec les produits de la commande
     * Table pivot: order_product avec colonnes: quantity, unit_price
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(ProductCBD::class, 'order_product', 'order_id', 'product_id')
            ->withPivot('quantity', 'unit_price')
            ->withTimestamps();
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
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

    /**
     * Scope pour filtrer par statut
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Accesseur pour calculer le nombre total d'articles
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->products->sum('pivot.quantity');
    }

    /**
     * Accesseur pour calculer le nombre de produits différents
     */
    public function getProductCountAttribute(): int
    {
        return $this->products->count();
    }

    /**
     * Accesseur pour formater le statut
     */
    public function getFormattedStatusAttribute(): string
    {
        $statusMap = [
            'pending' => 'En attente',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée',
        ];

        return $statusMap[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Relation avec les détails de commande (table pivot)
     */
    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    /**
     * Méthode pour charger les relations nécessaires selon le schéma réel
     */
    public static function withFullDetails()
    {
        return static::with([
            'user:id,name,email',
            'products' => function ($query) {
                $query->with('categories:id,name');
            }
        ]);
    }
}