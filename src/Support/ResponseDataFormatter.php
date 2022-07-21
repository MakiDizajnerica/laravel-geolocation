<?php

namespace MakiDizajnerica\GeoLocation\Support;

use MakiDizajnerica\GeoLocation\Support\Arr;

trait ResponseDataFormatter
{
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
            if (blank($format)) {
                return $data;
            }

            return Arr::mergeByAppending($format, $data, true, null);
        }

        return [];
    }
}
