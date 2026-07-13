<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de contrôle de rôle générique.
 * Enregistrement (Laravel 11+, bootstrap/app.php) :
 *
 *   ->withMiddleware(function (Middleware $middleware) {
 *       $middleware->alias(['role' => \App\Http\Middleware\EnsureRole::class]);
 *   })
 *
 * Utilisation dans les routes : Route::middleware('role:administrateur')->...
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        abort_if($request->user() === null, 401);
        abort_unless(in_array($request->user()->role, $roles, true), 403, "Accès non autorisé pour ce rôle.");

        return $next($request);
    }
}
