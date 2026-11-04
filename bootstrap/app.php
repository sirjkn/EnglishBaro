<?php

use App\Http\Middleware\EnsureHasModule;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\TrackSessionActivity;
use App\Http\Middleware\UnderConstruction;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'module' => EnsureHasModule::class,
        ]);

        $middleware->appendToGroup('web', TrackSessionActivity::class);
        // Appended (not prepended) so it runs after session/auth middleware
        // has already resolved — otherwise $request->user() is always null
        // here and even a logged-in admin gets gated out.
        $middleware->appendToGroup('web', UnderConstruction::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
