<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (): void {
        //
    })
    ->withExceptions(function (): void {
        //
    })->create();

$app['router']->aliasMiddleware('role', \App\Http\Middleware\UserRole::class);

return $app;
