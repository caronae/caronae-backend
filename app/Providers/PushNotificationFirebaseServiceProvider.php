<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\PushNotificationInterface;
use App\Repositories\PushNotificationFirebaseRepository;

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
