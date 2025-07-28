<?php

namespace App\Http\Controllers\Api;


use App\Actions\Comment\CreateCommentAction;
use Illuminate\Routing\Controller;
use Infrastructure\Http\Requests\CreateCommentRequest;
use Domain\Comment\Exceptions\CommentAlreadyExistsException;
use Domain\Profile\Exceptions\ProfileNotFoundException;
use Domain\Comment\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(
        private readonly CreateCommentAction $createAction,
        private readonly CommentService $commentService
    ) {}

    /**
     * Récupère les commentaires d'un profil
     *
     * @param int $profileId
     * @return JsonResponse
     */
    public function index(int $profileId): JsonResponse
    {
        try {
            $comments = $this->commentService->getCommentsByProfile($profileId);

            return response()->json([
                'success' => true,
                'message' => 'Commentaires récupérés avec succès',
                'data' => $comments,
                'count' => count($comments)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des commentaires',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Crée un nouveau commentaire (protégé par authentification)
     *
     * @param CreateCommentRequest $request
     * @return JsonResponse
     */
    public function store(CreateCommentRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $administratorId = $request->user()->id;

            $commentId = $this->createAction->execute($data, $administratorId);

            return response()->json([
                'success' => true,
                'message' => 'Commentaire créé avec succès',
                'data' => [
                    'id' => $commentId,
                    'administrator_id' => $administratorId,
                    'profile_id' => $data['profile_id']
                ]
            ], 201);

        } catch (CommentAlreadyExistsException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Commentaire déjà existant',
                'error' => 'Vous avez déjà commenté ce profil. Un seul commentaire par profil est autorisé.'
            ], 409);

        } catch (ProfileNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profil non trouvé',
                'error' => $e->getMessage()
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du commentaire',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Affiche un commentaire spécifique
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $comment = app(\Domain\Comment\Repositories\CommentRepositoryInterface::class)->findById($id);

            if (!$comment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Commentaire non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Commentaire récupéré avec succès',
                'data' => $comment
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du commentaire',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Vérifie si un administrateur peut commenter un profil
     *
     * @param Request $request
     * @param int $profileId
     * @return JsonResponse
     */
    public function canComment(Request $request, int $profileId): JsonResponse
    {
        try {
            $administratorId = $request->user()->id;
            $canComment = $this->commentService->canComment($administratorId, $profileId);

            return response()->json([
                'success' => true,
                'data' => [
                    'can_comment' => $canComment,
                    'administrator_id' => $administratorId,
                    'profile_id' => $profileId
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }
}
