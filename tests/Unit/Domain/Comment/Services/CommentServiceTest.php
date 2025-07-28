<?php

namespace Tests\Unit\Domain\Comment\Services;

use Tests\TestCase;
use Domain\Comment\Services\CommentService;
use Domain\Comment\Repositories\CommentRepositoryInterface;
use Domain\Profile\Repositories\ProfileRepositoryInterface;
use Domain\Comment\Dto\CreateCommentDto;
use Domain\Comment\Exceptions\CommentAlreadyExistsException;
use Domain\Comment\Exceptions\CommentNotFoundException;
use Domain\Profile\Exceptions\ProfileNotFoundException;
use InvalidArgumentException;
use Mockery;

class CommentServiceTest extends TestCase
{
    private CommentService $service;
    private CommentRepositoryInterface $commentRepositoryMock;
    private ProfileRepositoryInterface $profileRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commentRepositoryMock = Mockery::mock(CommentRepositoryInterface::class);
        $this->profileRepositoryMock = Mockery::mock(ProfileRepositoryInterface::class);
        $this->service = new CommentService(
            $this->commentRepositoryMock,
            $this->profileRepositoryMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_create_comment(): void
    {
        // Arrange
        $dto = new CreateCommentDto('Excellent profil !', 1, 1);

        $this->profileRepositoryMock
            ->shouldReceive('exists')
            ->once()
            ->with(1)
            ->andReturn(true);

        $this->commentRepositoryMock
            ->shouldReceive('hasCommentedProfile')
            ->once()
            ->with(1, 1)
            ->andReturn(false);

        $this->commentRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($dto)
            ->andReturn(1);

        // Act
        $result = $this->service->create($dto);

        // Assert
        $this->assertEquals(1, $result);
    }

    /** @test */
    public function it_throws_exception_when_profile_does_not_exist(): void
    {
        // Arrange
        $dto = new CreateCommentDto('Excellent profil !', 1, 999);

        $this->profileRepositoryMock
            ->shouldReceive('exists')
            ->once()
            ->with(999)
            ->andReturn(false);

        // Act & Assert
        $this->expectException(ProfileNotFoundException::class);
        $this->service->create($dto);
    }

    /** @test */
    public function it_throws_exception_when_administrator_already_commented(): void
    {
        // Arrange
        $dto = new CreateCommentDto('Excellent profil !', 1, 1);

        $this->profileRepositoryMock
            ->shouldReceive('exists')
            ->once()
            ->with(1)
            ->andReturn(true);

        $this->commentRepositoryMock
            ->shouldReceive('hasCommentedProfile')
            ->once()
            ->with(1, 1)
            ->andReturn(true);

        // Act & Assert
        $this->expectException(CommentAlreadyExistsException::class);
        $this->service->create($dto);
    }

    /** @test */
    public function it_throws_exception_for_empty_content(): void
    {
        // Arrange
        $dto = new CreateCommentDto('   ', 1, 1);

        $this->profileRepositoryMock
            ->shouldReceive('exists')
            ->once()
            ->with(1)
            ->andReturn(true);

        $this->commentRepositoryMock
            ->shouldReceive('hasCommentedProfile')
            ->once()
            ->with(1, 1)
            ->andReturn(false);

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Comment content cannot be empty');
        $this->service->create($dto);
    }

    /** @test */
    public function it_throws_exception_for_too_short_content(): void
    {
        // Arrange
        $dto = new CreateCommentDto('OK', 1, 1);

        $this->profileRepositoryMock
            ->shouldReceive('exists')
            ->once()
            ->with(1)
            ->andReturn(true);

        $this->commentRepositoryMock
            ->shouldReceive('hasCommentedProfile')
            ->once()
            ->with(1, 1)
            ->andReturn(false);

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Comment content must be at least 3 characters long');
        $this->service->create($dto);
    }

    /** @test */
    public function it_throws_exception_for_too_long_content(): void
    {
        // Arrange
        $longContent = str_repeat('a', 1001);
        $dto = new CreateCommentDto($longContent, 1, 1);

        $this->profileRepositoryMock
            ->shouldReceive('exists')
            ->once()
            ->with(1)
            ->andReturn(true);

        $this->commentRepositoryMock
            ->shouldReceive('hasCommentedProfile')
            ->once()
            ->with(1, 1)
            ->andReturn(false);

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Comment content must not exceed 1000 characters');
        $this->service->create($dto);
    }

    /** @test */
    public function it_can_check_if_administrator_can_comment(): void
    {
        // Arrange
        $this->commentRepositoryMock
            ->shouldReceive('hasCommentedProfile')
            ->once()
            ->with(1, 1)
            ->andReturn(false);

        // Act
        $result = $this->service->canComment(1, 1);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_get_comments_by_profile(): void
    {
        // Arrange
        $comments = [
            ['id' => 1, 'contenu' => 'Great profile!'],
            ['id' => 2, 'contenu' => 'Excellent work!'],
        ];

        $this->commentRepositoryMock
            ->shouldReceive('findByProfileId')
            ->once()
            ->with(1)
            ->andReturn($comments);

        // Act
        $result = $this->service->getCommentsByProfile(1);

        // Assert
        $this->assertEquals($comments, $result);
    }

    /** @test */
    public function it_can_delete_comment(): void
    {
        // Arrange
        $comment = (object) ['id' => 1];

        $this->commentRepositoryMock
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($comment);

        $this->commentRepositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with(1)
            ->andReturn(true);

        // Act
        $result = $this->service->delete(1);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_throws_exception_when_deleting_nonexistent_comment(): void
    {
        // Arrange
        $this->commentRepositoryMock
            ->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andReturn(null);

        // Act & Assert
        $this->expectException(CommentNotFoundException::class);
        $this->service->delete(999);
    }
}
