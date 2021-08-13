<?php

namespace MakiDizajnerica\GeoLocation;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use MakiDizajnerica\GeoLocation\Contracts\GeoLocationDriver as GeoLocationDriverContract;

abstract class GeoLocationDriver implements GeoLocationDriverContract
{
    private $options;
    private $queryParams;

    /**
     * @param  array $options
     * @return void
     */
    public function __construct($options, array $queryParams)
    {
        $this->options = $options;
        $this->queryParams = $queryParams;
    }

    /**
     * Get driver specific options using dot notation.
     *
     * @param  string $key
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    protected function options($key = null)
    {
        if (! is_string($key) || empty($key)) {
            return $this->options;
        }

        $options = Arr::get($this->options, $key, null);

        if (is_null($options)) {
            throw new InvalidArgumentException(
                sprintf('GeoLocation driver option \'%s\' not defined.', $key)
            );
        }

        return $options;
    }

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
     * Return data formatted to specified format.
     * 
     * @param  array $data
     * @param  array $format
     * @return array
     */
    protected function formatted($data, array $format)
    {
        if (is_array($data) && ! empty($data)) {
            if (empty($format)) {
                return $data;
            }

            return $this->mergeByAppending($format, $data, true, null);
        }

        return [];
    }

    /**
     * Generate complete api endpoint url.
     * 
     * @param  string $ipAddress
     * @param  string $apiEndpoint
     * @param  string $query
     * @return string
     */
    private function generateApiEndpoint($ipAddress, $apiEndpoint, $query)
    {
        if (Str::contains($apiEndpoint, '{ip}')) {
            $apiEndpoint = Str::replace('{ip}', $ipAddress, $apiEndpoint);
        }

        if ($query) {
            return sprintf('%s?%s', $apiEndpoint, $query);
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
            return Arr::query(
                $this->mergeByAppending($query, $this->queryParams)
            );
        }

        return '';
    }



    /**
     * Merge two arrays by appending one array keys to other array values.
     *
     * @param  array $toAppendOn
     * @param  array $toAppend
     * @param  bool  $returnDefault
     * @param  mixed $default
     * @return array
     */
    private function mergeByAppending(array $toAppendOn, array $toAppend, $returnDefault = false, $default = null)
    {
        return array_map(function ($value) use ($toAppend, $returnDefault, $default) {
            return Arr::get(
                $toAppend, $value, $returnDefault
                    ? $default
                    : $value
            );
        }, $toAppendOn);
    }
}
