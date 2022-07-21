<?php

namespace MakiDizajnerica\GeoLocation\Drivers;

use MakiDizajnerica\GeoLocation\GeoLocationDriver;

class IPWhoIs extends GeoLocationDriver
{
    /**
     * Get the geo location details for provided IP address.
     *
     * @param  string $ipAddress
     * @return array
     */
    public function lookup($ipAddress): array
    {
        return $this->httpClientLookup($ipAddress);
    }

    /**
     * Format lookup response.
     *
     * @param  array $data
     * @return array
     */
    public function format(array $data): array
    {
        // {key_that_will_be_available_in_collection} => {response_data_using_dot_notation}
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
