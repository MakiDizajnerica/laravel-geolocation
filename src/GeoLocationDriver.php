<?php

namespace MakiDizajnerica\GeoLocation;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use MakiDizajnerica\GeoLocation\Support\ApiEndpointFormatter;
use MakiDizajnerica\GeoLocation\Support\HandlingHttpResponse;
use MakiDizajnerica\GeoLocation\Support\ResponseDataFormatter;
use MakiDizajnerica\GeoLocation\Contracts\GeoLocationDriver as GeoLocationDriverContract;

abstract class GeoLocationDriver implements GeoLocationDriverContract
{
    use HandlingHttpResponse,
    ResponseDataFormatter,
    ApiEndpointFormatter;

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
        if (! $key) {
            return $this->options;
        }

        $options = Arr::get($this->options, $key, null);

        if (is_null($options)) {
            throw new InvalidArgumentException("GeoLocation driver option '{$key}' not defined.");
        }

        return $options;
    }
}
