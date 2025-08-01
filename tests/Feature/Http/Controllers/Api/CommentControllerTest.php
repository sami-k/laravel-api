<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Infrastructure\Eloquent\Administrator;
use Infrastructure\Eloquent\Comment;
use Infrastructure\Eloquent\Profile;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_comments_for_a_profile(): void
    {
        // Arrange
        $admin1 = Administrator::factory()->create();
        $admin2 = Administrator::factory()->create();
        $admin3 = Administrator::factory()->create();
        $profile = Profile::factory()->create(['administrator_id' => $admin1->id]);

        // Créer 3 commentaires de différents admins pour éviter la contrainte d'unicité
        Comment::factory()->create([
            'profile_id' => $profile->id,
            'administrator_id' => $admin1->id,
        ]);
        Comment::factory()->create([
            'profile_id' => $profile->id,
            'administrator_id' => $admin2->id,
        ]);
        Comment::factory()->create([
            'profile_id' => $profile->id,
            'administrator_id' => $admin3->id,
        ]);

        // Act
        $response = $this->getJson("/api/v1/profiles/{$profile->id}/comments");

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 3,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'contenu', 'created_at'],
                ],
                'count',
            ]);
    }

    /** @test */
    public function it_can_create_comment_when_authenticated(): void
    {
        // Arrange
        $administrator = Administrator::factory()->create();
        $profile = Profile::factory()->create();
        $token = $administrator->createToken('test-token')->plainTextToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/comments', [
            'profile_id' => $profile->id,
            'contenu' => 'Excellent profil ! Très professionnel.',
        ]);

        // Assert
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Commentaire créé avec succès',
                'data' => [
                    'administrator_id' => $administrator->id,
                    'profile_id' => $profile->id,
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'contenu' => 'Excellent profil ! Très professionnel.',
            'administrator_id' => $administrator->id,
            'profile_id' => $profile->id,
        ]);
    }

    /** @test */
    public function it_fails_to_create_comment_without_authentication(): void
    {
        // Arrange
        $profile = Profile::factory()->create();

        // Act
        $response = $this->postJson('/api/v1/comments', [
            'profile_id' => $profile->id,
            'contenu' => 'Test comment',
        ]);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function it_validates_comment_creation_data(): void
    {
        // Arrange
        $administrator = Administrator::factory()->create();
        $token = $administrator->createToken('test-token')->plainTextToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/comments', [
            'profile_id' => 999, // profil inexistant
            'contenu' => 'AB', // trop court
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['profile_id', 'contenu']);
    }

    /** @test */
    public function it_prevents_duplicate_comments_from_same_administrator(): void
    {
        // Arrange
        $administrator = Administrator::factory()->create();
        $profile = Profile::factory()->create();
        $token = $administrator->createToken('test-token')->plainTextToken;

        // Créer un premier commentaire
        Comment::factory()->create([
            'administrator_id' => $administrator->id,
            'profile_id' => $profile->id,
        ]);

        // Act - Tenter de créer un second commentaire
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/comments', [
            'profile_id' => $profile->id,
            'contenu' => 'Deuxième commentaire (interdit)',
        ]);

        // Assert
        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Commentaire déjà existant',
                'error' => 'Vous avez déjà commenté ce profil. Un seul commentaire par profil est autorisé.',
            ]);
    }

    /** @test */
    public function it_allows_different_administrators_to_comment_same_profile(): void
    {
        // Arrange
        $admin1 = Administrator::factory()->create();
        $admin2 = Administrator::factory()->create();
        $profile = Profile::factory()->create();

        // Premier admin commente
        Comment::factory()->create([
            'administrator_id' => $admin1->id,
            'profile_id' => $profile->id,
        ]);

        $token2 = $admin2->createToken('test-token')->plainTextToken;

        // Act - Deuxième admin commente le même profil
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token2,
        ])->postJson('/api/v1/comments', [
            'profile_id' => $profile->id,
            'contenu' => 'Commentaire du deuxième admin',
        ]);

        // Assert
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'administrator_id' => $admin2->id,
                    'profile_id' => $profile->id,
                ],
            ]);

        // Vérifier qu'il y a maintenant 2 commentaires sur ce profil
        $this->assertEquals(2, Comment::where('profile_id', $profile->id)->count());
    }

    /** @test */
    public function it_can_show_specific_comment(): void
    {
        // Arrange
        $administrator = Administrator::factory()->create();
        $comment = Comment::factory()->create();
        $token = $administrator->createToken('test-token')->plainTextToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson("/api/v1/comments/{$comment->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $comment->id,
                    'contenu' => $comment->contenu,
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_show_nonexistent_comment(): void
    {
        // Arrange
        $administrator = Administrator::factory()->create();
        $token = $administrator->createToken('test-token')->plainTextToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/v1/comments/999');

        // Assert
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Commentaire non trouvé',
            ]);
    }

    /** @test */
    public function it_fails_to_show_comment_without_authentication(): void
    {
        // Arrange
        $comment = Comment::factory()->create();

        // Act
        $response = $this->getJson("/api/v1/comments/{$comment->id}");

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_check_if_administrator_can_comment(): void
    {
        // Arrange
        $administrator = Administrator::factory()->create();
        $profile = Profile::factory()->create();
        $token = $administrator->createToken('test-token')->plainTextToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson("/api/v1/comments/can-comment/{$profile->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'can_comment' => true,
                    'administrator_id' => $administrator->id,
                    'profile_id' => $profile->id,
                ],
            ]);
    }

    /** @test */
    public function it_shows_cannot_comment_when_already_commented(): void
    {
        // Arrange
        $administrator = Administrator::factory()->create();
        $profile = Profile::factory()->create();
        $token = $administrator->createToken('test-token')->plainTextToken;

        // Créer un commentaire existant
        Comment::factory()->create([
            'administrator_id' => $administrator->id,
            'profile_id' => $profile->id,
        ]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson("/api/v1/comments/can-comment/{$profile->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'can_comment' => false,
                    'administrator_id' => $administrator->id,
                    'profile_id' => $profile->id,
                ],
            ]);
    }
}
