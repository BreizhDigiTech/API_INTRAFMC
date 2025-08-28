<?php

namespace App\GraphQL\Queries;

use App\Models\Category;

class CategoriesListQuery
{
    public function __invoke()
    {
        return Category::withCount('products')
            ->select(['id', 'name', 'description'])
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => \Illuminate\Support\Str::slug($category->name), // Générer le slug
                    'description' => $category->description,
                    'products_count' => $category->products_count,
                ];
            });
    }
}
