<?php

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
    ->withProviders([
        App\Providers\Filament\AdminPanelProvider::class,
        App\Providers\Filament\StorePanelProvider::class,
        App\Providers\Filament\HuashuPanelProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {
        // Apply locale from session on every web request
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        // Redirect unauthenticated web requests to the appropriate login page
        $middleware->redirectGuestsTo(function ($request) {
            // huashu-admin/* is handled by Filament's own Authenticate middleware
            return route('retailer.login');
        });

        $middleware->alias([
            'role'               => \App\Http\Middleware\EnsureRole::class,
            'retailer'           => \App\Http\Middleware\EnsureRetailer::class,
            'retailer.approved'  => \App\Http\Middleware\EnsureRetailerApproved::class,
            'retailer.auth'      => \App\Http\Middleware\RetailerAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (Throwable $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return (new \App\Exceptions\Handler(app()))->render($request, $e);
            }
        });
    })->create();
