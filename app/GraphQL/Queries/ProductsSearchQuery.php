<?php

namespace App\GraphQL\Queries;

use App\Models\ProductCBD;

class ProductsSearchQuery
{
    public function __invoke($root, array $args)
    {
        $query = ProductCBD::with(['categories:id,name']);

        // Recherche textuelle
        if (!empty($args['search'])) {
            $search = $args['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Filtre par catégorie
        if (!empty($args['category'])) {
            $query->whereHas('categories', function ($q) use ($args) {
                $q->where('categories.id', $args['category']);
            });
        }

        // Filtre par prix minimum
        if (isset($args['minPrice'])) {
            $query->where('price', '>=', $args['minPrice']);
        }

        // Filtre par prix maximum
        if (isset($args['maxPrice'])) {
            $query->where('price', '<=', $args['maxPrice']);
        }

        // Filtre par stock
        if (isset($args['inStock'])) {
            if ($args['inStock']) {
                $query->where('stock', '>', 0);
            } else {
                $query->where('stock', '<=', 0);
            }
        }

        return $query->orderBy('created_at', 'desc');
    }
}
