<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Ajouter le middleware pour suivre l'activité des utilisateurs
        $middleware->appendToGroup('web', \App\Http\Middleware\UpdateLastActivity::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Laravel utilise automatiquement les vues resources/views/errors/{code}.blade.php
        // en production quand APP_DEBUG=false
    })->create();
