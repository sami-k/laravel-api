<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Infrastructure\Eloquent\Administrator;
use Infrastructure\Eloquent\Comment;
use Infrastructure\Eloquent\Profile;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Infrastructure\Eloquent\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contenu' => $this->faker->paragraph(mt_rand(1, 3)),
            'administrator_id' => Administrator::factory(),
            'profile_id' => Profile::factory(),
        ];
    }

    /**
     * Create a comment for a specific administrator and profile.
     */
    public function forAdministratorAndProfile(Administrator $administrator, Profile $profile): static
    {
        return $this->state(fn (array $attributes) => [
            'administrator_id' => $administrator->id,
            'profile_id' => $profile->id,
        ]);
    }

    /**
     * Create a comment for a specific profile.
     */
    public function forProfile(Profile $profile): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_id' => $profile->id,
        ]);
    }

    /**
     * Create a comment by a specific administrator.
     */
    public function byAdministrator(Administrator $administrator): static
    {
        return $this->state(fn (array $attributes) => [
            'administrator_id' => $administrator->id,
        ]);
    }

    /**
     * Create a short comment.
     */
    public function short(): static
    {
        return $this->state(fn (array $attributes) => [
            'contenu' => $this->faker->sentence(mt_rand(3, 8)),
        ]);
    }

    /**
     * Create a long comment.
     */
    public function long(): static
    {
        return $this->state(fn (array $attributes) => [
            'contenu' => $this->faker->paragraphs(mt_rand(3, 5), true),
        ]);
    }

    /**
     * Create a positive comment.
     */
    public function positive(): static
    {
        $positiveComments = [
            'Excellent profil ! Très professionnel et compétent.',
            'Personne très qualifiée avec une belle expérience.',
            'Profil remarquable, je recommande vivement.',
            'Compétences exceptionnelles, travail de qualité.',
            'Très impressionnant, professionnel de haut niveau.',
        ];

        return $this->state(fn (array $attributes) => [
            'contenu' => $this->faker->randomElement($positiveComments),
        ]);
    }
}
