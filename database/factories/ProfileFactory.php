<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Infrastructure\Eloquent\Administrator;
use Infrastructure\Eloquent\Profile;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Infrastructure\Eloquent\Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Profile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => $this->faker->lastName(),
            'prenom' => $this->faker->firstName(),
            'image' => null, // Pas d'image par défaut dans les tests
            'statut' => $this->faker->randomElement([
                Profile::STATUT_INACTIF,
                Profile::STATUT_EN_ATTENTE,
                Profile::STATUT_ACTIF,
            ]),
            'administrator_id' => Administrator::factory(),
        ];
    }

    /**
     * Create a profile with active status.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => Profile::STATUT_ACTIF,
        ]);
    }

    /**
     * Create a profile with inactive status.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => Profile::STATUT_INACTIF,
        ]);
    }

    /**
     * Create a profile with pending status.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => Profile::STATUT_EN_ATTENTE,
        ]);
    }

    /**
     * Create a profile with a specific administrator.
     */
    public function forAdministrator(Administrator $administrator): static
    {
        return $this->state(fn (array $attributes) => [
            'administrator_id' => $administrator->id,
        ]);
    }

    /**
     * Create a profile with a fake image path.
     */
    public function withImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'image' => 'profiles/'.$this->faker->uuid().'.jpg',
        ]);
    }
}
