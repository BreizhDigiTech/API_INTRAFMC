<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer un utilisateur admin par défaut
        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('L15fddef!'),
                'is_admin' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        
        // Créer un utilisateur de test
        User::firstOrCreate(
            ['email' => 'test@example.com'], 
            [
                'name' => 'Test User',
                'password' => bcrypt('password123'),
                'is_admin' => false,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            TestUserSeeder::class,
        ]);
    }
}