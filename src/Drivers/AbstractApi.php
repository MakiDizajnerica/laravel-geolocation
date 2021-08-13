<?php

namespace MakiDizajnerica\GeoLocation\Drivers;

use MakiDizajnerica\GeoLocation\GeoLocationDriver;
use MakiDizajnerica\GeoLocation\Support\HandlingHttpResponse;

class AbstractApi extends GeoLocationDriver
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
            'ip' => 'ip_address',
            'continent' => 'continent',
            'continentCode' => 'continent_code',
            'country' => 'country',
            'countryCode' => 'country_code',
            'countryFlag' => 'flag.svg',
            'region' => 'region',
            'regionCode' => 'region_iso_code',
            'city' => 'city',
            'zipCode' => 'postal_code',
            'timezone' => 'timezone.name',
            'currency' => 'currency.currency_name',
            'currencyCode' => 'currency.currency_code',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
        ]);
    }
}
