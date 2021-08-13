<?php

namespace MakiDizajnerica\GeoLocation\Facades;

use Illuminate\Support\Facades\Facade;

class GeoLocation extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'geolocation';
    }
}
