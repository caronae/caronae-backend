<?php

namespace Caronae\Providers;

use Caronae\ViewComposers\AllViewComposer;
use Illuminate\Support\ServiceProvider;
use Caronae\ViewComposers\ErrorViewComposer;

class ViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer(
            '*',
            AllViewComposer::class
        );

        view()->composer(
            ['errors.404', 'errors.500'],
            ErrorViewComposer::class
        );

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
