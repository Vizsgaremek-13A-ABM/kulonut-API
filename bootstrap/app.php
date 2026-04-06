<?php

use App\Http\Middleware\AuthenticateOptionalSanctum;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureRoleLevel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.optional.sanctum' => AuthenticateOptionalSanctum::class,
            'role' => EnsureRole::class,
            'role.level' => EnsureRoleLevel::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
