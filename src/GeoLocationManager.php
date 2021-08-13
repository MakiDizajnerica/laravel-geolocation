<?php

namespace MakiDizajnerica\GeoLocation;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Illuminate\Cache\CacheManager;
use Illuminate\Support\Collection;
use MakiDizajnerica\GeoLocation\Exceptions\GeoLocationDriverException;

class GeoLocationManager
{
    protected $config;
    protected $cache;
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
        if (! is_string($key) || empty($key)) {
            return $this->config;
        }

        $config = Arr::get($this->config, $key, null);

        if (is_null($config)) {
            throw new InvalidArgumentException(
                sprintf('GeoLocation config property \'%s\' not defined.', $key)
            );
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
        $this->driver(
            $this->getDefaultDriver()
        );
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
     * @param  array  $queryParams
     * @return \MakiDizajnerica\GeoLocation\GeoLocationManager
     * 
     * @throws \InvalidArgumentException
     */
    public function driver($driver, array $queryParams = [])
    {
        if (! is_string($driver) || empty($driver)) {
            throw new InvalidArgumentException('GeoLocation driver must be non-empty string.');
        }

        return $this->resolve($driver, $queryParams);
    }

    /**
     * Resolve the driver.
     * 
     * @param  string $driver
     * @param  array  $queryParams
     * @return \MakiDizajnerica\GeoLocation\GeoLocationManager
     * 
     * @throws \InvalidArgumentException
     */
    protected function resolve($driver, array $queryParams)
    {
        $options = Arr::wrap(
            $this->config(sprintf('drivers.%s', $driver))
        );

        if (empty($options)) {
            throw new InvalidArgumentException(
                sprintf('GeoLocation driver \'%s\' is not defined.', $driver)
            );
        }

        $driverClass = Arr::pull($options, 'driver');

        if (! class_exists($driverClass)) {
            throw new InvalidArgumentException(
                sprintf('GeoLocation driver \'%s\' does not exist.', $driverClass)
            );
        }

        $this->driver = new $driverClass($options, $queryParams);

        return $this;
    }

    /**
     * Get driver.
     *
     * @return MakiDizajnerica\GeoLocation\GeoLocationDriver
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
            return request()->ip();
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
            $this->mergeCollection(
                $collection, $this->retrieveFromCache($ipAddress)
            );
        }
        else {
            try {
                $this->mergeCollection(
                    $collection, $this->retrieveFromLookup($ipAddress)
                );

                $this->storeToCache($ipAddress, $collection);
            }
            catch (GeoLocationDriverException $e) {
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
        return $this->format(
            $this->getDriver()->lookup($ipAddress)
        );
    }

    /**
     * Format driver lookup data.
     *
     * @param  mixed $data
     * @return array
     */
    protected function format($data)
    {
        if (is_array($data) && ! empty($data)) {
            return $this->getDriver()->format($data);
        }

        return [];
    }

    /**
     * Merge data collection.
     *
     * @param  \Illuminate\Support\Collection $collection
     * @param  array $data
     * @return void
     */
    protected function mergeCollection(&$collection, $data)
    {
        $collection = $collection->merge($data);
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
        if ($this->config('cache.store_to_cache')
            && $collection instanceof Collection
            && $collection->isNotEmpty()) {
            $this->cache()
                //->tags($this->config('cache.tags'))
                ->put($ipAddress, $collection->toArray(), $this->config('cache.ttl'));
        }
    }

    /**
     * Retrieve collection from cache.
     *
     * @param  string $ipAddress
     * @return array
     */
    protected function retrieveFromCache($ipAddress)
    {
        return $this->cache()
            //->tags($this->config('cache.tags'))
            ->get($ipAddress);
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
