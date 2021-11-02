<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default driver for GeoLocation lookup.
    |
    | Supported: "ipwhois", "geoplugin", "abstractapi", "ipdata"
    |
    */

    'default' => env('GEOLOCATION_DRIVER', 'ipwhois'),

    /*
    |--------------------------------------------------------------------------
    | Auto Detect IP Address
    |--------------------------------------------------------------------------
    |
    | Here you may specify if you want GeoLocation to auto detect IP address
    | from request instance.
    |
    */

    'auto_detect_ip' => false,

    /*
    |--------------------------------------------------------------------------
    | Drivers
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the GeoLocation drivers that will be used.
    |
    */

    'drivers' => [

        'ipwhois' => [
            'driver' => \MakiDizajnerica\GeoLocation\Drivers\IPWhoIs::class,
            'api_endpoint' => 'http://ipwhois.app/json/{ip}',
            'query_params' => [

                'lang' => 'en',

            ],
        ],

        'geoplugin' => [
            'driver' => \MakiDizajnerica\GeoLocation\Drivers\GeoPlugin::class,
            'api_endpoint' => 'http://www.geoplugin.net/json.gp',
            'query_params' => [

                'ip' => '{ip}',
                'lang' => 'en',

            ],
        ],

        'abstractapi' => [
            'driver' => \MakiDizajnerica\GeoLocation\Drivers\AbstractApi::class,
            'api_endpoint' => 'https://ipgeolocation.abstractapi.com/v1/',
            'query_params' => [

                'api_key' => env('ABSTRACTAPI_KEY', ''),
                'ip_address' => '{ip}',

            ],
        ],

        'ipdata' => [
            'driver' => \MakiDizajnerica\GeoLocation\Drivers\IPData::class,
            'api_endpoint' => 'https://api.ipdata.co/{ip}',
            'query_params' => [

                'api-key' => env('IPDATA_KEY', ''),

            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Options
    |--------------------------------------------------------------------------
    |
    | Here you may define if lookup records should be cached and cache
    | expiration time.
    |
    */

    'cache' => [

    	'store_to_cache' => true,
        'ttl' => 86400,

    ],

    /*
    |--------------------------------------------------------------------------
    | Log Errors
    |--------------------------------------------------------------------------
    |
    | Here you may specify if driver errors should be saved in log files.
    |
    */

    'log_errors' => true,

];
