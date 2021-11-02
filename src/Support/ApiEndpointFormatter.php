<?php

namespace MakiDizajnerica\GeoLocation\Support;

use Illuminate\Support\Str;
use Illuminate\Support\Arr as LaravelArr;
use MakiDizajnerica\GeoLocation\Support\Arr;

trait ApiEndpointFormatter
{
    /**
     * Format api endpoint url.
     * 
     * @param  string $ipAddress
     * @return string
     */
    protected function apiEndpoint($ipAddress)
    {
        $this->addToQueryParams('{ip}', $ipAddress);

        return $this->generateApiEndpoint(
            $ipAddress,
            $this->options('api_endpoint'),
            $this->sortedQueryParams()
        );
    }

    /**
     * Generate complete api endpoint url.
     * 
     * @param  string $ipAddress
     * @param  string $apiEndpoint
     * @param  string $sortedQuery
     * @return string
     */
    private function generateApiEndpoint($ipAddress, $apiEndpoint, $sortedQuery)
    {
        if (Str::contains($apiEndpoint, '{ip}')) {
            $apiEndpoint = Str::replace('{ip}', $ipAddress, $apiEndpoint);
        }

        if ($sortedQuery) {
            return sprintf('%s?%s', $apiEndpoint, $sortedQuery);
        }

        return $apiEndpoint;
    }

    /**
     * Add key/value pair to query params.
     * 
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    private function addToQueryParams($key, $value)
    {
        $this->queryParams[$key] = $value;
    }

    /**
     * Sort query params and make http query.
     * 
     * @return string
     */
    private function sortedQueryParams()
    {
        $query = $this->options('query_params');

        if (is_array($query) && ! empty($query)) {
            foreach ($this->queryParams as $key => $value) {
                if (! Str::startsWith($key, '{') &&
                    ! Str::endsWith($key, '}')) {
                    $this->queryParams['{' . $key . '}'] = $value;
                    unset($this->queryParams[$key]);
                }
            }

            return LaravelArr::query(
                Arr::mergeByAppending($query, $this->queryParams)
            );
        }

        return '';
    }
}
