<?php

namespace App\Modules\Order\GraphQL\Resolvers;

use App\Models\ProductCBD;

class OrderProductPivotResolver
{
    /**
     * Resolve the pivot data for a ProductCBD returned via Order.products relation.
     *
     * @param ProductCBD $root
     * @return array<string,mixed>|null
     */
    public function resolve(ProductCBD $root): ?array
    {
        $pivot = $root->pivot ?? null;
        if (!$pivot) {
            return null;
        }

        return [
            'quantity' => (int) ($pivot->quantity ?? 0),
            'unit_price' => (float) ($pivot->unit_price ?? 0),
            'created_at' => $pivot->created_at ?? null,
            'updated_at' => $pivot->updated_at ?? null,
        ];
    }
}
