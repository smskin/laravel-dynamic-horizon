<?php

namespace SMSkin\LaravelDynamicHorizon\Providers;

use SMSkin\LaravelDynamicHorizon\Contracts\IStorage;
use SMSkin\LaravelDynamicHorizon\Repositories\Storage;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);

        $this->app->singleton(IStorage::class, static function () {
            return new Storage();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
