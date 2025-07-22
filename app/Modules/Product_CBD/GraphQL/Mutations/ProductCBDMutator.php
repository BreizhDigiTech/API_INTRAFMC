<?php

namespace App\Modules\Product_CBD\GraphQL\Mutations;

use App\Exceptions\CustomException;
use App\Modules\Product_CBD\Services\ProductCBDService;
use Illuminate\Support\Facades\Gate;
use App\Models\ProductCBD;
use App\Helpers\AuthHelper;
use Illuminate\Validation\Rule;

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
            $input = $args['input'];
            
            // Validate input
            $validator = validator($input, [
                'name' => ['required', 'string', 'max:255', Rule::unique('cbd_products', 'name')],
                'description' => ['nullable', 'string'],
                'price' => ['required', 'numeric', 'min:0'],
                'stock' => ['required', 'integer', 'min:0'],
                'category_id' => ['nullable', 'exists:categories,id'],
                'analysis_file' => ['nullable', 'string'],
                'images' => ['nullable', 'array']
            ]);

            if ($validator->fails()) {
                throw new \Illuminate\Validation\ValidationException($validator);
            }
            
            $product = $this->service->createProduct($input);
            return $product;
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
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
            $input = $args['input'];
            
            // Validate input for update
            $validator = validator($input, [
                'name' => ['sometimes', 'string', 'max:255', Rule::unique('cbd_products', 'name')->ignore($args['id'])],
                'description' => ['nullable', 'string'],
                'price' => ['sometimes', 'numeric', 'min:0'],
                'stock' => ['sometimes', 'integer', 'min:0'],
                'category_id' => ['nullable', 'exists:categories,id'],
                'analysis_file' => ['nullable', 'string'],
                'images' => ['nullable', 'array']
            ]);

            if ($validator->fails()) {
                throw new \Illuminate\Validation\ValidationException($validator);
            }
            
            $updatedProduct = $this->service->updateProduct($input, $args['id']);
            return $updatedProduct;
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
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
                'message' => 'Product deleted successfully'
            ];
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de supprimer le produit.');
        }
    }
}