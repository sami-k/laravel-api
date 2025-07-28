<?php

namespace Domain\Comment\Repositories;

use Domain\Comment\Dto\CreateCommentDto;

interface CommentRepositoryInterface
{
    /**
     * Trouve un commentaire par son ID
     */
    public function findById(int $id): ?object;

    /**
     * Crée un nouveau commentaire
     */
    public function create(CreateCommentDto $dto): int;

    /**
     * Supprime un commentaire
     */
    public function delete(int $id): bool;

    /**
     * Récupère tous les commentaires d'un profil
     */
    public function findByProfileId(int $profileId): array;

    /**
     * Récupère tous les commentaires d'un administrateur
     */
    public function findByAdministratorId(int $administratorId): array;

    /**
     * Vérifie si un administrateur a déjà commenté un profil
     */
    public function hasCommentedProfile(int $administratorId, int $profileId): bool;

    /**
     * Trouve le commentaire d'un admin sur un profil spécifique
     */
    public function findByAdministratorAndProfile(int $administratorId, int $profileId): ?object;
}
