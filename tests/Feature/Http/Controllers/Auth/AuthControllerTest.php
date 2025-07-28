<?php

namespace Tests\Feature\Http\Controllers\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Infrastructure\Eloquent\Administrator;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_login_with_valid_credentials(): void
    {
        // Arrange
        $administrator = Administrator::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
        ]);

        // Act
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'administrator' => ['id', 'name', 'email'],
                    'token',
                    'token_type'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'token_type' => 'Bearer',
                    'administrator' => [
                        'email' => 'admin@test.com'
                    ]
                ]
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    /** @test */
    public function it_fails_login_with_invalid_credentials(): void
    {
        // Arrange
        Administrator::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
        ]);

        // Act
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ]);

        // Assert
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Identifiants invalides'
            ]);
    }

    /** @test */
    public function it_fails_login_with_nonexistent_email(): void
    {
        // Act
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'password123',
        ]);

        // Assert
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Identifiants invalides'
            ]);
    }

    /** @test */
    public function it_validates_login_request(): void
    {
        // Act
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'invalid-email',
            'password' => '123', // trop court
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /** @test */
    public function it_can_get_authenticated_user_info(): void
    {
        // Arrange
        $administrator = Administrator::factory()->create();
        $token = $administrator->createToken('test-token')->plainTextToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/auth/me');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $administrator->id,
                    'name' => $administrator->name,
                    'email' => $administrator->email,
                ]
            ])
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'name', 'email', 'created_at']
            ]);
    }

    /** @test */
    public function it_fails_to_get_user_info_without_token(): void
    {
        // Act
        $response = $this->getJson('/api/v1/auth/me');

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_logout_successfully(): void
    {
        // Arrange
        $administrator = Administrator::factory()->create();
        $token = $administrator->createToken('test-token')->plainTextToken;

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/auth/logout');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Déconnexion réussie'
            ]);

        // Vérifier que le token est révoqué
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $administrator->id,
            'tokenable_type' => Administrator::class,
        ]);
    }

    /** @test */
    public function it_fails_logout_without_token(): void
    {
        // Act
        $response = $this->postJson('/api/v1/auth/logout');

        // Assert
        $response->assertStatus(401);
    }
}
