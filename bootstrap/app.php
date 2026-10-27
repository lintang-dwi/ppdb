<?php

use App\Http\Middleware\IdentifyTenantPpdb;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'tenant' => IdentifyTenantPpdb::class,
            'staff.tenant' => \App\Http\Middleware\EnsureStaffBelongsToTenant::class,
        ]);

        $middleware->priority([
            EncryptCookies::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            IdentifyTenantPpdb::class,
            Authenticate::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);

        $middleware->redirectTo(
            guests: function (Request $request) {
                if ($request->is('superadmin*')) {
                    return route('superadmin.login');
                }

                if ($request->is('*/cms/*') || $request->is('cms/*') || $request->is('*/cms') || $request->is('cms')) {
                    $schoolCode = $request->route('school_code') ?? tenant_context()->getSchoolCode();

                    return tenant_route('cms.login', $schoolCode ? ['school_code' => $schoolCode] : []);
                }

                return route('platform.home');
            }
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
