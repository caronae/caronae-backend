<?php

namespace Caronae\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Caronae\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \Caronae\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Caronae\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'csrf'
        ],
        'api.v1' => [
            'bindings'
        ]
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Caronae\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'api.v1.auth' => \Caronae\Http\Middleware\ApiV1Authenticate::class,
        'api.v1.userMatchesRequestedUser' => \Caronae\Http\Middleware\ApiV1AuthenticateRequestedUser::class,
        'api.v1.userBelongsToRide' => \Caronae\Http\Middleware\ApiV1AuthenticateRideUser::class,
        'api.v1.userOwnsRide' => \Caronae\Http\Middleware\ApiV1AuthenticateRideDriver::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'guest' => \Caronae\Http\Middleware\RedirectIfAuthenticated::class,
        'csrf' => \Caronae\Http\Middleware\VerifyCsrfToken::class
    ];

    /**
     * OVERRIDING PARENT CLASS
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected $bootstrappers = [
        'Illuminate\Foundation\Bootstrap\DetectEnvironment',
        'Illuminate\Foundation\Bootstrap\LoadConfiguration',
        'Illuminate\Foundation\Bootstrap\HandleExceptions',
        'Illuminate\Foundation\Bootstrap\RegisterFacades',
        'Illuminate\Foundation\Bootstrap\SetRequestForConsole',
        'Illuminate\Foundation\Bootstrap\RegisterProviders',
        'Illuminate\Foundation\Bootstrap\BootProviders',
        'Bootstrap\ConfigureLogging'
    ];
}
