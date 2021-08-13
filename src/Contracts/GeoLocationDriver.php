<?php

namespace MakiDizajnerica\GeoLocation\Contracts;

interface GeoLocationDriver
{
    /**
     * Get the geo location details for provided IP address.
     *
     * @param  string $ipAddress
     * @return array
     *
     * @throws \MakiDizajnerica\GeoLocation\Exceptions\GeoLocationDriverException
     */
    public function lookup($ipAddress);

    /**
     * Format lookup response.
     *
     * @param  array $data
     * @return array
     */
    public function format($data);
}
