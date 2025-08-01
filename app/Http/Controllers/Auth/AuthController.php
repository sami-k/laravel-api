<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Administrator\AuthenticateAdministratorAction;
use Domain\Administrator\Exceptions\InvalidCredentialsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Infrastructure\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthenticateAdministratorAction $authenticateAction
    ) {}

    /**
     * Authentifie un administrateur
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->validated();
            $result = $this->authenticateAction->execute($credentials);

            return response()->json([
                'success' => true,
                'message' => 'Authentification réussie',
                'data' => $result,
            ], 200);

        } catch (InvalidCredentialsException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiants invalides',
                'error' => $e->getMessage(),
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'authentification',
                'error' => (bool) config('app.debug') ? $e->getMessage() : 'Erreur interne',
            ], 500);
        }
    }

    /**
     * Déconnecte un administrateur (révoque les tokens)
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user !== null) {
                $user->currentAccessToken()->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Déconnexion réussie',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la déconnexion',
                'error' => (bool) config('app.debug') ? $e->getMessage() : 'Erreur interne',
            ], 500);
        }
    }

    /**
     * Récupère les informations de l'administrateur connecté
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des informations',
                'error' => (bool) config('app.debug') ? $e->getMessage() : 'Erreur interne',
            ], 500);
        }
    }
}
