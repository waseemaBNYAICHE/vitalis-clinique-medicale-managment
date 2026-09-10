<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // SCRUM-526 - Limitation de debit sur TOUTE l'API.
        //
        // Depuis Laravel 11, le groupe de middleware 'api' ne contient plus de
        // limiteur par defaut : il faut l'activer explicitement. Sans cet
        // appel, seules les quatre routes d'authentification etaient limitees
        // (SCRUM-510) et le reste de l'API acceptait un nombre illimite de
        // requetes par minute - de quoi aspirer le fichier patients ou saturer
        // le serveur avec un seul jeton valide.
        //
        // Le limiteur 'api' est defini dans AppServiceProvider.
        //
        // Portee exacte, verifiee par les tests : Laravel ordonne les
        // middleware par priorite et place l'authentification AVANT la
        // limitation. Une requete sans jeton sur une route protegee est donc
        // rejetee en 401 sans etre comptee - un refus qui ne coute presque
        // rien au serveur. Les routes publiques sensibles ne dependent pas de
        // ce limiteur : /api/login et les routes de mot de passe ont deja les
        // leurs, plus stricts (SCRUM-510).
        $middleware->throttleApi('api');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();