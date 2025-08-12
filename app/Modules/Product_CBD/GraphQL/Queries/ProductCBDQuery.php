<?php

namespace App\Modules\Product_CBD\GraphQL\Queries;

use App\Models\ProductCBD;
use App\Helpers\AuthHelper;
use Illuminate\Support\Facades\Gate;

class ProductCBDQuery
{
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
