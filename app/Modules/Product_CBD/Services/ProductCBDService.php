<?php

namespace App\Modules\Product_CBD\Services;

use App\Models\ProductCBD;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductCBDService
{
    public function getProducts(array $args)
    {
        $query = ProductCBD::query();

        // Pagination
        $pagination = $query->paginate(
            $perPage = $args['per_page'] ?? 10,
            $columns = ['*'],
            $pageName = 'page',
            $page = $args['page'] ?? 1
        );

        return [
            'data' => $pagination->items(),
            'pagination' => [
                'total' => $pagination->total(),
                'per_page' => $pagination->perPage(),
                'current_page' => $pagination->currentPage(),
                'last_page' => $pagination->lastPage(),
            ],
        ];
    }

    public function createProduct(array $data)
    {
        if (isset($data['analysis_file'])) {
            $data['analysis_file'] = $this->storeAnalysisFile($data['analysis_file']);
        }

        return ProductCBD::create($data);
    }

    public function updateProduct(array $data, $id)
    {
        $product = ProductCBD::findOrFail($id);

        if (isset($data['analysis_file'])) {
            if ($product->analysis_file) {
                Storage::disk('analysis')->delete($product->analysis_file);
            }

            $data['analysis_file'] = $this->storeAnalysisFile($data['analysis_file']);
        }

        $product->update($data);
        return $product;
    }

    public function deleteProduct($id)
    {
        $product = ProductCBD::findOrFail($id);
        
        // Supprime le fichier d'analyse s'il existe
        if ($product->analysis_file) {
            Storage::disk('analysis')->delete($product->analysis_file);
        }
        
        $product->delete();
        return ['success' => true, 'message' => 'Produit supprimé avec succès.'];
    }

    public function getProductById($id)
    {
        return ProductCBD::findOrFail($id);
    }

    private function storeAnalysisFile($file)
    {
        return $file->store('', 'analysis');
    }
}