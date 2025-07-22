<?php

namespace App\Modules\Product_CBD\GraphQL\Mutations;

use App\Exceptions\CustomException;
use App\Modules\Product_CBD\Services\ProductCBDService;
use Illuminate\Support\Facades\Gate;
use App\Models\ProductCBD;
use App\Helpers\AuthHelper;

class ProductCBDMutator
{
    protected $service;

    public function __construct()
    {
        $this->service = app(ProductCBDService::class);
    }

    /**
     * Crée un nouveau produit CBD.
     *
     * @param mixed $root
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function createProduct($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        if (!Gate::allows('create', ProductCBD::class)) {
            throw new CustomException('Accès refusé', 'Vous n’avez pas les permissions nécessaires pour créer un produit.');
        }

        try {
            $product = $this->service->createProduct($args);
            // Retourne directement le produit tel qu'attendu par le schéma GraphQL
            return $product;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de créer le produit.');
        }
    }

    /**
     * Met à jour un produit CBD existant.
     *
     * @param mixed $root
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function updateProduct($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();
        $product = ProductCBD::findOrFail($args['id']);

        if (!Gate::allows('update', $product)) {
            throw new CustomException('Accès refusé', 'Vous n’avez pas les permissions nécessaires pour modifier ce produit.');
        }

        try {
            $updatedProduct = $this->service->updateProduct($args, $args['id']);
            // Retourne directement le produit mis à jour tel qu'attendu par le schéma GraphQL
            return $updatedProduct;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de modifier le produit.');
        }
    }

    /**
     * Supprime un produit CBD.
     *
     * @param mixed $root
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function deleteProduct($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();
        $product = ProductCBD::findOrFail($args['id']);

        if (!Gate::allows('delete', $product)) {
            throw new CustomException('Accès refusé', 'Vous n’avez pas les permissions nécessaires pour supprimer ce produit.');
        }

        try {
            $this->service->deleteProduct($args['id']);
            return [
                'success' => true,
                'message' => 'Produit CBD supprimé avec succès.',
            ];
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de supprimer le produit.');
        }
    }
}