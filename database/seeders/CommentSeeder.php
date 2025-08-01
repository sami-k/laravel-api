<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Infrastructure\Eloquent\Administrator;
use Infrastructure\Eloquent\Comment;
use Infrastructure\Eloquent\Profile;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profiles = Profile::all();
        $administrators = Administrator::all();

        if ($profiles->isEmpty() || $administrators->isEmpty()) {
            $this->command->error('Aucun profil ou administrateur trouvé. Exécutez les autres seeders d\'abord.');

            return;
        }

        $createdComments = 0;
        $skippedComments = 0;

        // Pour chaque profil, créer des commentaires aléatoirement
        foreach ($profiles as $profile) {
            // Entre 0 et 3 commentaires par profil
            $commentCount = rand(0, 3);

            if ($commentCount === 0) {
                continue;
            }

            // Sélectionner des administrateurs aléatoirement (sans doublon)
            $selectedAdmins = $administrators->random(min($commentCount, $administrators->count()));

            foreach ($selectedAdmins as $admin) {
                $existingComment = Comment::query()
                    ->where('administrator_id', $admin->id)
                    ->where('profile_id', $profile->id)
                    ->getQuery()
                    ->exists();

                if (! $existingComment) {
                    Comment::factory()
                        ->forAdministratorAndProfile($admin, $profile)
                        ->positive()
                        ->create();

                    $createdComments++;
                } else {
                    $skippedComments++;
                }
            }
        }

        // Créer quelques commentaires supplémentaires
        Comment::factory(5)->short()->create();
        Comment::factory(3)->long()->create();

        $this->command->info('Commentaires créés avec succès !');
        $this->command->info('Total: '.Comment::query()->getQuery()->count().' commentaires');
        $this->command->info('Ignorés (doublons): '.$skippedComments);

        // ✅ LIGNE 68 - Correction has()->count() avec getQuery()
        $profilesWithComments = Profile::query()
            ->has('comments')
            ->getQuery()
            ->count();
        $this->command->info('Profils avec commentaires: '.$profilesWithComments.'/'.$profiles->count());
    }
}
