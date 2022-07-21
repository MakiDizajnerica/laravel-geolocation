<?php

namespace MakiDizajnerica\GeoLocation;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Illuminate\Cache\CacheManager;
use Illuminate\Support\Collection;
use MakiDizajnerica\GeoLocation\Exceptions\GeoLocationDriverException;

class GeoLocationManager
{
    /** @var array */
    protected $config;

    /** @var \Illuminate\Cache\CacheManager */
    protected $cache;

    /** @var MakiDizajnerica\GeoLocation\Contracts\GeoLocationDriver */
    protected $driver;

    /** @var array */
    protected $collection;

    /**
     * @param  array $config
     * @param  \Illuminate\Cache\CacheManager $cache
     * @return void
     */
    public function __construct($config, CacheManager $cache)
    {
        $this->config = $config;
        $this->cache = $cache;

        $this->setDefaultDriver();
    }

    /**
     * Get config properties using dot notation.
     *
     * @param  string $key
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    protected function config($key = null)
    {
        if (! $key) {
            return $this->config;
        }

        $config = Arr::get($this->config, $key, null);

        if (is_null($config)) {
            throw new InvalidArgumentException("GeoLocation config property '{$key}' not defined.");
        }

        return $config;
    }

    /**
     * Get cache driver.
     *
     * @return \Illuminate\Cache\CacheManager
     */
    protected function cache()
    {
        return $this->cache;
    }

    /**
     * Set default driver.
     * 
     * @return void
     */
    protected function setDefaultDriver()
    {
        $this->driver($this->getDefaultDriver());
    }

    /**
     * Get default driver.
     *
     * @return string
     */
    protected function getDefaultDriver()
    {
        return $this->config('default');
    }

    /**
     * Set driver.
     * 
     * @param  string $driver
     * @param  array $queryParams
     * @return \MakiDizajnerica\GeoLocation\GeoLocationManager
     */
    public function driver($driver, array $queryParams = [])
    {
        $this->resolve($driver, $queryParams);

        return $this;
    }

    /**
     * Get driver.
     *
     * @return \MakiDizajnerica\GeoLocation\Contracts\GeoLocationDriver
     */
    protected function getDriver()
    {
        return $this->driver;
    }

    /**
     * Resolve the driver.
     * 
     * @param  string $driver
     * @param  array $queryParams
     * @return \MakiDizajnerica\GeoLocation\GeoLocationManager
     * 
     * @throws \InvalidArgumentException
     */
    protected function resolve($driver, array $queryParams)
    {
        if (! is_string($driver) || empty($driver)) {
            throw new InvalidArgumentException('GeoLocation driver must be non-empty string.');
        }

        $options = Arr::wrap($this->config("drivers.{$driver}"));

        if (! $options) {
            throw new InvalidArgumentException("GeoLocation driver '{$driver}' is not defined.");
        }

        $driverClass = Arr::pull($options, 'driver');

        if (! class_exists($driverClass)) {
            throw new InvalidArgumentException("GeoLocation driver '{$driverClass}' does not exist.");
        }

        $this->driver = new $driverClass($options, $queryParams);
    }

    /**
     * Collect IP address.
     * 
     * @param  mixed $ipAddress
     * @return string
     * 
     * @throws \InvalidArgumentException
     */    
    protected function collectIpAddress($ipAddress)
    {
        if ($this->config('auto_detect_ip')) {
            $ipAddress = request()->ip();
        }

        if (! is_string($ipAddress) || empty($ipAddress)) {
            throw new InvalidArgumentException('GeoLocation IP address must be non-empty string.');
        }

        return $ipAddress;
    }

    /**
     * Lookup provided IP address.
     *
     * @param  string $ipAddress
     * @return \Illuminate\Support\Collection
     */
    public function lookup($ipAddress = null)
    {
        $ipAddress = $this->collectIpAddress($ipAddress);
        $collection = collect([]);

        switch (true) {
            case $this->isInProperty($ipAddress):
                $collection = $collection->merge($this->retrieveFromProperty($ipAddress));
                break;
            case $this->isInCache($ipAddress):
                $collection = $collection->merge($this->retrieveFromCache($ipAddress));

                $this->storeToProperty($ipAddress, $collection);
                break;
            default:
                try {
                    $collection = $collection->merge($this->retrieveFromLookup($ipAddress));

                    $this->storeToCache($ipAddress, $collection);
                    $this->storeToProperty($ipAddress, $collection);
                } catch (GeoLocationDriverException $e) {
                    if ($this->config('log_errors')) {
                        report($e);
                    }
                }
                break;
        }

        return $collection;
    }

    /**
     * Get response for IP address lookup.
     *
     * @param  string $ipAddress
     * @return array
     */
    protected function retrieveFromLookup($ipAddress)
    {
        $data = $this->getDriver()->lookup($ipAddress);

        if (is_array($data) && ! blank($data)) {
            return $this->getDriver()->format($data);
        }

        return [];
    }

    /**
     * @param  string $ipAddress
     * @return bool
     */
    protected function isInCache($ipAddress)
    {
        return $this->cache()->has($this->cacheKey($ipAddress));
    }

    /**
     * Retrieve collection from cache.
     *
     * @param  string $ipAddress
     * @return array
     */
    protected function retrieveFromCache($ipAddress)
    {
        return $this->cache()->get($this->cacheKey($ipAddress));
    }

    /**
     * Store collection to cache.
     *
     * @param  string $ipAddress
     * @param  \Illuminate\Support\Collection $collection
     * @return void
     */
    protected function storeToCache($ipAddress, $collection)
    {
        if ($this->config('cache.store_to_cache') &&
            $collection instanceof Collection &&
            $collection->isNotEmpty()) {
            $this->cache()->put(
                $this->cacheKey($ipAddress),
                $collection->toArray(),
                $this->config('cache.ttl')
            );
        }
    }

    /**
     * Format cache key.
     *
     * @param  string $ipAddress
     * @return string
     */
    protected function cacheKey($ipAddress)
    {
        return "geolocation-{$ipAddress}";
    }

    /**
     * @param  string $ipAddress
     * @return bool
     */
    protected function isInProperty($ipAddress)
    {
        if (isset($this->collection[$ipAddress])) {
            return ! blank($this->collection[$ipAddress]);
        }
    
        return false;
    }

    /**
     * Retrieve collection from property.
     *
     * @param  string $ipAddress
     * @return array
     */
    protected function retrieveFromProperty($ipAddress)
    {
        return $this->collection[$ipAddress];
    }

    /**
     * Store collection to property.
     *
     * @param  string $ipAddress
     * @param  \Illuminate\Support\Collection $collection
     * @return void
     */
    protected function storeToProperty($ipAddress, $collection)
    {
        $this->collection[$ipAddress] = $collection->toArray();
    }
}
