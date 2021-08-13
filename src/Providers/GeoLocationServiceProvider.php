<?php

namespace MakiDizajnerica\GeoLocation\Providers;

use Illuminate\Support\ServiceProvider;
use MakiDizajnerica\GeoLocation\GeoLocationManager;
use MakiDizajnerica\GeoLocation\Facades\GeoLocation;
use Illuminate\Contracts\Support\DeferrableProvider;

class GeoLocationServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/geolocation.php', 'geolocation'
        );

        $this->app->singleton('geolocation', function($app) {
            return $app->makeWith(GeoLocationManager::class, [
                'config' => $app['config']->get('geolocation'),
                'cache' => $app->get('cache'),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/geolocation.php' => config_path('geolocation.php')
        ], 'laravel-geolocation-config');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['geolocation'];
    }
}
