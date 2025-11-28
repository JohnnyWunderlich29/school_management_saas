<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware globais para web
        $middleware->web(append: [
            \App\Http\Middleware\CheckSessionExpiry::class,
            \App\Http\Middleware\AlertMiddleware::class,
        ]);
        
        // Aliases de middleware
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'sala.access' => \App\Http\Middleware\CheckSalaAccess::class,
            'force.auth.notifications' => \App\Http\Middleware\ForceAuthForNotifications::class,
            'session.expiry' => \App\Http\Middleware\CheckSessionExpiry::class,
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
            'escola.context' => \App\Http\Middleware\EscolaContext::class,
            'escola.scope' => \App\Http\Middleware\EscolaScope::class,
            'module.license' => \App\Http\Middleware\ModuleLicenseMiddleware::class,
            'superadmin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'superadmin.only' => \App\Http\Middleware\SuperAdminOnlyMiddleware::class,
            'biblioteca.access' => \App\Http\Middleware\BibliotecaAccessMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
