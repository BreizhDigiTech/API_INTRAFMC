<?php

namespace App\Modules\Category\Services;

use App\Models\Category;
use App\Models\ProductCBD;

class CategoryService
{
    public function createCategory($name, $description = null)
    {
        return Category::create([
            'name' => $name,
            'description' => $description
        ]);
    }

    public function updateCategory($id, $name, $description = null)
    {
        $category = Category::findOrFail($id);
        $updateData = ['name' => $name];
        if ($description !== null) {
            $updateData['description'] = $description;
        }
        $category->update($updateData);
        return $category;
    }

    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return ['success' => true, 'message' => 'Catégorie supprimée avec succès.'];
    }

    public function attachCategoryToProduct($category_id, $product_id)
    {
        $category = Category::findOrFail($category_id);
        $product = ProductCBD::findOrFail($product_id);
        $category->products()->syncWithoutDetaching([$product->id]);
        return $category->load('products');
    }

    public function detachCategoryFromProduct($category_id, $product_id)
    {
        $category = Category::findOrFail($category_id);
        $product = ProductCBD::findOrFail($product_id);
        $category->products()->detach($product->id);
        return $category->load('products');
    }
}