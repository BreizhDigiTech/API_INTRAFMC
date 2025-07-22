<?php

namespace App\Modules\Category\GraphQL\Mutations;

use App\Exceptions\CustomException;
use App\Modules\Category\Services\CategoryService;
use Illuminate\Support\Facades\Gate;
use App\Models\Category;
use App\Helpers\AuthHelper;

class CategoryMutator
{
    protected $service;

    public function __construct()
    {
        $this->service = new CategoryService();
    }

    /**
     * Crée une nouvelle catégorie.
     *
     * @param mixed $_
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function createCategory($_, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        if (!Gate::allows('create', Category::class)) {
            throw new CustomException('Accès refusé', 'Vous n’avez pas les permissions nécessaires pour créer une catégorie.');
        }

        try {
            $category = $this->service->createCategory($args['name']);
            return $category;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de créer la catégorie.');
        }
    }

    /**
     * Met à jour une catégorie existante.
     *
     * @param mixed $_
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function updateCategory($_, array $args)
    {
        $category = Category::findOrFail($args['id']);

        if (!Gate::allows('update', $category)) {
            throw new CustomException('Accès refusé', 'Vous n’avez pas les permissions nécessaires pour modifier cette catégorie.');
        }

        try {
            $updatedCategory = $this->service->updateCategory($args['id'], $args['name']);
            return $updatedCategory;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de modifier la catégorie.');
        }
    }

    /**
     * Supprime une catégorie.
     *
     * @param mixed $_
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function deleteCategory($_, array $args)
    {
        $category = Category::findOrFail($args['id']);

        if (!Gate::allows('delete', $category)) {
            throw new CustomException('Accès refusé', 'Vous n’avez pas les permissions nécessaires pour supprimer cette catégorie.');
        }

        try {
            $this->service->deleteCategory($args['id']);
            return true;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de supprimer la catégorie.');
        }
    }

    /**
     * Attache une catégorie à un produit.
     *
     * @param mixed $_
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function attachCategoryToProduct($_, array $args)
    {
        $category = Category::findOrFail($args['category_id']);

        if (!Gate::allows('update', $category)) {
            throw new CustomException('Accès refusé', 'Vous n’avez pas les permissions nécessaires pour attacher une catégorie à un produit.');
        }

        try {
            $category = $this->service->attachCategoryToProduct($args['category_id'], $args['product_id']);
            return $category;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible d’attacher la catégorie au produit.');
        }
    }

    /**
     * Détache une catégorie d’un produit.
     *
     * @param mixed $_
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function detachCategoryFromProduct($_, array $args)
    {
        $category = Category::findOrFail($args['category_id']);

        if (!Gate::allows('update', $category)) {
            throw new CustomException('Accès refusé', 'Vous n’avez pas les permissions nécessaires pour détacher une catégorie d’un produit.');
        }

        try {
            $category = $this->service->detachCategoryFromProduct($args['category_id'], $args['product_id']);
            return $category;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de détacher la catégorie du produit.');
        }
    }
}