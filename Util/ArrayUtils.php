<?php

namespace ITE\Common\Util;

/**
 * Class ArrayUtils
 * @package ITE\FormBundle\Util
 */
class ArrayUtils
{
    /**
     * @return mixed
     */
    public static function replaceRecursive()
    {
        $arrays = func_get_args();
        $base = array_shift($arrays);

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (isset($base[$key]) && is_array($base[$key]) && is_array($value) && static::isAssociative($value)) {
                    $base[$key] = static::replaceRecursive($base[$key], $value);
                } else {
                    $base[$key] = $value;
                }
            }
        }

        return $base;
    }

    /**
     * @param $array
     * @return bool
     */
    public static function isAssociative(array $array)
    {
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }

    /**
     * @param $callback
     * @param array $array
     * @return array
     */
    public static function arrayMapKey($callback, array $array)
    {
        return array_map(function($key) use ($callback, $array) {
            return call_user_func_array($callback, array($array[$key], $key));
        }, array_keys($array));
    }

    /**
     * @param array $array
     * @param $callback
     * @return array
     */
    public static function arrayFilterKey(array $array, $callback)
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (call_user_func_array($callback, [$value, $key])) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @param array $array
     * @param array $keys
     * @return array
     */
    public static function arrayIntersectKey(array $array, array $keys)
    {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * preg_grep for array keys.
     *
     * @param string $pattern
     * @param array $input
     * @param int $flags
     * @return array
     */
    public static function pregGrepKeys($pattern, array $input, $flags = 0)
    {
        $values = [];
        $keys = preg_grep($pattern, array_keys($input), $flags);

        foreach ($keys as $key) {
            $values[$key] = $input[$key];
        }

        return $values;
    }

    /**
     * preg_grep for array values.
     *
     * @param string $pattern
     * @param array $input
     * @param int $flags
     * @return array
     */
    public static function pregGrepValues($pattern, array $input, $flags = 0)
    {
        return preg_grep($pattern, $input, $flags);
    }

    /**
     * Filter array by keys.
     *
     * @param $pattern
     * @param array $input
     * @param int $flags
     * @return array
     */
    public static function arrayKeysStartsFrom($pattern, array $input, $flags = 0)
    {
        $pattern = self::pregGrepPattern($pattern);

        return self::pregGrepKeys($pattern, $input, $flags);
    }

    /**
     * Filter array by values, that starts from $pattern
     *
     * @param string|array $pattern
     * @param array $input
     * @param int $flags
     * @return array
     */
    public static function arrayValuesStartsFrom($pattern, array $input, $flags = 0)
    {
        $pattern = self::pregGrepPattern($pattern);

        return self::pregGrepValues($pattern, $input, $flags);
    }

    /**
     * Creates pattern for preg_grep
     *
     * @param string|array $pattern
     * @return string
     */
    public static function pregGrepPattern($pattern)
    {
        if (is_array($pattern)) {
            $pattern = implode('|', $pattern);
        }

        return sprintf('/^%s?(\w+)/i', $pattern);
    }

    /**
     * Remove all elements after element with key = $key
     *
     * @param $key
     * @param array $array
     * @return array
     */
    public static function arrayRemoveAfter($key, array $array)
    {
        $position = array_search($key, array_keys($array));

        if ($position !== false) {
            array_splice($array, (++$position));
        }

        return $array;
    }

    /**
     * Generate key.
     *
     * @param array $array
     * @param string $key
     * @param int $start
     * @return string
     */
    public static function generateKey($array, $key, $start = 1)
    {
        $stringKey = sprintf('%s-%s', $key, $start);


        if(is_array($array) && array_key_exists($stringKey, $array)){
            return self::generateKey($array, $key, ++$start);
        }

        return $stringKey;
    }

    /**
     * Check for value existing recursively.
     *
     * @param $needle
     * @param $haystack
     * @param bool $strict
     * @return bool
     */
    public static function arrayHasValue($needle, $haystack, $strict = false) {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && self::arrayHasValue($needle, $item, $strict))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check for key existing recursively.
     * This function check all keys, not only keys with single values.
     *
     * @param $needle
     * @param $haystack
     * @param bool $strict
     * @return bool
     */
    public static function arrayHasKey($needle, $haystack, $strict = false) {
        foreach ($haystack as $key => $item) {
            if (($strict ? $key === $needle : $key == $needle) || (is_array($item) && self::arrayHasKey($needle, $item, $strict))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Applied lcfirst function to each key.
     *
     * @param array $array
     * @return array
     */
    public static function lcfirstKeys(array $array)
    {
        $changed = [];
        array_walk($array, function($val, $key) use(&$changed) {
            $changed[lcfirst($key)] = $val;
        });

        return $changed;
    }

    /**
     * Filter array by keys.
     *
     * @param array $array
     * @param array $keysToFilter
     * @return array
     */
    public static function keysFilter(array $array, array $keysToFilter = [])
    {
        return array_diff_key($array, array_flip($keysToFilter));
    }

    /**
     * Normalize case of array values.
     *
     * @param array $array
     * @param array $keys
     * @return array
     */
    public static function normalizeValuesCase(array $array, array $keys = [])
    {
        foreach($array as $key => $val) {
            if($val && (empty($keys) || in_array($key, $keys))) {
                $array[$key] = ucwords(strtolower($val));
            }
        }

        return $array;
    }
} 