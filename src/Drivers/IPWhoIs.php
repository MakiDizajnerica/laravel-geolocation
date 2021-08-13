<?php

namespace MakiDizajnerica\GeoLocation\Drivers;

use MakiDizajnerica\GeoLocation\GeoLocationDriver;
use MakiDizajnerica\GeoLocation\Support\HandlingHttpResponse;

class IPWhoIs extends GeoLocationDriver
{
    use HandlingHttpResponse;

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
        // {key_that_will_be_available_in_collection} => {response_data_key}
        return $this->formatted($data, [
            'ip' => 'ip',
            'continent' => 'continent',
            'continentCode' => 'continent_code',
            'country' => 'country',
            'countryCode' => 'country_code',
            'countryFlag' => 'country_flag',
            'countryCapital' => 'country_capital',
            'region' => 'region',
            'city' => 'city',
            'timezone' => 'timezone',
            'timezoneName' => 'timezone_name',
            'currency' => 'currency',
            'currencyCode' => 'currency_code',
            'currencySymbol' => 'currency_symbol',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
        ]);
    }
}
