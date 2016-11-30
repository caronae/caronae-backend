<?php

namespace Caronae\Providers;

use Illuminate\Support\ServiceProvider;
use Caronae\Repositories\SigaInterface;
use Caronae\Repositories\SigaRemoteRepository;

class SigaRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(SigaInterface::class, SigaRemoteRepository::class);
    }
}
