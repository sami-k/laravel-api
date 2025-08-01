<?php

namespace Tests\Unit\Domain\Administrator\Services;

use Domain\Administrator\Dto\AuthenticateAdministratorDto;
use Domain\Administrator\Dto\CreateAdministratorDto;
use Domain\Administrator\Exceptions\AdministratorAlreadyExistsException;
use Domain\Administrator\Exceptions\InvalidCredentialsException;
use Domain\Administrator\Repositories\AdministratorRepositoryInterface;
use Domain\Administrator\Services\AdministratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Infrastructure\Eloquent\Administrator;
use Mockery;
use Tests\TestCase;

class AdministratorServiceTest extends TestCase
{
    use RefreshDatabase;

    private AdministratorService $service;

    private AdministratorRepositoryInterface $repositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repositoryMock = Mockery::mock(AdministratorRepositoryInterface::class);
        $this->service = new AdministratorService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_authenticate_administrator_with_valid_credentials(): void
    {
        // Arrange
        $email = 'admin@test.com';
        $password = 'password123';

        // Créer un vrai administrateur pour ce test
        $administrator = Administrator::factory()->create([
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $dto = new AuthenticateAdministratorDto($email, $password);

        // Utiliser le vrai repository pour ce test spécifique
        $realRepository = app(AdministratorRepositoryInterface::class);
        $realService = new AdministratorService($realRepository);

        // Act
        $result = $realService->authenticate($dto);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('administrator', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('token_type', $result);
        $this->assertEquals('Bearer', $result['token_type']);
        $this->assertEquals($email, $result['administrator']['email']);
        $this->assertEquals($administrator->id, $result['administrator']['id']);

        // Vérifier que le token n'est pas vide
        $this->assertNotEmpty($result['token']);
    }

    /** @test */
    public function it_throws_exception_when_administrator_not_found(): void
    {
        // Arrange
        $dto = new AuthenticateAdministratorDto('nonexistent@test.com', 'password');

        $this->repositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->andReturn(null);

        // Act & Assert
        $this->expectException(InvalidCredentialsException::class);
        $this->service->authenticate($dto);
    }

    /** @test */
    public function it_throws_exception_when_password_is_incorrect(): void
    {
        // Arrange
        $email = 'admin@test.com';
        $administrator = new Administrator([
            'email' => $email,
            'password' => Hash::make('correct_password'),
        ]);

        $dto = new AuthenticateAdministratorDto($email, 'wrong_password');

        $this->repositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->andReturn($administrator);

        // Act & Assert
        $this->expectException(InvalidCredentialsException::class);
        $this->service->authenticate($dto);
    }

    /** @test */
    public function it_can_create_administrator(): void
    {
        // Arrange
        $dto = new CreateAdministratorDto('John Doe', 'john@test.com', 'password123');

        $this->repositoryMock
            ->shouldReceive('existsByEmail')
            ->once()
            ->with('john@test.com')
            ->andReturn(false);

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->andReturn(1);

        // Act
        $result = $this->service->create($dto);

        // Assert
        $this->assertEquals(1, $result);
    }

    /** @test */
    public function it_throws_exception_when_creating_administrator_with_existing_email(): void
    {
        // Arrange
        $dto = new CreateAdministratorDto('John Doe', 'existing@test.com', 'password123');

        $this->repositoryMock
            ->shouldReceive('existsByEmail')
            ->once()
            ->with('existing@test.com')
            ->andReturn(true);

        // Act & Assert
        $this->expectException(AdministratorAlreadyExistsException::class);
        $this->service->create($dto);
    }

    /** @test */
    public function it_can_check_if_administrator_exists_by_email(): void
    {
        // Arrange
        $email = 'test@test.com';

        $this->repositoryMock
            ->shouldReceive('existsByEmail')
            ->once()
            ->with($email)
            ->andReturn(true);

        // Act
        $result = $this->service->existsByEmail($email);

        // Assert
        $this->assertTrue($result);
    }
}
