<?php

namespace Domain\Administrator\Repositories;

use Domain\Administrator\Dto\CreateAdministratorDto;

interface AdministratorRepositoryInterface
{
    /**
     * Trouve un administrateur par son ID
     */
    public function findById(int $id): ?object;

    /**
     * Trouve un administrateur par son email
     */
    public function findByEmail(string $email): ?object;

    /**
     * Vérifie si un administrateur existe avec cet email
     */
    public function existsByEmail(string $email): bool;

    /**
     * Crée un nouvel administrateur
     */
    public function create(CreateAdministratorDto $dto): int;

    /**
     * Met à jour un administrateur
     */
    public function update(int $id, array $data): bool;

    /**
     * Supprime un administrateur
     */
    public function delete(int $id): bool;

    /**
     * Récupère tous les administrateurs
     */
    public function findAll(): array;
}
