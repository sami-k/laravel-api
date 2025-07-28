<?php

namespace Domain\Comment\Services;

use Domain\Comment\Dto\CreateCommentDto;
use Domain\Comment\Repositories\CommentRepositoryInterface;
use Domain\Comment\Exceptions\CommentAlreadyExistsException;
use Domain\Comment\Exceptions\CommentNotFoundException;
use Domain\Profile\Repositories\ProfileRepositoryInterface;
use Domain\Profile\Exceptions\ProfileNotFoundException;

class CommentService
{
    public function __construct(
        private readonly CommentRepositoryInterface $commentRepository,
        private readonly ProfileRepositoryInterface $profileRepository
    ) {}

    /**
     * Crée un nouveau commentaire avec validation des règles métier
     */
    public function create(CreateCommentDto $dto): int
    {
        // Vérification que le profil existe
        if (!$this->profileRepository->exists($dto->profileId)) {
            throw new ProfileNotFoundException("Profile with ID {$dto->profileId} not found");
        }

        // Vérification qu'un administrateur ne peut poster qu'un commentaire par profil
        if ($this->commentRepository->hasCommentedProfile($dto->administratorId, $dto->profileId)) {
            throw new CommentAlreadyExistsException(
                "Administrator {$dto->administratorId} has already commented on profile {$dto->profileId}"
            );
        }

        // Validation du contenu
        $this->validateCommentContent($dto->contenu);

        return $this->commentRepository->create($dto);
    }

    /**
     * Supprime un commentaire
     */
    public function delete(int $id): bool
    {
        $comment = $this->commentRepository->findById($id);

        if (!$comment) {
            throw new CommentNotFoundException("Comment with ID {$id} not found");
        }

        return $this->commentRepository->delete($id);
    }

    /**
     * Vérifie si un administrateur peut commenter un profil
     */
    public function canComment(int $administratorId, int $profileId): bool
    {
        return !$this->commentRepository->hasCommentedProfile($administratorId, $profileId);
    }

    /**
     * Récupère tous les commentaires d'un profil
     */
    public function getCommentsByProfile(int $profileId): array
    {
        return $this->commentRepository->findByProfileId($profileId);
    }

    /**
     * Récupère tous les commentaires d'un administrateur
     */
    public function getCommentsByAdministrator(int $administratorId): array
    {
        return $this->commentRepository->findByAdministratorId($administratorId);
    }

    /**
     * Valide le contenu d'un commentaire
     */
    private function validateCommentContent(string $content): void
    {
        $content = trim($content);

        if (empty($content)) {
            throw new \InvalidArgumentException('Comment content cannot be empty');
        }

        if (strlen($content) < 3) {
            throw new \InvalidArgumentException('Comment content must be at least 3 characters long');
        }

        if (strlen($content) > 1000) {
            throw new \InvalidArgumentException('Comment content must not exceed 1000 characters');
        }
    }
}
