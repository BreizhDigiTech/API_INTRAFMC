<?php

namespace App\Modules\Supplier\Services;

use App\Models\Supplier;
use App\Models\ProductCBD;

class SupplierService
{
    public function createSupplier($data)
    {
        $payload = [
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'website' => $data['website'] ?? null,
            'contact_person' => $data['contact_person'] ?? null,
            'description' => $data['description'] ?? null,
        ];
        return Supplier::create($payload);
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

    public function updateSupplier($id, $data)
    {
        $supplier = Supplier::findOrFail($id);
        
        $payload = array_filter([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'website' => $data['website'] ?? null,
            'contact_person' => $data['contact_person'] ?? null,
            'description' => $data['description'] ?? null,
        ], function($value) {
            return $value !== null;
        });
        
        $supplier->update($payload);
        return $supplier->fresh();
    }

    public function deleteSupplier($id)
    {
        $supplier = Supplier::findOrFail($id);
        
        // Détacher tous les produits avant suppression
        $supplier->products()->detach();
        
        return $supplier->delete();
    }
}