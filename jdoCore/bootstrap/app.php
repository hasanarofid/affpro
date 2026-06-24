<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'superadmin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'merge_cart' => \App\Http\Middleware\MergeGuestCart::class,
            'demo_mode' => \App\Http\Middleware\DemoMode::class,
            'installed' => \App\Http\Middleware\CheckInstalled::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\CaptureReferral::class,
            \App\Http\Middleware\CheckInstalled::class,
            \App\Http\Middleware\TrackVisitor::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
