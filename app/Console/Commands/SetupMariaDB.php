<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class SetupMariaDB extends Command
{
    protected $signature = 'setup:mariadb';
    protected $description = 'Setup MariaDB connection and create admin user';

    public function handle()
    {
        $this->info('🔧 Configuration de MariaDB...');
        
        try {
            // Forcer la connexion MariaDB
            $this->info('Test de connexion MariaDB...');
            $pdo = DB::connection('mariadb')->getPdo();
            $this->info('✅ Connexion MariaDB réussie');
            
            // Créer l'utilisateur admin
            $this->info('Création de l\'utilisateur admin...');
            
            $exists = DB::connection('mariadb')->table('users')
                ->where('email', 'admin@admin.com')
                ->exists();
                
            if (!$exists) {
                DB::connection('mariadb')->table('users')->insert([
                    'name' => 'Admin',
                    'email' => 'admin@admin.com',
                    'password' => bcrypt('L15fddef!'),
                    'is_admin' => true,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $this->info('✅ Utilisateur admin créé');
            } else {
                $this->info('ℹ️ Utilisateur admin existe déjà');
            }
            
            // Vérifier l'utilisateur
            $user = DB::connection('mariadb')->table('users')
                ->where('email', 'admin@admin.com')
                ->first();
                
            $this->info("✅ Utilisateur trouvé: {$user->name} ({$user->email})");
            $this->info('🎉 Configuration MariaDB terminée !');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
            return 1;
        }
    }
}
