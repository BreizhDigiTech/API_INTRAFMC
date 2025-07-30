<?php

namespace App\Modules\Product_CBD\GraphQL\Mutations;

use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Gate;
use App\Models\ProductCBD;
use App\Helpers\AuthHelper;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProductCBDMutator
{
    /**
     * Crée un nouveau produit CBD.
     *
     * @param mixed $root
     * @param array $args
     * @return ProductCBD
     * @throws CustomException
     */
    public function createProduct($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        if (!Gate::allows('create', ProductCBD::class)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour creer un produit.');
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
                throw \Illuminate\Validation\ValidationException::withMessages($validator->errors()->toArray());
            }

            // Create product directly
            $product = ProductCBD::create([
                'name' => $input['name'],
                'description' => $input['description'] ?? null,
                'price' => $input['price'],
                'stock' => $input['stock'],
                'category_id' => $input['category_id'] ?? null,
                'analysis_file' => $input['analysis_file'] ?? null,
                'images' => $input['images'] ?? []
            ]);

            return $product;
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de creer le produit.');
        }
    }

    /**
     * Met à jour un produit CBD existant.
     *
     * @param mixed $root
     * @param array $args
     * @return ProductCBD
     * @throws CustomException
     */
    public function updateProduct($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();
        $product = ProductCBD::findOrFail($args['id']);

        if (!Gate::allows('update', $product)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour modifier ce produit.');
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
                throw \Illuminate\Validation\ValidationException::withMessages($validator->errors()->toArray());
            }

            // Update product directly
            $product->update(array_filter($input, function($value) {
                return $value !== null;
            }));

            return $product->fresh();
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
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour supprimer ce produit.');
        }

        try {
            // Delete analysis file if exists
            if ($product->analysis_file && Storage::exists($product->analysis_file)) {
                Storage::delete($product->analysis_file);
            }

            $product->delete();

            return [
                'success' => true,
                'message' => 'Produit supprime avec succes.'
            ];
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de supprimer le produit.');
        }
    }
}