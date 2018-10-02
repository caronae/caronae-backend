<?php

namespace Caronae\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Caronae\Http\Middleware\TrimStrings::class,
        \Caronae\Http\Middleware\TrustProxies::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            \Caronae\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Caronae\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
        'api.v1' => [
            'bindings'
        ]
    ];

    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'api.institution' => \Caronae\Http\Middleware\ApiAuthenticateInstitution::class,
        'api.v1.auth' => \Caronae\Http\Middleware\ApiV1Authenticate::class,
        'api.v1.userMatchesRequestedUser' => \Caronae\Http\Middleware\ApiV1AuthenticateRequestedUser::class,
        'api.v1.userBelongsToRide' => \Caronae\Http\Middleware\ApiV1AuthenticateRideUser::class,
        'api.v1.userIsTheDriver' => \Caronae\Http\Middleware\ApiV1AuthenticateRideDriver::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \Caronae\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    ];
}
