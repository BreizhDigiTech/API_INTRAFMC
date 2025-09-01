<?php

namespace App\Modules\Product_CBD\GraphQL\Queries;

use App\Models\ProductCBD;
use App\Helpers\AuthHelper;
use Illuminate\Support\Facades\Gate;

class ProductCBDQuery
{
    public function products($root, array $args)
    {
        AuthHelper::ensureAuthenticated();

        // Pas de restriction spécifique mentionnée dans les tests
        return ProductCBD::query()->orderByDesc('created_at');
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
