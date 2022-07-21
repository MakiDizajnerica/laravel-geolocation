<?php

namespace MakiDizajnerica\GeoLocation;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use MakiDizajnerica\GeoLocation\GeoLocationManager;
use MakiDizajnerica\GeoLocation\Facades\GeoLocation;

class GeoLocationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/geolocation.php', 'geolocation');

        $this->app->singleton('geolocation', function($app) {
            return $app->makeWith(GeoLocationManager::class, [
                'config' => $app->get('config')->get('geolocation'),
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
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/geolocation.php' => config_path('geolocation.php')], 'geolocation-config');
        }

        Request::macro('geolocation', function () {
            return GeoLocation::lookup($this->ip());
        });
    }
}
