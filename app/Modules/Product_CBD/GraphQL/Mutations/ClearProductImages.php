<?php

namespace App\Modules\Product_CBD\GraphQL\Mutations;

use App\Models\ProductCBD;
use App\Helpers\AuthHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use App\Exceptions\CustomException;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ClearProductImages
{
    /**
     * Supprimer toutes les images d'un produit.
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): ProductCBD
    {
        $user = AuthHelper::ensureAuthenticated();
        
        $product = ProductCBD::findOrFail($args['product_id']);
        
        if (!Gate::allows('update', $product)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour modifier ce produit.');
        }
        
        $currentImages = is_array($product->images) ? $product->images : [];
        $imageCount = count($currentImages);

        // Supprimer tous les fichiers physiques
        foreach ($currentImages as $path) {
            $this->deleteImageFile($path);
        }

        // Vider le champ images
        $product->images = [];
        $product->save();

        Log::info("Toutes les images supprimées du produit {$product->id}", [
            'user_id' => $user->id,
            'deleted_count' => $imageCount,
            'paths' => $currentImages
        ]);

        return $product->fresh();
    }

    protected function deleteImageFile(string $path): void
    {
        try {
            // Supprimer du disque public_web (public/product_images/...)
            if (Storage::disk('public_web')->exists($path)) {
                Storage::disk('public_web')->delete($path);
                Log::info("Fichier supprimé du disque public_web: {$path}");
            }
            
            // Supprimer du disque public (storage/app/public/...)
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                Log::info("Fichier supprimé du disque public: {$path}");
            }
            
            // Supprimer du disque product_images si nécessaire
            if (Storage::disk('product_images')->exists($path)) {
                Storage::disk('product_images')->delete($path);
                Log::info("Fichier supprimé du disque product_images: {$path}");
            }
        } catch (\Exception $e) {
            Log::warning("Erreur lors de la suppression du fichier: {$path}", [
                'error' => $e->getMessage()
            ]);
        }
    }
}
