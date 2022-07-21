<?php

namespace MakiDizajnerica\GeoLocation\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\RequestException;
use MakiDizajnerica\GeoLocation\Exceptions\GeoLocationDriverException;

trait HandlingHttpResponse
{
    /**
     * Get the geo location details for provided IP address
     * using http client.
     *
     * @param  string $ipAddress
     * @return array
     */
    protected function httpClientLookup($ipAddress)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->get($this->apiEndpoint($ipAddress));

        return $this->decodeResponseData($this->checkResponse($response));
    }

    /**
     * Check response from http client.
     *
     * @param  \Illuminate\Http\Client\Response $response
     * @return mixed
     * 
     * @throws \MakiDizajnerica\GeoLocation\Exceptions\GeoLocationDriverException
     */
    protected function checkResponse($response)
    {
        if ($response instanceof Response) {
            try {
                return $response->throw();
            }
            catch (RequestException $e) {
                throw new GeoLocationDriverException($e->getMessage(), static::class);
            }
        }

        return $response;
    }

    /**
     * Decode http client response data.
     *
     * @param  \Illuminate\Http\Client\Response $response
     * @return mixed
     */
    protected function decodeResponseData($response)
    {
        if ($response instanceof Response) {
            $data = json_decode($response = $response->getBody()->getContents(), true);

            // Sometimes the response can be a string which will result in
            // a JSON_ERROR so for this cases we do:
            if (json_last_error() !== JSON_ERROR_NONE) {
                $data = $response;
            }

            return Arr::wrap($data);
        }

        return $response;
    }
}
