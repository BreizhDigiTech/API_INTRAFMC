<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ProductCBD;
use App\Models\Category;
use App\Services\FileManagerService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FileManagementTest extends TestCase
{
    use RefreshDatabase;

    private $fileManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileManager = app(FileManagerService::class);
        
        // Fake storage pour les tests
        Storage::fake('product_images');
        Storage::fake('analysis');
        Storage::fake('avatars');
    }

    public function test_can_upload_product_image()
    {
        // Données de test
        $category = Category::factory()->create();
        $product = ProductCBD::factory()->create(['category_id' => $category->id]);
        
        // Fichier image fake
        $file = UploadedFile::fake()->image('product.jpg', 800, 600)->size(1024); // 1MB

        // Upload de l'image
        $result = $this->fileManager->storeProductImage($file, $product->id);

        // Assertions
        $this->assertArrayHasKey('original', $result);
        $this->assertArrayHasKey('variants', $result);
        $this->assertArrayHasKey('size', $result);
        $this->assertArrayHasKey('mime_type', $result);

        // Vérification que les fichiers existent
        Storage::disk('product_images')->assertExists($result['original']);
        
        foreach ($result['variants'] as $variant) {
            Storage::disk('product_images')->assertExists($variant);
        }
    }

    public function test_can_upload_analysis_file()
    {
        $category = Category::factory()->create();
        $product = ProductCBD::factory()->create(['category_id' => $category->id]);
        
        // Fichier PDF fake
        $file = UploadedFile::fake()->create('analysis.pdf', 2048); // 2MB

        // Upload du fichier
        $result = $this->fileManager->storeAnalysisFile($file, $product->id);

        // Assertions
        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('size', $result);
        $this->assertArrayHasKey('original_name', $result);

        // Vérification que le fichier existe
        Storage::disk('analysis')->assertExists($result['path']);
    }

    public function test_rejects_invalid_image_format()
    {
        $category = Category::factory()->create();
        $product = ProductCBD::factory()->create(['category_id' => $category->id]);
        
        // Fichier avec mauvaise extension
        $file = UploadedFile::fake()->create('document.txt', 1024);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Format d\'image non autorisé');

        $this->fileManager->storeProductImage($file, $product->id);
    }

    public function test_rejects_oversized_image()
    {
        $category = Category::factory()->create();
        $product = ProductCBD::factory()->create(['category_id' => $category->id]);
        
        // Image trop grande (6MB)
        $file = UploadedFile::fake()->image('huge.jpg')->size(6144);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Image trop volumineuse');

        $this->fileManager->storeProductImage($file, $product->id);
    }

    public function test_can_delete_product_images()
    {
        $category = Category::factory()->create();
        $product = ProductCBD::factory()->create(['category_id' => $category->id]);
        
        // Upload d'une image
        $file = UploadedFile::fake()->image('product.jpg');
        $result = $this->fileManager->storeProductImage($file, $product->id);

        // Vérification que les fichiers existent
        Storage::disk('product_images')->assertExists($result['original']);

        // Suppression
        $this->fileManager->deleteProductImages([$result['original']]);

        // Vérification que les fichiers sont supprimés
        Storage::disk('product_images')->assertMissing($result['original']);
        
        foreach ($result['variants'] as $variant) {
            Storage::disk('product_images')->assertMissing($variant);
        }
    }

    public function test_product_model_integration()
    {
        $category = Category::factory()->create();
        $product = ProductCBD::factory()->create(['category_id' => $category->id]);

        // Upload via le modèle
        $file = UploadedFile::fake()->image('product.jpg');
        $result = $this->fileManager->storeProductImage($file, $product->id);
        
        $product->addImage($result['original']);

        // Vérification
        $this->assertContains($result['original'], $product->images);
        $this->assertNotEmpty($product->images_urls);

        // Suppression via le modèle
        $product->removeImage($result['original']);
        
        $this->assertNotContains($result['original'], $product->images ?? []);
        Storage::disk('product_images')->assertMissing($result['original']);
    }

    public function test_analysis_file_model_integration()
    {
        $category = Category::factory()->create();
        $product = ProductCBD::factory()->create(['category_id' => $category->id]);

        // Upload d'un fichier d'analyse
        $file = UploadedFile::fake()->create('analysis.pdf', 1024);
        $result = $this->fileManager->storeAnalysisFile($file, $product->id);
        
        $product->setAnalysisFile($result['path']);

        // Vérification
        $this->assertEquals($result['path'], $product->analysis_file);
        $this->assertNotNull($product->analysis_file_url);

        Storage::disk('analysis')->assertExists($result['path']);
    }

    public function test_file_cleanup_on_product_deletion()
    {
        $category = Category::factory()->create();
        $product = ProductCBD::factory()->create(['category_id' => $category->id]);

        // Upload de fichiers
        $imageFile = UploadedFile::fake()->image('product.jpg');
        $imageResult = $this->fileManager->storeProductImage($imageFile, $product->id);
        
        $analysisFile = UploadedFile::fake()->create('analysis.pdf', 1024);
        $analysisResult = $this->fileManager->storeAnalysisFile($analysisFile, $product->id);

        $product->addImage($imageResult['original']);
        $product->setAnalysisFile($analysisResult['path']);

        // Vérification que les fichiers existent
        Storage::disk('product_images')->assertExists($imageResult['original']);
        Storage::disk('analysis')->assertExists($analysisResult['path']);

        // Suppression du produit
        $product->delete();

        // Vérification que les fichiers sont supprimés
        Storage::disk('product_images')->assertMissing($imageResult['original']);
        Storage::disk('analysis')->assertMissing($analysisResult['path']);
    }

    public function test_can_generate_secure_urls()
    {
        $category = Category::factory()->create();
        $product = ProductCBD::factory()->create(['category_id' => $category->id]);
        
        $file = UploadedFile::fake()->image('product.jpg');
        $result = $this->fileManager->storeProductImage($file, $product->id);

        // Test des URLs
        $originalUrl = $this->fileManager->getProductImageUrl($result['original']);
        $thumbnailUrl = $this->fileManager->getProductImageUrl($result['original'], 'thumbnail');

        $this->assertStringContainsString('/api/files/product-image/', $originalUrl);
        $this->assertStringContainsString('/api/files/product-image/', $thumbnailUrl);
        $this->assertNotEquals($originalUrl, $thumbnailUrl);
    }

    public function test_orphaned_files_cleanup()
    {
        // Créer un produit et uploader des fichiers
        $category = Category::factory()->create();
        $product = ProductCBD::factory()->create(['category_id' => $category->id]);
        
        $file = UploadedFile::fake()->image('product.jpg');
        $result = $this->fileManager->storeProductImage($file, $product->id);
        
        // Supprimer le produit sans passer par le modèle (simulation d'orphelins)
        ProductCBD::where('id', $product->id)->delete();

        // Test du nettoyage
        $cleanedCount = $this->fileManager->cleanupOrphanedFiles();
        
        $this->assertGreaterThan(0, $cleanedCount);
        Storage::disk('product_images')->assertMissing($result['original']);
    }
}
