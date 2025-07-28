<?php

namespace App\Http\Controllers\Api;


use App\Actions\Profile\CreateProfileAction;
use App\Actions\Profile\UpdateProfileAction;
use App\Actions\Profile\GetActiveProfilesAction;
use Illuminate\Routing\Controller;
use Infrastructure\Http\Requests\CreateProfileRequest;
use Infrastructure\Http\Requests\UpdateProfileRequest;
use Domain\Profile\Exceptions\ProfileNotFoundException;
use Domain\Profile\Exceptions\InvalidImageException;
use Domain\Profile\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private readonly CreateProfileAction $createAction,
        private readonly UpdateProfileAction $updateAction,
        private readonly GetActiveProfilesAction $getActiveProfilesAction,
        private readonly ProfileService $profileService
    ) {}

    /**
     * Récupère tous les profils actifs (endpoint public)
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $profiles = $this->getActiveProfilesAction->execute();

            return response()->json([
                'success' => true,
                'message' => 'Profils actifs récupérés avec succès',
                'data' => $profiles,
                'count' => count($profiles)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des profils',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Crée un nouveau profil (protégé par authentification)
     *
     * @param CreateProfileRequest $request
     * @return JsonResponse
     */
    public function store(CreateProfileRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $administratorId = $request->user()->id;

            $profileId = $this->createAction->execute($data, $administratorId);

            return response()->json([
                'success' => true,
                'message' => 'Profil créé avec succès',
                'data' => [
                    'id' => $profileId,
                    'administrator_id' => $administratorId
                ]
            ], 201);

        } catch (InvalidImageException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur avec l\'image fournie',
                'error' => $e->getMessage()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du profil',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Affiche un profil spécifique (protégé par authentification)
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            // Utilisation directe du service pour récupérer tous les détails
            $profile = app(\Domain\Profile\Repositories\ProfileRepositoryInterface::class)->findById($id);

            if (!$profile) {
                throw new ProfileNotFoundException("Profile with ID {$id} not found");
            }

            return response()->json([
                'success' => true,
                'message' => 'Profil récupéré avec succès',
                'data' => $profile
            ], 200);

        } catch (ProfileNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profil non trouvé',
                'error' => $e->getMessage()
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du profil',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Met à jour un profil (protégé par authentification)
     *
     * @param UpdateProfileRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateProfileRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $success = $this->updateAction->execute($id, $data);

            if (!$success) {
                throw new \RuntimeException('Échec de la mise à jour du profil');
            }

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'data' => ['id' => $id]
            ], 200);

        } catch (ProfileNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profil non trouvé',
                'error' => $e->getMessage()
            ], 404);

        } catch (InvalidImageException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur avec l\'image fournie',
                'error' => $e->getMessage()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du profil',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Supprime un profil (protégé par authentification)
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $success = $this->profileService->delete($id);

            if (!$success) {
                throw new \RuntimeException('Échec de la suppression du profil');
            }

            return response()->json([
                'success' => true,
                'message' => 'Profil supprimé avec succès'
            ], 200);

        } catch (ProfileNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profil non trouvé',
                'error' => $e->getMessage()
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du profil',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }
}
