<?php

namespace Tests\Feature\Http\Controllers\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Infrastructure\Eloquent\Administrator;
use Infrastructure\Eloquent\Profile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_active_profiles_publicly(): void
    {
        // Arrange
        $admin = Administrator::factory()->create();

        // Créer des profils avec différents statuts
        Profile::factory()->create(['statut' => 'actif', 'administrator_id' => $admin->id]);
        Profile::factory()->create(['statut' => 'actif', 'administrator_id' => $admin->id]);
        Profile::factory()->create(['statut' => 'inactif', 'administrator_id' => $admin->id]);
        Profile::factory()->create(['statut' => 'en_attente', 'administrator_id' => $admin->id]);

        // Act
        $response = $this->getJson('/api/v1/profiles');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 2 // Seulement les profils actifs
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'nom', 'prenom', 'image'] // Pas de statut
                ],
                'count'
            ]);

        // Vérifier qu'aucun profil ne contient le champ 'statut'
        $profiles = $response->json('data');
        foreach ($profiles as $profile) {
            $this->assertArrayNotHasKey('statut', $profile);
        }
    }

    /** @test */
    public function it_can_create_profile_when_authenticated(): void
    {
        // Arrange
        $administrator = Administrator::factory()->create();
        $token = $administrator->createToken('test-token')->plainTextToken;

        Storage::fake('public');

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/profiles', [
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'statut' => 'actif',
        ]);

        // Assert
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Profil créé avec succès',
                'data' => [
                    'administrator_id' => $administrator->id
                ]
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'administrator_id']
            ]);

        $this->assertDatabaseHas('profiles', [
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'statut' => 'actif',
            'administrator_id' => $administrator->id,
        ]);
    }

    /** @test */
    public function it_can_create_profile_with_image(): void
    {
        // Arrange
        $administrator = Administrator::factory()->create();
        $token = $administrator->createToken('test-token')->plainTextToken;

        Storage::fake('public');
        $image = UploadedFile::fake()->image('profile.jpg', 800, 600);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/profiles', [
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'statut' => 'actif',
            'image' => $image,
        ]);

        // Assert
        $response->assertStatus(201);

        // Vérifier que l'image a été stockée
        $profile = Profile::latest()->first();
        $this->assertNotNull($profile->image);
        Storage::disk('public')->assertExists($profile->image);
    }

    /** @test */
    public function it_fails_to_create_profile_without_authentication(): void
    {
        // Act
        $response = $this->postJson('/api/v1/profiles', [
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'statut' => 'actif',
        ]);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function it_validates_profile_creation_data(): void
    {
        // Arrange
        $administrator = Administrator::factory()->create();
        $token = $administrator->createToken('test-token')->plainTextToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/profiles', [
            'nom' => '', // requis
            'prenom' => '', // requis
            'statut' => 'invalid_status', // valeur invalide
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nom', 'prenom', 'statut']);
    }

    /** @test */
    public function it_can_show_specific_profile_when_authenticated(): void
    {
        // Arrange
        $administrator = Administrator::factory()->create();
        $profile = Profile::factory()->create(['administrator_id' => $administrator->id]);
        $token = $administrator->createToken('test-token')->plainTextToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/v1/profiles/{$profile->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $profile->id,
                    'nom' => $profile->nom,
                    'prenom' => $profile->prenom,
                    'statut' => $profile->statut, // Visible pour les admins
                ]
            ]);
    }

    /** @test */
    public function it_fails_to_show_nonexistent_profile(): void
    {
        // Arrange
        $administrator = Administrator::factory()->create();
        $token = $administrator->createToken('test-token')->plainTextToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/profiles/999');

        // Assert
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Profil non trouvé'
            ]);
    }

    /** @test */
    public function it_can_update_profile_when_authenticated(): void
    {
        // Arrange
        $administrator = Administrator::factory()->create();
        $profile = Profile::factory()->create(['administrator_id' => $administrator->id]);
        $token = $administrator->createToken('test-token')->plainTextToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/v1/profiles/{$profile->id}", [
            'nom' => 'Martin',
            'statut' => 'inactif',
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profil mis à jour avec succès'
            ]);

        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'nom' => 'Martin',
            'statut' => 'inactif',
        ]);
    }

    /** @test */
    public function it_can_delete_profile_when_authenticated(): void
    {
        // Arrange
        $administrator = Administrator::factory()->create();
        $profile = Profile::factory()->create(['administrator_id' => $administrator->id]);
        $token = $administrator->createToken('test-token')->plainTextToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/v1/profiles/{$profile->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profil supprimé avec succès'
            ]);

        $this->assertDatabaseMissing('profiles', [
            'id' => $profile->id,
        ]);
    }
}
