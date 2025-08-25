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

class RemoveProductImages
{
    /**
     * Supprimer des images spécifiques d'un produit.
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): ProductCBD
    {
        $user = AuthHelper::ensureAuthenticated();
        
        $product = ProductCBD::findOrFail($args['product_id']);
        
        if (!Gate::allows('update', $product)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour modifier ce produit.');
        }
        
        $pathsToRemove = $args['image_paths'] ?? [];
        
        if (empty($pathsToRemove)) {
            return $product->fresh();
        }

        $currentImages = is_array($product->images) ? $product->images : [];
        $removedPaths = [];

        // Filtrer les images à garder
        $remainingImages = array_filter($currentImages, function($imagePath) use ($pathsToRemove, &$removedPaths) {
            if (in_array($imagePath, $pathsToRemove)) {
                $removedPaths[] = $imagePath;
                return false; // Retirer cette image
            }
            return true; // Garder cette image
        });

        // Supprimer physiquement les fichiers
        foreach ($removedPaths as $path) {
            $this->deleteImageFile($path);
        }

        // Mettre à jour le produit
        $product->images = array_values($remainingImages); // Réindexer le tableau
        $product->save();

        Log::info("Images supprimées du produit {$product->id}", [
            'user_id' => $user->id,
            'removed_paths' => $removedPaths,
            'remaining_count' => count($remainingImages)
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
