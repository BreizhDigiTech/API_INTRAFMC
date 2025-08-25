<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductCBD;

class NormalizeImagePaths extends Command
{
    protected $signature = 'intrafmc:normalize-image-paths {--dry-run : Ne fait qu\'afficher les changements sans écrire}';
    protected $description = 'Normalise les chemins d\'images et fichiers d\'analyse en BDD vers product_images/{id}/... et product_analysis/{id}/...';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $updated = 0;

        ProductCBD::chunkById(200, function ($products) use (&$updated, $dry) {
            foreach ($products as $product) {
                $changed = false;

                // Images
                $images = (array) ($product->images ?? []);
                $newImages = [];
                foreach ($images as $p) {
                    if (!is_string($p) || $p === '') continue;
                        $np = str_replace('\\\\', '/', $p);
                    // Déjà normalisé
                    if (str_starts_with($np, 'product_images/')) {
                        $newImages[] = $np;
                        continue;
                    }
                    // Pattern "<id>/<file>" => prefixer product_images/
                    if (preg_match('#^(\\d+)/(.+)$#', $np, $m)) {
                        $np = 'product_images/' . $m[1] . '/' . $m[2];
                        $changed = true;
                    }
                    $newImages[] = $np;
                }

                // Fichier d'analyse
                $analysis = $product->analysis_file;
                if (is_string($analysis) && $analysis !== '') {
                        $na = str_replace('\\\\', '/', $analysis);
                    if (!str_starts_with($na, 'product_analysis/')) {
                        if (preg_match('#^(\\d+)/(.+)$#', $na, $m)) {
                            $na = 'product_analysis/' . $m[1] . '/' . $m[2];
                            $changed = true;
                        }
                    }
                } else {
                    $na = $analysis;
                }

                if ($changed) {
                    $this->line(sprintf('Produit #%d: normalisation', $product->id));
                    if ($dry) {
                        $this->line('  images: ' . json_encode($product->images) . ' => ' . json_encode($newImages));
                        if ($analysis) {
                            $this->line('  analysis_file: ' . $analysis . ' => ' . $na);
                        }
                    } else {
                        $product->images = $newImages;
                        $product->analysis_file = $na;
                        $product->save();
                        $updated++;
                    }
                }
            }
        });

        $this->info($dry ? 'Simulation terminée.' : ("Normalisation terminée. Produits mis à jour: $updated"));
        return Command::SUCCESS;
    }
}
