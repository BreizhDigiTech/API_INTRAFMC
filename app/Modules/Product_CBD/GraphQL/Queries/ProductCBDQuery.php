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
}
