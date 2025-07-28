<?php

namespace Domain\Profile\Repositories;

use Domain\Profile\Dto\CreateProfileDto;
use Domain\Profile\Dto\UpdateProfileDto;

interface ProfileRepositoryInterface
{
    /**
     * Trouve un profil par son ID
     */
    public function findById(int $id): ?object;

    /**
     * Crée un nouveau profil
     */
    public function create(CreateProfileDto $dto): int;

    /**
     * Met à jour un profil
     */
    public function update(int $id, UpdateProfileDto $dto): bool;

    /**
     * Supprime un profil
     */
    public function delete(int $id): bool;

    /**
     * Récupère tous les profils
     */
    public function findAll(): array;

    /**
     * Récupère tous les profils actifs (pour l'endpoint public)
     */
    public function findActiveProfiles(): array;

    /**
     * Récupère les profils créés par un administrateur
     */
    public function findByAdministratorId(int $administratorId): array;

    /**
     * Vérifie si un profil existe
     */
    public function exists(int $id): bool;
}
