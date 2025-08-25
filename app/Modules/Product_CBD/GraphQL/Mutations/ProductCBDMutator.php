<?php

namespace App\Modules\Product_CBD\GraphQL\Mutations;

use App\Exceptions\CustomException;
use App\Models\ProductCBD;
use App\Services\FileManagerService;
use App\Helpers\AuthHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ProductCBDMutator
{
    protected ?FileManagerService $fileManager = null;

    public function __construct(?FileManagerService $fileManager = null)
    {
        $this->fileManager = $fileManager;
    }

    protected function getFileManager(): FileManagerService
    {
        if (!$this->fileManager) {
            $this->fileManager = app(FileManagerService::class);
        }
        return $this->fileManager;
    }

    /**
     * Crée un nouveau produit CBD avec upload de fichiers.
     */
    public function create($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = AuthHelper::ensureAuthenticated();

        if (!Gate::allows('create', ProductCBD::class)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour creer un produit.');
        }

        return DB::transaction(function () use ($args) {
            $input = $args['input'];
            
            // Extraire les fichiers et relations avant la création
            $images = $input['images'] ?? [];
            $analysisImage = $input['analysis_image'] ?? ($input['analysis_file'] ?? null);
            $categoryIds = $input['category_ids'] ?? [];
            $categoryId = $input['category_id'] ?? null;
            
            // Supprimer les champs non-model pour la création
            unset($input['images'], $input['analysis_image'], $input['analysis_file'], $input['category_ids']);
            
            // Créer le produit
            $product = ProductCBD::create($input);
            
            // Attacher les catégories (priorité à category_ids puis category_id)
            if (!empty($categoryIds)) {
                $product->categories()->attach($categoryIds);
            } elseif ($categoryId) {
                $product->categories()->attach([$categoryId]);
            }
            
            // Gérer les images (enregistrées sous public/product_images/{id}/...)
            $uploadedImages = [];
            if (!empty($images)) {
                foreach ($images as $image) {
                    try {
                        if ($image instanceof UploadedFile) {
                            $dir = "product_images/{$product->id}";
                            $name = (string) Str::uuid() . '.' . strtolower($image->getClientOriginalExtension());
                            Storage::disk('public_web')->putFileAs($dir, $image, $name);
                            $uploadedImages[] = "$dir/$name";
                        }
                    } catch (\Exception $e) {
                        Log::error('Erreur upload image produit: ' . $e->getMessage());
                    }
                }
                $product->images = $uploadedImages;
            }
            
            // Gérer l'image d'analyse
            if ($analysisImage) {
                try {
                    if ($analysisImage instanceof UploadedFile) {
                        $dir = "product_analysis/{$product->id}";
                        $name = (string) Str::uuid() . '.' . strtolower($analysisImage->getClientOriginalExtension());
                        Storage::disk('public_web')->putFileAs($dir, $analysisImage, $name);
                        $analysisPath = "$dir/$name";
                        
                        // Stocker au champ canonical analysis_file
                        $product->analysis_file = $analysisPath;
                        $product->analysis_file_original_name = $analysisImage->getClientOriginalName();
                        $product->analysis_file_size = $analysisImage->getSize();
                        $product->analysis_file_mime_type = $analysisImage->getMimeType();
                    }
                } catch (\Exception $e) {
                    Log::error('Erreur upload image analyse: ' . $e->getMessage());
                }
            }
            
            $product->save();
            $product->load('categories');
            
            Log::info('createProduct returning', $product->toArray());
            return $product->fresh(['categories']);
        });
    }

    /**
     * Met à jour un produit CBD.
     */
    public function update($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = AuthHelper::ensureAuthenticated();

        return DB::transaction(function () use ($args) {
            $product = ProductCBD::findOrFail($args['id']);
            $input = $args['input'];
            
            // Extraire les fichiers et relations
            $images = $input['images'] ?? null;
            $analysisImage = $input['analysis_image'] ?? ($input['analysis_file'] ?? null);
            $categoryIds = $input['category_ids'] ?? null;
            $categoryId = $input['category_id'] ?? null;
            
            // Supprimer les champs non-model
            unset($input['images'], $input['analysis_image'], $input['analysis_file'], $input['category_ids']);
            
            // Mettre à jour les champs du produit
            $product->fill($input);
            
            // Mettre à jour les catégories (priorité à category_ids puis category_id)
            if ($categoryIds !== null) {
                $product->categories()->sync($categoryIds);
            } elseif ($categoryId !== null) {
                $product->categories()->sync([$categoryId]);
            }
            
            // Gérer les nouvelles images (public/product_images/{id}/...)
            if ($images !== null) {
                // Supprimer les anciennes images
                if (!empty($product->images)) {
                    foreach ($product->images as $oldImage) {
                        $this->getFileManager()->deleteFile($oldImage);
                    }
                }

                // Uploader les nouvelles images
                $uploadedImages = [];
                foreach ($images as $image) {
                    try {
                        if ($image instanceof UploadedFile) {
                            $dir = "product_images/{$product->id}";
                            $name = (string) Str::uuid() . '.' . strtolower($image->getClientOriginalExtension());
                            Storage::disk('public_web')->putFileAs($dir, $image, $name);
                            $uploadedImages[] = "$dir/$name";
                        }
                    } catch (\Exception $e) {
                        Log::error('Erreur upload image produit: ' . $e->getMessage());
                    }
                }
                $product->images = $uploadedImages;
            }
            
            // Gérer la nouvelle image d'analyse
            if ($analysisImage !== null) {
                // Supprimer l'ancienne image d'analyse
                if ($product->analysis_file) {
                    $this->getFileManager()->deleteFile($product->analysis_file);
                }
                
                try {
                    if ($analysisImage instanceof UploadedFile) {
                        $dir = "product_analysis/{$product->id}";
                        $name = (string) Str::uuid() . '.' . strtolower($analysisImage->getClientOriginalExtension());
                        Storage::disk('public_web')->putFileAs($dir, $analysisImage, $name);
                        $analysisPath = "$dir/$name";
                        
                        $product->analysis_file = $analysisPath;
                        $product->analysis_file_original_name = $analysisImage->getClientOriginalName();
                        $product->analysis_file_size = $analysisImage->getSize();
                        $product->analysis_file_mime_type = $analysisImage->getMimeType();
                    } else {
                        $product->analysis_file = null;
                    }
                } catch (\Exception $e) {
                    Log::error('Erreur upload image analyse: ' . $e->getMessage());
                    $product->analysis_file = null;
                }
            }
            
            $product->save();
            $product->load('categories');
            
            Log::info('updateProduct returning', $product->toArray());
            return $product->fresh(['categories']);
        });
    }

    /**
     * Supprime un produit CBD.
     */
    public function delete($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();
        $product = ProductCBD::findOrFail($args['id']);

        if (!Gate::allows('delete', $product)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour supprimer ce produit.');
        }

        try {
            return DB::transaction(function () use ($product) {
                // Supprimer les fichiers associés
                if (!empty($product->images)) {
                    foreach ($product->images as $image) {
                        $this->getFileManager()->deleteFile($image);
                    }
                }
                
                if ($product->analysis_file) {
                    $this->getFileManager()->deleteFile($product->analysis_file);
                }
                
                // Détacher les catégories
                $product->categories()->detach();
                
                // Supprimer le produit
                $product->delete();
                
                return [
                    'success' => true,
                    'message' => 'Produit supprime avec succes.'
                ];
            });
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de supprimer le produit.');
        }
    }

    /**
     * Upload d'images pour un produit existant.
     */
    public function uploadImages($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = AuthHelper::ensureAuthenticated();
        $productId = $args['productId'];
        $images = $args['images'];
        
        $product = ProductCBD::findOrFail($productId);
        
        if (!Gate::allows('update', $product)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour modifier ce produit.');
        }
        
        $results = [];
        
        foreach ($images as $image) {
            try {
                if ($image instanceof UploadedFile) {
                    $dir = "product_images/{$productId}";
                    $name = (string) Str::uuid() . '.' . strtolower($image->getClientOriginalExtension());
                    Storage::disk('public_web')->putFileAs($dir, $image, $name);
                    $path = "$dir/$name";

                    // Ajouter l'image au produit
                    $currentImages = $product->images ?? [];
                    $currentImages[] = $path;
                    $product->images = $currentImages;
                    $product->save();

                    $results[] = [
                        'success' => true,
                        'message' => 'Image uploadee avec succes',
                        'url' => asset($path),
                        'path' => $path
                    ];
                }
            } catch (\Exception $e) {
                $results[] = [
                    'success' => false,
                    'message' => 'Erreur lors de l\'upload: ' . $e->getMessage(),
                    'url' => null,
                    'path' => null
                ];
            }
        }
        
        return $results;
    }
}
