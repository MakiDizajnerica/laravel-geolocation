<?php

namespace MakiDizajnerica\GeoLocation;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Illuminate\Cache\CacheManager;
use Illuminate\Support\Collection;
use MakiDizajnerica\GeoLocation\Exceptions\GeoLocationDriverException;

class GeoLocationManager
{
    /** @param array */
    protected $config;

    /** @param \Illuminate\Cache\CacheManager */
    protected $cache;

    /** @param MakiDizajnerica\GeoLocation\Contracts\GeoLocationDriver */
    protected $driver;

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
     * Get driver.
     *
     * @return \MakiDizajnerica\GeoLocation\Contracts\GeoLocationDriver
     */
    protected function getDriver()
    {
        return $this->driver;
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

        if ($this->cache()->has($ipAddress)) {
            $collection = $collection->merge(
                $this->retrieveFromCache($ipAddress)
            );
        } else {
            try {
                $collection = $collection->merge(
                    $this->retrieveFromLookup($ipAddress)
                );

                $this->storeToCache($ipAddress, $collection);
            } catch (GeoLocationDriverException $e) {
                $this->reportException($e);
            }
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
        return 'geolocation-' . $ipAddress;
    }

    /**
     * Report exception to log files.
     *
     * @param  \MakiDizajnerica\GeoLocation\Exceptions\GeoLocationDriverException $e
     * @return void
     */
    protected function reportException(GeoLocationDriverException $e)
    {
        if ($this->config('log_errors')) {
            report($e);
        }
    }
}
