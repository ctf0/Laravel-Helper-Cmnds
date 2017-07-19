<?php

namespace ctf0\LaravelHelperCmnds;

use Illuminate\Support\ServiceProvider;

class LaravelHelperCmndsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        $this->commands([
            Commands\ClearAll::class,
            Commands\MakeAll::class,
            Commands\FineTune::class,
            Commands\ReMigrate::class,
        ]);
    }

    /**
     * Register any package services.
     */
    public function register()
    {
    }
}
