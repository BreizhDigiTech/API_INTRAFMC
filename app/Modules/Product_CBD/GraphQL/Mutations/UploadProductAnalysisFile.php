<?php

namespace App\Modules\Product_CBD\GraphQL\Mutations;

use App\Models\ProductCBD;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadProductAnalysisFile
{
    public function __invoke($root, array $args): ProductCBD
    {
        $product = ProductCBD::findOrFail($args['product_id']);
        $file = $args['file'] ?? null;

        if ($file instanceof UploadedFile) {
            $dir = "cbd_products/{$product->id}/analysis";
            $name = uniqid('ana_') . '.' . $file->getClientOriginalExtension();
            Storage::disk('public')->putFileAs($dir, $file, $name);

            $product->analysis_file = "$dir/$name";
            $product->analysis_file_original_name = $file->getClientOriginalName();
            $product->analysis_file_size = (int) $file->getSize();
            $product->analysis_file_mime_type = $file->getClientMimeType();
            $product->save();
        }

        return $product->refresh();
    }
}
