<?php

namespace Domain\Profile\Services;

use Domain\Profile\Dto\CreateProfileDto;
use Domain\Profile\Dto\UpdateProfileDto;
use Domain\Profile\Repositories\ProfileRepositoryInterface;
use Domain\Profile\Exceptions\ProfileNotFoundException;
use Domain\Profile\Exceptions\InvalidImageException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ProfileService
{
    public function __construct(
        private readonly ProfileRepositoryInterface $repository
    ) {}

    /**
     * Crée un nouveau profil avec gestion de l'image
     */
    public function create(CreateProfileDto $dto): int
    {
        // Gestion de l'upload d'image
        $imagePath = null;
        if ($dto->image) {
            $imagePath = $this->handleImageUpload($dto->image);
        }

        // Création du DTO avec le chemin de l'image
        $createDto = new CreateProfileDto(
            nom: $dto->nom,
            prenom: $dto->prenom,
            image: $imagePath,
            statut: $dto->statut,
            administratorId: $dto->administratorId
        );

        return $this->repository->create($createDto);
    }

    /**
     * Met à jour un profil existant
     */
    public function update(object $profile, UpdateProfileDto $dto): bool
    {
        // Gestion de l'upload d'image si présente
        if ($dto->image) {
            // Suppression de l'ancienne image si elle existe
            if ($profile->image && Storage::exists($profile->image)) {
                Storage::delete($profile->image);
            }

            // Upload de la nouvelle image
            $imagePath = $this->handleImageUpload($dto->image);
            $dto = new UpdateProfileDto(
                nom: $dto->nom,
                prenom: $dto->prenom,
                image: $imagePath,
                statut: $dto->statut
            );
        }

        return $this->repository->update($profile->id, $dto);
    }

    /**
     * Supprime un profil et son image associée
     */
    public function delete(int $id): bool
    {
        $profile = $this->repository->findById($id);

        if (!$profile) {
            throw new ProfileNotFoundException("Profile with ID {$id} not found");
        }

        // Suppression de l'image si elle existe
        if ($profile->image && Storage::exists($profile->image)) {
            Storage::delete($profile->image);
        }

        return $this->repository->delete($id);
    }

    /**
     * Récupère les profils actifs (pour l'endpoint public)
     * Filtre le champ 'statut' pour la sécurité
     */
    public function getActiveProfilesForPublic(): array
    {
        $profiles = $this->repository->findActiveProfiles();

        // Suppression du champ 'statut' pour l'endpoint public
        return array_map(function ($profile) {
            $profileArray = (array) $profile;
            unset($profileArray['statut']);
            return $profileArray;
        }, $profiles);
    }

    /**
     * Gère l'upload d'image
     */
    private function handleImageUpload(UploadedFile $image): string
    {
        // Validation de l'image
        if (!$image->isValid()) {
            throw new InvalidImageException('Invalid image file');
        }

        // Validation du type MIME
        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (!in_array($image->getMimeType(), $allowedMimes)) {
            throw new InvalidImageException('Image must be a valid image file (jpeg, png, jpg, gif)');
        }

        // Validation de la taille (max 5MB)
        if ($image->getSize() > 5 * 1024 * 1024) {
            throw new InvalidImageException('Image size must not exceed 5MB');
        }

        // Stockage de l'image
        return $image->store('profiles', 'public');
    }
}
