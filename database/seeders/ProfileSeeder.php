<?php

namespace Database\Seeders;

use Infrastructure\Eloquent\Profile;
use Infrastructure\Eloquent\Administrator;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $administrators = Administrator::all();

        if ($administrators->isEmpty()) {
            $this->command->error('❌ Aucun administrateur trouvé. Exécutez AdministratorSeeder d\'abord.');
            return;
        }

        // Profils actifs (visibles publiquement)
        Profile::factory(8)
            ->active()
            ->create([
                'administrator_id' => $administrators->random()->id
            ]);

        // Profils en attente
        Profile::factory(5)
            ->pending()
            ->create([
                'administrator_id' => $administrators->random()->id
            ]);

        // Profils inactifs
        Profile::factory(3)
            ->inactive()
            ->create([
                'administrator_id' => $administrators->random()->id
            ]);

        // Profils avec images (simulation)
        Profile::factory(4)
            ->active()
            ->withImage()
            ->create([
                'administrator_id' => $administrators->random()->id
            ]);

        // Profils spécifiques pour le premier admin
        $firstAdmin = $administrators->first();
        Profile::factory(2)
            ->active()
            ->forAdministrator($firstAdmin)
            ->create();

        $this->command->info('✅ Profils créés avec succès !');
        $this->command->info('📊 Total: ' . Profile::count() . ' profils');
        $this->command->info('🟢 Actifs: ' . Profile::where('statut', 'actif')->count());
        $this->command->info('🟡 En attente: ' . Profile::where('statut', 'en_attente')->count());
        $this->command->info('🔴 Inactifs: ' . Profile::where('statut', 'inactif')->count());
    }
}
