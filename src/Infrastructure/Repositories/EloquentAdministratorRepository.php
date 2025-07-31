<?php

namespace Infrastructure\Repositories;

use Domain\Administrator\Repositories\AdministratorRepositoryInterface;
use Domain\Administrator\Dto\CreateAdministratorDto;
use Infrastructure\Eloquent\Administrator;

class EloquentAdministratorRepository implements AdministratorRepositoryInterface
{
    /**
     * Trouve un administrateur par son ID
     */
    public function findById(int $id): ?object
    {
        return Administrator::query()->find($id);
    }

    /**
     * Trouve un administrateur par son email
     */
    public function findByEmail(string $email): ?object
    {
        return Administrator::query()->where('email', $email)->first();
    }

    /**
     * Vérifie si un administrateur existe avec cet email
     */
    public function existsByEmail(string $email): bool
    {
        return Administrator::query()
            ->where('email', $email)
            ->getQuery()
            ->exists();
    }

    /**
     * Crée un nouvel administrateur
     */
    public function create(CreateAdministratorDto $dto): int
    {
        $administrator = Administrator::query()->create($dto->toArray());

        return $administrator->id;
    }

    /**
     * Met à jour un administrateur
     */
    public function update(int $id, array $data): bool
    {
        $administrator = Administrator::query()->find($id);

        if ($administrator === null) {
            return false;
        }

        return $administrator->update($data);
    }

    /**
     * Supprime un administrateur
     */
    public function delete(int $id): bool
    {
        $administrator = Administrator::query()->find($id);

        if ($administrator === null) {
            return false;
        }

        return $administrator->delete();
    }

    /**
     * Récupère tous les administrateurs
     *
     * @return array<int, mixed>
     */
    public function findAll(): array
    {
        return Administrator::query()->get()->toArray();
    }
}
