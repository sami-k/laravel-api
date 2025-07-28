<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Pour une API, on ne redirige pas, on retourne null
        // Laravel retournera automatiquement une erreur 401 JSON
        return $request->expectsJson() ? null : route('login');
    }
}
