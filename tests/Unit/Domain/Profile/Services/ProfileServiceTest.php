<?php

namespace Tests\Unit\Domain\Profile\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Domain\Profile\Services\ProfileService;
use Domain\Profile\Repositories\ProfileRepositoryInterface;
use Domain\Profile\Dto\CreateProfileDto;
use Domain\Profile\Dto\UpdateProfileDto;
use Domain\Profile\Exceptions\ProfileNotFoundException;
use Domain\Profile\Exceptions\InvalidImageException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;

class ProfileServiceTest extends TestCase
{
    private ProfileService $service;
    private ProfileRepositoryInterface $repositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repositoryMock = Mockery::mock(ProfileRepositoryInterface::class);
        $this->service = new ProfileService($this->repositoryMock);

        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_create_profile_without_image(): void
    {
        // Arrange
        $dto = new CreateProfileDto('Dupont', 'Jean', null, 'actif', 1);

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
    public function it_can_create_profile_with_image(): void
    {
        // Arrange
        $image = UploadedFile::fake()->image('profile.jpg', 800, 600);
        $dto = new CreateProfileDto('Dupont', 'Jean', $image, 'actif', 1);

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->andReturn(1);

        // Act
        $result = $this->service->create($dto);

        // Assert
        $this->assertEquals(1, $result);
        Storage::disk('public')->assertExists('profiles/' . $image->hashName());
    }

    /** @test */
    public function it_throws_exception_for_invalid_image(): void
    {
        // Arrange
        $invalidImage = UploadedFile::fake()->create('document.pdf', 1000);
        $dto = new CreateProfileDto('Dupont', 'Jean', $invalidImage, 'actif', 1);

        // Act & Assert
        $this->expectException(InvalidImageException::class);
        $this->service->create($dto);
    }

    /** @test */
    public function it_throws_exception_for_oversized_image(): void
    {
        // Arrange
        $oversizedImage = UploadedFile::fake()->image('large.jpg')->size(6000); // 6MB
        $dto = new CreateProfileDto('Dupont', 'Jean', $oversizedImage, 'actif', 1);

        // Act & Assert
        $this->expectException(InvalidImageException::class);
        $this->expectExceptionMessage('Image size must not exceed 5MB');
        $this->service->create($dto);
    }

    /** @test */
    public function it_can_update_profile(): void
    {
        // Arrange
        $profile = (object) ['id' => 1, 'image' => null];
        $dto = new UpdateProfileDto('Martin', 'Pierre', null, 'inactif');

        $this->repositoryMock
            ->shouldReceive('update')
            ->once()
            ->with(1, $dto)
            ->andReturn(true);

        // Act
        $result = $this->service->update($profile, $dto);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_delete_profile(): void
    {
        // Arrange
        $profileId = 1;
        $profile = (object) ['id' => $profileId, 'image' => null];

        $this->repositoryMock
            ->shouldReceive('findById')
            ->once()
            ->with($profileId)
            ->andReturn($profile);

        $this->repositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with($profileId)
            ->andReturn(true);

        // Act
        $result = $this->service->delete($profileId);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_throws_exception_when_deleting_nonexistent_profile(): void
    {
        // Arrange
        $profileId = 999;

        $this->repositoryMock
            ->shouldReceive('findById')
            ->once()
            ->with($profileId)
            ->andReturn(null);

        // Act & Assert
        $this->expectException(ProfileNotFoundException::class);
        $this->service->delete($profileId);
    }

    /** @test */
    public function it_can_get_active_profiles_for_public(): void
    {
        // Arrange
        $profiles = [
            ['id' => 1, 'nom' => 'Dupont', 'statut' => 'actif'],
            ['id' => 2, 'nom' => 'Martin', 'statut' => 'actif'],
        ];

        $this->repositoryMock
            ->shouldReceive('findActiveProfiles')
            ->once()
            ->andReturn($profiles);

        // Act
        $result = $this->service->getActiveProfilesForPublic();

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals(2, count($result));
        // Vérifier que le champ 'statut' est supprimé
        foreach ($result as $profile) {
            $this->assertArrayNotHasKey('statut', $profile);
        }
    }
}
