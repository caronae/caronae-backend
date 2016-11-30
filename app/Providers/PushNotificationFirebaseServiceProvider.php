<?php

namespace Caronae\Providers;

use Illuminate\Support\ServiceProvider;
use Caronae\Repositories\PushNotificationInterface;
use Caronae\Repositories\PushNotificationFirebaseRepository;

class PushNotificationFirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PushNotificationInterface::class, PushNotificationFirebaseRepository::class);
    }
}
