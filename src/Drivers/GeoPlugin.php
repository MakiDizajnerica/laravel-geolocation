<?php

namespace MakiDizajnerica\GeoLocation\Drivers;

use MakiDizajnerica\GeoLocation\GeoLocationDriver;

class GeoPlugin extends GeoLocationDriver
{
    /**
     * Get the geo location details for provided IP address.
     *
     * @param  string $ipAddress
     * @return array
     */
    public function lookup($ipAddress)
    {
        return $this->httpClientLookup($ipAddress);
    }

    /**
     * Format lookup response.
     *
     * @param  array $data
     * @return array
     */
    public function format($data)
    {
        // {key_that_will_be_available_in_collection} => {response_data_using_dot_notation}
        return $this->formatted($data, [
            'ip' => 'geoplugin_request',
            'continent' => 'geoplugin_continentName',
            'continentCode' => 'geoplugin_continentCode',
            'country' => 'geoplugin_countryName',
            'countryCode' => 'geoplugin_countryCode',
            'region' => 'geoplugin_region',
            'city' => 'geoplugin_city',
            'timezone' => 'geoplugin_timezone',
            'currencyCode' => 'geoplugin_currencyCode',
            'currencySymbol' => 'geoplugin_currencySymbol',
            'latitude' => 'geoplugin_latitude',
            'longitude' => 'geoplugin_longitude',
        ]);
    }
}
