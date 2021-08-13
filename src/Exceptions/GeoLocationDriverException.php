<?php

namespace MakiDizajnerica\GeoLocation\Exceptions;

use Exception;

class GeoLocationDriverException extends Exception
{
    protected $driver;

    /**
     * @param  string $message
     * @param  string $driver
     * @return void
     */
    public function __construct($message, $driver = null)
    {
        $this->message = $message;
        $this->driver = $driver;

        parent::__construct($this->message);
    }

    /**
     * Get the exception's context information.
     *
     * @return array
     */
    public function context()
    {
        return ['driver' => $this->driver];
    }
}
