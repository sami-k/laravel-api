<?php

namespace App\Actions\Comment;

use Domain\Comment\Dto\CreateCommentDto;
use Domain\Comment\Services\CommentService;
use Domain\Comment\Exceptions\CommentAlreadyExistsException;
use Domain\Profile\Exceptions\ProfileNotFoundException;

/**
 * Action de création d'un commentaire
 */
class CreateCommentAction
{
    public function __construct(
        private readonly CommentService $commentService
    ) {}

    /**
     * Execute la création d'un commentaire
     *
     * @param array<string, mixed> $data
     * @throws CommentAlreadyExistsException
     * @throws ProfileNotFoundException
     * @throws \RuntimeException
     */
    public function execute(array $data, int $administratorId): int
    {
        try {
            // Ajout de l'ID de l'administrateur aux données
            $data['administrator_id'] = $administratorId;

            $dto = CreateCommentDto::fromArray($data);

            return $this->commentService->create($dto);

        } catch (CommentAlreadyExistsException|ProfileNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \RuntimeException('Comment creation failed: ' . $e->getMessage());
        }
    }
}
