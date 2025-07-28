<?php

namespace Database\Seeders;

use Infrastructure\Eloquent\Administrator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdministratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Administrateur de test principal
        Administrator::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Principal',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Administrateur demo
        Administrator::firstOrCreate(
            ['email' => 'demo@laravel-challenge.com'],
            [
                'name' => 'Admin Demo',
                'password' => Hash::make('demo123'),
                'email_verified_at' => now(),
            ]
        );

        // Créer 3 administrateurs supplémentaires avec la factory
        Administrator::factory(3)->create();

        $this->command->info('✅ Administrateurs créés avec succès !');
        $this->command->info('📧 Connexion test: admin@test.com / password123');
        $this->command->info('📧 Connexion demo: demo@laravel-challenge.com / demo123');
    }
}
