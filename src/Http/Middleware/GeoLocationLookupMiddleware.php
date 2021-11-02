<?php

namespace MakiDizajnerica\GeoLocation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use MakiDizajnerica\GeoLocation\Facades\GeoLocation;

class GeoLocationLookupMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string $driver
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $driver = null)
    {
        $request->request->add([
            'geolocation' => GeoLocation::lookup($request->ip())->toArray()
        ]);

        return $next($request);
    }
}
