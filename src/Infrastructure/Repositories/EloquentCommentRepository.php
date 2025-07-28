<?php

namespace Infrastructure\Repositories;

use Domain\Comment\Repositories\CommentRepositoryInterface;
use Domain\Comment\Dto\CreateCommentDto;
use Infrastructure\Eloquent\Comment;

class EloquentCommentRepository implements CommentRepositoryInterface
{
    public function __construct(
        private readonly Comment $model
    ) {}

    /**
     * Trouve un commentaire par son ID
     */
    public function findById(int $id): ?object
    {
        return $this->model->with(['administrator', 'profile'])->find($id);
    }

    /**
     * Crée un nouveau commentaire
     */
    public function create(CreateCommentDto $dto): int
    {
        $comment = $this->model->create($dto->toArray());

        return $comment->id;
    }

    /**
     * Supprime un commentaire
     */
    public function delete(int $id): bool
    {
        $comment = $this->model->find($id);

        if (!$comment) {
            return false;
        }

        return $comment->delete();
    }

    /**
     * Récupère tous les commentaires d'un profil
     */
    public function findByProfileId(int $profileId): array
    {
        return $this->model
            ->where('profile_id', $profileId)
            ->with(['administrator:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Récupère tous les commentaires d'un administrateur
     */
    public function findByAdministratorId(int $administratorId): array
    {
        return $this->model
            ->where('administrator_id', $administratorId)
            ->with(['profile:id,nom,prenom'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Vérifie si un administrateur a déjà commenté un profil
     */
    public function hasCommentedProfile(int $administratorId, int $profileId): bool
    {
        return $this->model
            ->where('administrator_id', $administratorId)
            ->where('profile_id', $profileId)
            ->exists();
    }

    /**
     * Trouve le commentaire d'un admin sur un profil spécifique
     */
    public function findByAdministratorAndProfile(int $administratorId, int $profileId): ?object
    {
        return $this->model
            ->where('administrator_id', $administratorId)
            ->where('profile_id', $profileId)
            ->with(['administrator', 'profile'])
            ->first();
    }
}
