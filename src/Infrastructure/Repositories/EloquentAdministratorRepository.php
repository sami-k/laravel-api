<?php

namespace Infrastructure\Repositories;

use Domain\Administrator\Repositories\AdministratorRepositoryInterface;
use Domain\Administrator\Dto\CreateAdministratorDto;
use Infrastructure\Eloquent\Administrator;

class EloquentAdministratorRepository implements AdministratorRepositoryInterface
{
    public function __construct(
        private readonly Administrator $model
    ) {}

    /**
     * Trouve un administrateur par son ID
     */
    public function findById(int $id): ?object
    {
        return $this->model->find($id);
    }

    /**
     * Trouve un administrateur par son email
     */
    public function findByEmail(string $email): ?object
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Vérifie si un administrateur existe avec cet email
     */
    public function existsByEmail(string $email): bool
    {
        return $this->model->where('email', $email)->exists();
    }

    /**
     * Crée un nouvel administrateur
     */
    public function create(CreateAdministratorDto $dto): int
    {
        $administrator = $this->model->create($dto->toArray());

        return $administrator->id;
    }

    /**
     * Met à jour un administrateur
     */
    public function update(int $id, array $data): bool
    {
        $administrator = $this->model->find($id);

        if (!$administrator) {
            return false;
        }

        return $administrator->update($data);
    }

    /**
     * Supprime un administrateur
     */
    public function delete(int $id): bool
    {
        $administrator = $this->model->find($id);

        if (!$administrator) {
            return false;
        }

        return $administrator->delete();
    }

    /**
     * Récupère tous les administrateurs
     */
    public function findAll(): array
    {
        return $this->model->all()->toArray();
    }
}
