<?php

namespace App\Modules\Supplier\Services;

use App\Models\Supplier;
use App\Models\ProductCBD;

class SupplierService
{
    public function createSupplier($data)
    {
        return Supplier::create($data);
    }

    public function attachSupplierToProduct($supplier_id, $product_id)
    {
        $supplier = Supplier::findOrFail($supplier_id);
        $supplier->products()->syncWithoutDetaching([$product_id]);
        return $supplier->load('products');
    }

    public function detachSupplierFromProduct($supplier_id, $product_id)
    {
        $supplier = Supplier::findOrFail($supplier_id);
        $supplier->products()->detach($product_id);
        return $supplier->load('products');
    }
}