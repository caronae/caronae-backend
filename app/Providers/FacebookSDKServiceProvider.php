<?php

namespace Caronae\Providers;

use Illuminate\Support\ServiceProvider;

class FacebookSDKServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(\Facebook\Facebook::class, function ($app) {
            return new \Facebook\Facebook([
                'app_id' => env('FACEBOOK_APP_ID'),
                'app_secret' => env('FACEBOOK_APP_SECRET'),
                'default_graph_version' => 'v2.8',
            ]);
        });
    }
}
