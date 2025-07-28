<?php

namespace Infrastructure\Repositories;

use Domain\Profile\Repositories\ProfileRepositoryInterface;
use Domain\Profile\Dto\CreateProfileDto;
use Domain\Profile\Dto\UpdateProfileDto;
use Infrastructure\Eloquent\Profile;

class EloquentProfileRepository implements ProfileRepositoryInterface
{
    public function __construct(
        private readonly Profile $model
    ) {}

    /**
     * Trouve un profil par son ID
     */
    public function findById(int $id): ?object
    {
        return $this->model->with(['administrator', 'comments'])->find($id);
    }

    /**
     * Crée un nouveau profil
     */
    public function create(CreateProfileDto $dto): int
    {
        $profileData = $dto->toArray();

        // Gestion spéciale pour l'image (UploadedFile → string)
        if (isset($profileData['image']) && is_object($profileData['image'])) {
            $profileData['image'] = $dto->image; // Le service a déjà géré le stockage
        }

        $profile = $this->model->create($profileData);

        return $profile->id;
    }

    /**
     * Met à jour un profil
     */
    public function update(int $id, UpdateProfileDto $dto): bool
    {
        $profile = $this->model->find($id);

        if (!$profile) {
            return false;
        }

        $updateData = $dto->toArray();

        // Gestion spéciale pour l'image
        if (isset($updateData['image']) && is_object($updateData['image'])) {
            $updateData['image'] = $dto->image; // Le service a déjà géré le stockage
        }

        return $profile->update($updateData);
    }

    /**
     * Supprime un profil
     */
    public function delete(int $id): bool
    {
        $profile = $this->model->find($id);

        if (!$profile) {
            return false;
        }

        return $profile->delete();
    }

    /**
     * Récupère tous les profils
     */
    public function findAll(): array
    {
        return $this->model->with(['administrator'])->get()->toArray();
    }

    /**
     * Récupère tous les profils actifs (pour l'endpoint public)
     */
    public function findActiveProfiles(): array
    {
        return $this->model
            ->active()
            ->with(['administrator:id,name']) // Charge seulement les champs nécessaires
            ->get()
            ->toArray();
    }

    /**
     * Récupère les profils créés par un administrateur
     */
    public function findByAdministratorId(int $administratorId): array
    {
        return $this->model
            ->where('administrator_id', $administratorId)
            ->with(['comments'])
            ->get()
            ->toArray();
    }

    /**
     * Vérifie si un profil existe
     */
    public function exists(int $id): bool
    {
        return $this->model->where('id', $id)->exists();
    }
}
