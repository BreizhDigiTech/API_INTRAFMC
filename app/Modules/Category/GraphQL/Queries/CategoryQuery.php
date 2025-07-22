<?php

namespace App\Modules\Category\GraphQL\Queries;

use App\Models\Category;
use App\Exceptions\CustomException;
use App\Services\GraphQLCacheService;
use Illuminate\Support\Facades\Gate;

class CategoryQuery
{
    protected $cacheService;

    public function __construct(GraphQLCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Récupère la liste des catégories.
     *
     * @return array
     * @throws CustomException
     */
    public function categories()
    {
        // Vérifie les permissions
        if (!Gate::allows('viewAny', Category::class)) {
            throw new CustomException('Accès refusé', 'Vous n\'avez pas les permissions nécessaires pour voir la liste des catégories.');
        }

        try {
            // Utilise le cache pour récupérer les catégories
            return $this->cacheService->getCachedCategories(function() {
                return Category::with('products')->get()->toArray();
            });
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de récupérer la liste des catégories.');
        }
    }

    /**
     * Récupère une catégorie spécifique.
     *
     * @param mixed $_
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function category($_, array $args)
    {
        try {
            // Utilise le cache pour récupérer la catégorie spécifique
            $category = $this->cacheService->getCachedCategory($args['id'], function() use ($args) {
                $category = Category::with('products')->find($args['id']);
                return $category ? $category->toArray() : null;
            });

            if (!$category) {
                throw new CustomException('Catégorie introuvable', 'Aucune catégorie n\'a été trouvée avec cet identifiant.');
            }

            // Vérifie les permissions sur l'objet Category
            $categoryModel = Category::find($args['id']);
            if (!Gate::allows('view', $categoryModel)) {
                throw new CustomException('Accès refusé', 'Vous n’avez pas les permissions nécessaires pour voir cette catégorie.');
            }

            return $category;
        } catch (xception $e) {
            throw new CustomException('Erreur interne', 'Impossible de récupérer la catégorie.');
        }
    }
}