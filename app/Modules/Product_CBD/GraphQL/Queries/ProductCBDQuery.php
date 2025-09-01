<?php

namespace App\Modules\Product_CBD\GraphQL\Queries;

use App\Models\ProductCBD;
use App\Helpers\AuthHelper;
use Illuminate\Support\Facades\Gate;

class ProductCBDQuery
{
    public function productsCBD($root, array $args)
    {
        AuthHelper::ensureAuthenticated();

        // 🔥 DEBUG: Log pour voir si le résolveur est appelé
        \Log::info('🔥 CUSTOM RESOLVER productsCBD appelé', [
            'args' => $args,
            'page' => $args['page'] ?? 'undefined',
            'first' => $args['first'] ?? 'undefined'
        ]);

        $first = $args['first'] ?? 20;
        $page = $args['page'] ?? 1;
        $offset = ($page - 1) * $first;
        
        $query = ProductCBD::query()->orderByDesc('created_at');
        
        // Appliquer les filtres
        if (isset($args['name']) && !empty($args['name'])) {
            $query->where('name', 'like', '%' . $args['name'] . '%');
        }
        
        if (isset($args['category_id']) && !empty($args['category_id'])) {
            $query->where('category_id', $args['category_id']);
        }
        
        $total = $query->count();
        $data = $query->skip($offset)->take($first)->get();
        
        $result = [
            'data' => $data,
            'paginatorInfo' => [
                'currentPage' => $page,
                'hasMorePages' => ($page * $first) < $total,
                'total' => $total,
                'perPage' => $first,
                'lastPage' => ceil($total / $first),
            ]
        ];

        // 🔥 DEBUG: Log du résultat
        \Log::info('🔥 CUSTOM RESOLVER résultat', [
            'currentPage' => $page,
            'total' => $total,
            'count_data' => $data->count(),
            'first_product_id' => $data->first()?->id,
            'last_product_id' => $data->last()?->id
        ]);
        
        return $result;
    }

    public function product($root, array $args)
    {
        AuthHelper::ensureAuthenticated();

        $product = ProductCBD::find($args['id']);
        if (!$product) {
            return null;
        }

        if (!Gate::allows('view', $product)) {
            return null;
        }

        return $product;
    }

    /**
     * Récupérer TOUS les produits sans pagination ni limitation
     */
    public function allProducts($root, array $args)
    {
        AuthHelper::ensureAuthenticated();

        $query = ProductCBD::query();

        // Filtres optionnels
        if (isset($args['name']) && !empty($args['name'])) {
            $query->where('name', 'like', '%' . $args['name'] . '%');
        }

        if (isset($args['category_id']) && !empty($args['category_id'])) {
            $query->where('category_id', $args['category_id']);
        }

        // AUCUNE LIMITATION - récupère TOUS les produits
        return $query->orderByDesc('created_at')->get();
    }
}
