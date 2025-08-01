<?php

namespace Infrastructure\Repositories;

use Domain\Comment\Dto\CreateCommentDto;
use Domain\Comment\Repositories\CommentRepositoryInterface;
use Infrastructure\Eloquent\Comment;

class EloquentCommentRepository implements CommentRepositoryInterface
{
    /**
     * Trouve un commentaire par son ID
     */
    public function findById(int $id): ?object
    {
        return Comment::query()->with(['administrator', 'profile'])->find($id);
    }

    /**
     * Crée un nouveau commentaire
     */
    public function create(CreateCommentDto $dto): int
    {
        $comment = Comment::query()->create($dto->toArray());

        return $comment->id;
    }

    /**
     * Supprime un commentaire
     */
    public function delete(int $id): bool
    {
        $comment = Comment::query()->find($id);

        if ($comment === null) {
            return false;
        }

        return $comment->delete();
    }

    /**
     * Récupère tous les commentaires d'un profil
     *
     * @return array<int, mixed>
     */
    public function findByProfileId(int $profileId): array
    {
        return Comment::query()
            ->where('profile_id', $profileId)
            ->with(['administrator:id,name,email'])
            ->getQuery()
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Récupère tous les commentaires d'un administrateur
     *
     * @return array<int, mixed>
     */
    public function findByAdministratorId(int $administratorId): array
    {
        return Comment::query()
            ->where('administrator_id', $administratorId)
            ->with(['profile:id,nom,prenom'])
            ->getQuery()
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Vérifie si un administrateur a déjà commenté un profil
     */
    public function hasCommentedProfile(int $administratorId, int $profileId): bool
    {
        return Comment::query()
            ->where('administrator_id', $administratorId)
            ->where('profile_id', $profileId)
            ->getQuery()
            ->exists();
    }

    /**
     * Trouve le commentaire d'un admin sur un profil spécifique
     */
    public function findByAdministratorAndProfile(int $administratorId, int $profileId): ?object
    {
        return Comment::query()
            ->where('administrator_id', $administratorId)
            ->where('profile_id', $profileId)
            ->with(['administrator', 'profile'])
            ->first();
    }
}
