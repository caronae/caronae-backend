<?php

namespace Caronae\Providers;

use Carbon\Carbon;
use Caronae\Services\ValidateDuplicateService;
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

        $this->app->bind(ValidateDuplicateService::class, function()
        {
            $request = app('request');

            $dateTime = Carbon::createFromFormat('d/m/Y H:i:s', $request->input('date') . ' ' . $request->input('time'));

            return new ValidateDuplicateService($request->user(), $dateTime, $request->input('going'));
        });
    }
}
