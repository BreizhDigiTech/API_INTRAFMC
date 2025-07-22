<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FileManagerService;

class CleanupOrphanedFiles extends Command
{
    protected $signature = 'files:cleanup {--dry-run : Show what would be deleted without actually deleting}';
    protected $description = 'Clean up orphaned files (images and analysis files)';

    public function handle()
    {
        $fileManager = app(FileManagerService::class);
        $dryRun = $this->option('dry-run');

        $this->info('🧹 Nettoyage des fichiers orphelins...');

        if ($dryRun) {
            $this->warn('⚠️  Mode simulation - aucun fichier ne sera supprimé');
        }

        try {
            if (!$dryRun) {
                $cleanedCount = $fileManager->cleanupOrphanedFiles();
                $this->info("✅ {$cleanedCount} fichiers orphelins supprimés");
            } else {
                // Mode simulation
                $this->simulateCleanup();
            }

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors du nettoyage: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function simulateCleanup(): void
    {
        $this->info('📋 Simulation du nettoyage:');
        
        // Simulation pour les images produits
        $productImages = \Storage::disk('product_images')->allFiles();
        $existingProducts = \App\Models\ProductCBD::pluck('id')->toArray();
        $orphanedImages = 0;

        foreach ($productImages as $imagePath) {
            $productId = $this->extractProductIdFromPath($imagePath);
            if ($productId && !in_array($productId, $existingProducts)) {
                $orphanedImages++;
                $this->line("  🗑️  Image orpheline: {$imagePath}");
            }
        }

        // Simulation pour les fichiers d'analyse
        $analysisFiles = \Storage::disk('analysis')->allFiles();
        $orphanedAnalysis = 0;

        foreach ($analysisFiles as $filePath) {
            $productId = $this->extractProductIdFromPath($filePath);
            if ($productId && !in_array($productId, $existingProducts)) {
                $orphanedAnalysis++;
                $this->line("  🗑️  Fichier d'analyse orphelin: {$filePath}");
            }
        }

        $total = $orphanedImages + $orphanedAnalysis;
        $this->info("📊 Total: {$total} fichiers orphelins trouvés ({$orphanedImages} images, {$orphanedAnalysis} analyses)");
        
        if ($total > 0) {
            $this->comment('💡 Exécutez sans --dry-run pour supprimer ces fichiers');
        }
    }

    private function extractProductIdFromPath(string $path): ?int
    {
        if (preg_match('/products\/(\d+)\//', $path, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }
}
