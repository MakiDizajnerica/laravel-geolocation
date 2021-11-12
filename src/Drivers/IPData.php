<?php

namespace MakiDizajnerica\GeoLocation\Drivers;

use MakiDizajnerica\GeoLocation\GeoLocationDriver;

class IPData extends GeoLocationDriver
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
            'ip' => 'ip',
            'continent' => 'continent_name',
            'continentCode' => 'continent_code',
            'country' => 'country_name',
            'countryCode' => 'country_code',
            'countryFlag' => 'flag',
            'region' => 'region',
            'regionCode' => 'region_code',
            'city' => 'city',
            'zipCode' => 'postal',
            'timezone' => 'time_zone.name',
            'currency' => 'currency.name',
            'currencyCode' => 'currency.code',
            'currencySymbol' => 'currency.symbol',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
        ]);
    }
}
