<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\SigaInterface;
use App\Repositories\SigaRemoteRepository;

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
