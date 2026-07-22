<?php

use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware de contrôle de rôle (chap. 4 du mémoire) : utilisable
        // dans les routes via Route::middleware('role:administrateur') ou
        // 'role:eleveur,vendeur_provende,vendeur_accessoire' pour plusieurs
        // rôles autorisés. Voir app/Http/Middleware/EnsureRole.php.
        $middleware->alias([
            'role' => EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
