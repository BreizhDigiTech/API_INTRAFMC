<?php

namespace App\Modules\Category\Services;

use App\Models\Category;
use App\Models\ProductCBD;

class CategoryService
{
    public function createCategory($name)
    {
        return Category::create(['name' => $name]);
    }

    public function updateCategory($id, $name)
    {
        $category = Category::findOrFail($id);
        $category->update(['name' => $name]);
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