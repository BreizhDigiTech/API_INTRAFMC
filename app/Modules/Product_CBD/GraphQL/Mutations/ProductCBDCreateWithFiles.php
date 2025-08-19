<?php

namespace App\Modules\Product_CBD\GraphQL\Mutations;

use App\Models\ProductCBD;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductCBDCreateWithFiles
{
    public function __invoke($root, array $args): ProductCBD
    {
        return DB::transaction(function () use ($args) {
            $product = new ProductCBD();
            $product->name = $args['name'];
            $product->description = $args['description'] ?? null;
            $product->price = $args['price'];
            $product->stock = $args['stock'] ?? 0;
            $product->category_id = $args['category_id'] ?? null;
            $product->image_metadata = $args['image_metadata'] ?? null;
            $product->save();

            // catégories many-to-many en option
            if (!empty($args['category_ids'])) {
                $product->categories()->sync($args['category_ids']);
            }

            // Images
            $images = [];
            foreach (($args['images'] ?? []) as $file) {
                if ($file instanceof UploadedFile) {
                    $dir = "cbd_products/{$product->id}/images";
                    $name = uniqid('img_') . '.' . $file->getClientOriginalExtension();
                    Storage::disk('public')->putFileAs($dir, $file, $name);
                    $images[] = "$dir/$name";
                }
            }
            if ($images) {
                $product->images = $images;
            }

            // Fichier d'analyse
            if (!empty($args['analysis_file']) && $args['analysis_file'] instanceof UploadedFile) {
                $file = $args['analysis_file'];
                $dir = "cbd_products/{$product->id}/analysis";
                $name = uniqid('ana_') . '.' . $file->getClientOriginalExtension();
                Storage::disk('public')->putFileAs($dir, $file, $name);

                $product->analysis_file = "$dir/$name";
                $product->analysis_file_original_name = $file->getClientOriginalName();
                $product->analysis_file_size = (int) $file->getSize();
                $product->analysis_file_mime_type = $file->getClientMimeType();
            }

            $product->save();
            return $product->refresh();
        });
    }
}
