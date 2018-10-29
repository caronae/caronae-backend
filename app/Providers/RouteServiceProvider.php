<?php

namespace Caronae\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'Caronae\Http\Controllers';

    public function boot()
    {
        parent::boot();
    }

    public function map()
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
        $this->mapAdminRoutes();
    }

    protected function mapApiRoutes()
    {
        Route::prefix('api/v1')
            ->middleware('api.v1')
            ->namespace($this->namespace . '\API\v1')
            ->group(base_path('routes/api.v1.php'));
    }

    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace . '\Web')
             ->group(base_path('routes/web.php'));
    }

    protected function mapAdminRoutes()
    {
        Route::prefix('admin')
            ->middleware(['web'])
            ->namespace($this->namespace . '\Admin')
            ->group(base_path('routes/admin.auth.php'));

        Route::prefix('admin')
            ->middleware(['web', 'auth:admin'])
            ->namespace($this->namespace . '\Admin')
            ->group(base_path('routes/admin.php'));
    }
}
