<?php

namespace Infrastructure\Repositories;

use Domain\Profile\Repositories\ProfileRepositoryInterface;
use Domain\Profile\Dto\CreateProfileDto;
use Domain\Profile\Dto\UpdateProfileDto;
use Infrastructure\Eloquent\Profile;

class EloquentProfileRepository implements ProfileRepositoryInterface
{
    /**
     * Trouve un profil par son ID
     */
    public function findById(int $id): ?object
    {
        return Profile::query()->with(['administrator', 'comments'])->find($id);
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

        $profile = Profile::query()->create($profileData);

        return $profile->id;
    }

    /**
     * Met à jour un profil
     */
    public function update(int $id, UpdateProfileDto $dto): bool
    {
        $profile = Profile::query()->find($id);

        if ($profile === null) {
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
        $profile = Profile::query()->find($id);

        if ($profile === null) {
            return false;
        }

        return $profile->delete();
    }

    /**
     * Récupère tous les profils
     *
     * @return array<int, mixed>
     */
    public function findAll(): array
    {
        return Profile::query()->with(['administrator'])->get()->toArray();
    }

    /**
     * Récupère tous les profils actifs (pour l'endpoint public)
     *
     * @return array<int, mixed>
     */
    public function findActiveProfiles(): array
    {
        return Profile::query()
            ->where('statut', 'actif')
            ->with(['administrator:id,name']) // Charge seulement les champs nécessaires
            ->get()
            ->toArray();
    }

    /**
     * Récupère les profils créés par un administrateur
     *
     * @return array<int, mixed>
     */
    public function findByAdministratorId(int $administratorId): array
    {
        return Profile::query()
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
        return Profile::query()
            ->where('id', $id)
            ->getQuery()
            ->exists();
    }

    /**
     * Récupère les profils par statut
     *
     * @return array<int, mixed>
     */
    public function findByStatus(string $status): array
    {
        return Profile::query()
            ->where('statut', $status)
            ->with(['administrator:id,name,email'])
            ->get()
            ->toArray();
    }

    /**
     * Compte le nombre de profils par statut
     */
    public function countByStatus(string $status): int
    {
        $count = Profile::query()
            ->where('statut', $status)
            ->getQuery()
            ->count();

        return $count;
    }

    /**
     * Récupère les profils récents (derniers créés)
     *
     * @return array<int, mixed>
     */
    public function findRecent(int $limit = 10): array
    {
        $query = Profile::query();

        return $query->with(['administrator:id,name'])
            ->getQuery()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Recherche des profils par nom ou prénom
     *
     * @return array<int, mixed>
     */
    public function searchByName(string $searchTerm): array
    {
        return Profile::query()
            ->where(function ($query) use ($searchTerm) {
                $query->where('nom', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('prenom', 'LIKE', "%{$searchTerm}%");
            })
            ->with(['administrator:id,name'])
            ->get()
            ->toArray();
    }

    /**
     * Récupère les profils avec leurs statistiques de commentaires
     *
     * @return array<int, mixed>
     */
    public function findWithCommentStats(): array
    {
        return Profile::query()
            ->withCount('comments')
            ->with(['administrator:id,name'])
            ->get()
            ->toArray();
    }

    /**
     * Met à jour le statut d'un profil
     */
    public function updateStatus(int $id, string $status): bool
    {
        $affected = Profile::query()
            ->where('id', $id)
            ->update(['statut' => $status]);

        return $affected > 0;
    }

    /**
     * Supprime tous les profils d'un administrateur
     */
    public function deleteByAdministratorId(int $administratorId): int
    {
        return Profile::query()
            ->where('administrator_id', $administratorId)
            ->delete();
    }

    /**
     * Vérifie si un administrateur a des profils
     */
    public function hasProfilesForAdministrator(int $administratorId): bool
    {
        return Profile::query()
            ->where('administrator_id', $administratorId)
            ->getQuery()
            ->exists();
    }
}
