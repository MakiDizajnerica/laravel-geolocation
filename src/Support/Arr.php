<?php

namespace MakiDizajnerica\GeoLocation\Support;

use Illuminate\Support\Arr as LaravelArr;

class Arr
{
    /**
     * Merge two arrays by appending one array keys to other array values.
     *
     * @param  array $toAppendOn
     * @param  array $toAppend
     * @param  bool  $returnDefault
     * @param  mixed $default
     * @return array
     */
    public static function mergeByAppending(array $toAppendOn, array $toAppend, $returnDefault = false, $default = null)
    {
        return array_map(function ($value) use ($toAppend, $returnDefault, $default) {
            return LaravelArr::get(
                $toAppend, $value, $returnDefault
                    ? $default
                    : $value
            );
        }, $toAppendOn);
    }
}
