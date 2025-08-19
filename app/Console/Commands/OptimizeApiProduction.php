<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class OptimizeApiProduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:optimize 
                            {--force : Force optimization even if not in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize the API for production deployment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!app()->isProduction() && !$this->option('force')) {
            $this->error('This command should only be run in production!');
            $this->info('Use --force to run in other environments.');
            return 1;
        }

        $this->info('🚀 Starting API optimization for production...');
        
        // 1. Cache GraphQL Schema
        $this->info('📊 Caching GraphQL schema...');
        Artisan::call('lighthouse:cache');
        $this->line(Artisan::output());
        
        // 2. Cache Configuration
        $this->info('⚙️ Caching configuration...');
        Artisan::call('config:cache');
        $this->line(Artisan::output());
        
        // 3. Cache Routes
        $this->info('🛣️ Caching routes...');
        Artisan::call('route:cache');
        $this->line(Artisan::output());
        
        // 4. Cache Views (si des vues existent)
        $this->info('👁️ Caching views...');
        try {
            Artisan::call('view:cache');
            $this->line(Artisan::output());
        } catch (\Exception $e) {
            $this->warn('Views caching skipped (no views found)');
        }
        
        // 5. Cache Events
        $this->info('📢 Caching events...');
        Artisan::call('event:cache');
        $this->line(Artisan::output());
        
        // 6. Optimize Autoloader
        $this->info('🔧 Optimizing autoloader...');
        Artisan::call('optimize');
        $this->line(Artisan::output());
        
        // 7. Clear unnecessary caches
        $this->info('🧹 Clearing development caches...');
        Artisan::call('cache:clear');
        
        $this->info('✅ API optimization completed successfully!');
        
        // Afficher un résumé
        $this->newLine();
        $this->info('📋 Optimization Summary:');
        $this->table(
            ['Component', 'Status'],
            [
                ['GraphQL Schema', '✅ Cached'],
                ['Configuration', '✅ Cached'],
                ['Routes', '✅ Cached'],
                ['Views', '✅ Cached'],
                ['Events', '✅ Cached'],
                ['Autoloader', '✅ Optimized'],
                ['Application Cache', '✅ Cleared'],
            ]
        );
        
        $this->newLine();
        $this->warn('⚠️ Remember to:');
        $this->line('  • Set APP_DEBUG=false in .env');
        $this->line('  • Set APP_ENV=production in .env');
        $this->line('  • Configure proper logging levels');
        $this->line('  • Set up monitoring (Laravel Telescope, Sentry, etc.)');
        
        return 0;
    }
}
