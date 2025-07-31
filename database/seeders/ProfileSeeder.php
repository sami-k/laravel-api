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
            $this->command->error('Aucun administrateur trouvé. Exécutez AdministratorSeeder d\'abord.');
            return;
        }

        // Profils actifs
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

        // Profils avec images
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

        $this->command->info('Profils créés avec succès !');

        // ✅ LIGNES 61-64 - Corrections count() avec getQuery()
        $totalProfiles = Profile::query()->getQuery()->count();
        $activeProfiles = Profile::query()->where('statut', 'actif')->getQuery()->count();
        $pendingProfiles = Profile::query()->where('statut', 'en_attente')->getQuery()->count();
        $inactiveProfiles = Profile::query()->where('statut', 'inactif')->getQuery()->count();

        $this->command->info('Total: ' . $totalProfiles . ' profils');
        $this->command->info('Actifs: ' . $activeProfiles);
        $this->command->info('En attente: ' . $pendingProfiles);
        $this->command->info('Inactifs: ' . $inactiveProfiles);
    }
}
