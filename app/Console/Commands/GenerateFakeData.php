<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\DevelopmentDataSeeder;

class GenerateFakeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:fake-data 
                            {--fresh : Fresh migration before seeding}
                            {--minimal : Generate minimal data set}
                            {--full : Generate full data set with extras}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère des données factices pour le développement frontend';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Génération de données factices pour le développement...');
        $this->newLine();

        // Vérifier si on doit refaire la migration
        if ($this->option('fresh')) {
            $this->warn('⚠️  Migration fresh en cours...');
            $this->call('migrate:fresh');
            $this->newLine();
        }

        // Choisir le mode de génération
        if ($this->option('minimal')) {
            $this->generateMinimalData();
        } elseif ($this->option('full')) {
            $this->generateFullData();
        } else {
            $this->generateStandardData();
        }

        $this->newLine();
        $this->info('✅ Génération terminée !');
        $this->displayCredentials();

        return 0;
    }

    private function generateMinimalData(): void
    {
        $this->info('📦 Mode minimal - Données essentielles uniquement');
        
        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\TestUserSeeder'
        ]);
        
        // Quelques catégories et produits
        $this->call('tinker', [
            '--execute' => "
                \App\Models\Category::factory()->count(5)->create();
                \App\Models\ProductCBD::factory()->count(20)->create();
                \App\Models\Supplier::factory()->count(3)->create();
            "
        ]);
    }

    private function generateStandardData(): void
    {
        $this->info('📊 Mode standard - Jeu de données complet');
        
        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\DevelopmentDataSeeder'
        ]);
    }

    private function generateFullData(): void
    {
        $this->info('💎 Mode complet - Données étendues avec extras');
        
        // Seeder principal
        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\DevelopmentDataSeeder'
        ]);
        
        // Données supplémentaires
        $this->info('🔄 Ajout de données supplémentaires...');
        $this->call('tinker', [
            '--execute' => "
                // Plus d'utilisateurs
                \App\Models\User::factory()->count(20)->create();
                
                // Produits spéciaux
                \App\Models\ProductCBD::factory()->count(10)->premium()->create();
                \App\Models\ProductCBD::factory()->count(5)->discounted()->create();
                \App\Models\ProductCBD::factory()->count(8)->lowStock()->create();
                
                // Fournisseurs internationaux
                \App\Models\Supplier::factory()->count(3)->international()->create();
                
                // Plus de commandes historiques
                \App\Models\Order::factory()->count(50)->delivered()->create();
                \App\Models\Order::factory()->count(10)->cancelled()->create();
            "
        ]);
    }

    private function displayCredentials(): void
    {
        $this->newLine();
        $this->info('🔑 COMPTES DE TEST DISPONIBLES:');
        $this->table(
            ['Type', 'Email', 'Mot de passe', 'Rôle'],
            [
                ['Admin Principal', 'admin@admin.com', 'L15fddef!', 'Administrateur'],
                ['Manager', 'manager@cbdstore.com', 'manager123', 'Administrateur'],
                ['Client 1', 'marie.dubois@email.com', 'client123', 'Client'],
                ['Client 2', 'pierre.martin@email.com', 'client123', 'Client'],
                ['Client 3', 'sophie.bernard@email.com', 'client123', 'Client'],
            ]
        );
        
        $this->newLine();
        $this->info('📋 DONNÉES GÉNÉRÉES:');
        $this->line('• Catégories de produits avec descriptions détaillées');
        $this->line('• Produits CBD réalistes avec prix et stocks');
        $this->line('• Fournisseurs avec informations complètes');
        $this->line('• Utilisateurs avec profils variés');
        $this->line('• Paniers avec articles');
        $this->line('• Commandes avec différents statuts');
        $this->line('• Arrivages de marchandises');
        
        $this->newLine();
        $this->info('🌐 ENDPOINTS GRAPHQL UTILES:');
        $this->line('• /graphql - Interface GraphQL Playground');
        $this->line('• POST /graphql - Endpoint API');
        
        $this->newLine();
        $this->comment('💡 Tip: Utilisez --fresh pour repartir de zéro');
        $this->comment('💡 Tip: Utilisez --minimal pour un dataset léger');
        $this->comment('💡 Tip: Utilisez --full pour toutes les données');
    }
}
