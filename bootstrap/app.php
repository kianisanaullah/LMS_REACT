<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\AdminOnly;
use App\Http\Middleware\AttachUserRoles;   // ✅ new import
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

// ✅ Import Spatie permission middlewares
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

      
$middleware->alias([
    'role'               => RoleMiddleware::class,
    'permission'         => PermissionMiddleware::class,
    'role_or_permission' => RoleOrPermissionMiddleware::class,
    'admin.only'         => AdminOnly::class,
    'attach.roles'       => AttachUserRoles::class,
    'must.have'          => \App\Http\Middleware\CheckPermission::class, // ✅ new


        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
