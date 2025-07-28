<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CommentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Routes publiques
Route::prefix('v1')->group(function () {

    // Authentification (publique)
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    });

    // Profils actifs (endpoint public - selon le challenge)
    Route::get('/profiles', [ProfileController::class, 'index'])->name('profiles.public');

    // Commentaires d'un profil (public pour consultation)
    Route::get('/profiles/{profileId}/comments', [CommentController::class, 'index'])->name('comments.by-profile');
});

// Routes protégées par authentification Sanctum
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {

    // Authentification (utilisateur connecté)
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    });

    // Gestion des profils (protégée)
    Route::prefix('profiles')->group(function () {
        Route::post('/', [ProfileController::class, 'store'])->name('profiles.store');
        Route::get('/{id}', [ProfileController::class, 'show'])->name('profiles.show');
        Route::put('/{id}', [ProfileController::class, 'update'])->name('profiles.update');
        Route::patch('/{id}', [ProfileController::class, 'update'])->name('profiles.patch');
        Route::delete('/{id}', [ProfileController::class, 'destroy'])->name('profiles.destroy');
    });

    // Gestion des commentaires (protégée)
    Route::prefix('comments')->group(function () {
        Route::post('/', [CommentController::class, 'store'])->name('comments.store');
        Route::get('/{id}', [CommentController::class, 'show'])->name('comments.show');

        // Vérifier si un admin peut commenter un profil
        Route::get('/can-comment/{profileId}', [CommentController::class, 'canComment'])->name('comments.can-comment');
    });
});

// Route de test pour vérifier l'API
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now(),
        'version' => 'v1'
    ]);
})->name('api.health');
