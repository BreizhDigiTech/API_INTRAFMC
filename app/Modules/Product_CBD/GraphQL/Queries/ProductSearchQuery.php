<?php

namespace App\Modules\Product_CBD\GraphQL\Queries;

use App\Models\ProductCBD;
use Illuminate\Database\Eloquent\Builder;

class ProductSearchQuery
{
    /**
     * Recherche avancée de produits avec critères multiples
     */
    public function search($_, array $args)
    {
        $query = ProductCBD::with(['categories']);
        
        // Recherche textuelle sur nom et description (uniquement si query n'est pas vide)
        if (isset($args['query']) && !empty($args['query']) && trim($args['query']) !== '') {
            $searchTerm = '%' . $args['query'] . '%';
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm);
            });
        }
        
        // Filtrage par catégorie
        if (isset($args['category_id']) && !empty($args['category_id'])) {
            $query->whereHas('categories', function (Builder $q) use ($args) {
                $q->where('categories.id', $args['category_id']);
            });
        }
        
        // Filtrage par prix
        if (isset($args['min_price']) && is_numeric($args['min_price'])) {
            $query->where('price', '>=', $args['min_price']);
        }
        
        if (isset($args['max_price']) && is_numeric($args['max_price'])) {
            $query->where('price', '<=', $args['max_price']);
        }
        
        // Filtrage par stock disponible
        if (isset($args['in_stock']) && $args['in_stock'] === true) {
            $query->where('stock', '>', 0);
        }
        
        // Tri par pertinence (nom exact en premier, puis alphabétique, puis par date de création)
        if (isset($args['query']) && !empty($args['query']) && trim($args['query']) !== '') {
            $query->orderByRaw("
                CASE 
                    WHEN name = ? THEN 1
                    WHEN name LIKE ? THEN 2
                    ELSE 3
                END, name ASC, created_at DESC
            ", [$args['query'], $args['query'] . '%']);
        } else {
            // Pas de recherche textuelle : trier par date de création (plus récent en premier)
            $query->orderByDesc('created_at');
        }
        
        // Pour les tests et queries simples, retourner directement les résultats
        return $query->get();
    }
    
    /**
     * Recherche rapide par nom (fonction helper)
     */
    public function searchByName($_, array $args)
    {
        $name = $args['name'];
        
        return ProductCBD::with(['categories'])
            ->where('name', 'like', '%' . $name . '%')
            ->orderByRaw("
                CASE 
                    WHEN name = ? THEN 1
                    WHEN name LIKE ? THEN 2
                    ELSE 3
                END, name ASC, created_at DESC
            ", [$name, $name . '%'])
            ->limit($args['limit'] ?? 10)
            ->get();
    }
    
    /**
     * Suggestions de recherche basées sur les noms de produits
     */
    public function suggestions($_, array $args)
    {
        $query = $args['query'] ?? '';
        
        if (strlen($query) < 2) {
            return [];
        }
        
        return ProductCBD::select('name')
            ->where('name', 'like', $query . '%')
            ->distinct()
            ->orderByDesc('created_at') // Suggestions des produits les plus récents d'abord
            ->limit(5)
            ->pluck('name')
            ->toArray();
    }
}
