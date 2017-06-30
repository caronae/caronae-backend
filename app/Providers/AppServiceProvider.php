<?php

namespace Caronae\Providers;

use Carbon\Carbon;
use Caronae\Http\Requests\LoginRequest;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Carbon::setToStringFormat(config('custom.nativeDateFormat'));
    }

    public function register()
    {
        $this->app->singleton(Generator::class, function() {
            return Factory::create('pt_BR');
        });

        $this->app->singleton(LoginRequest::class, function() {
            return LoginRequest::capture();
        });
    }
}
