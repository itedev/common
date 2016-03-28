<?php

namespace ITE\Common\Util;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * Class ArrayUtils
 */
class ArrayUtils
{
    /**
     * @var PropertyAccessor $propertyAccessor
     */
    protected static $propertyAccessor;

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
        return array_map(function ($key) use ($callback, $array) {
            return call_user_func_array($callback, [$array[$key], $key]);
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
     * @param array $array
     * @param string $key
     * @return array
     */
    public static function indexByKey(array $array, $key)
    {
        $result = [];
        foreach ($array as $i => $value) {
            $result[$value[$key]] = $value;
        }

        return $result;
    }

    /**
     * @param array $array
     * @param string|PropertyPathInterface $propertyPath
     * @return array
     */
    public static function indexByPropertyPath(array $array, $propertyPath)
    {
        $propertyAccessor = self::getPropertyAccessor();

        $result = [];
        foreach ($array as $i => $value) {
            $propertyValue = $propertyAccessor->getValue($value, $propertyPath);
            $result[$propertyValue] = $value;
        }

        return $result;
    }

    /**
     * @param array $array
     * @param callable $callable
     * @return array
     */
    public static function indexByCallable(array $array, $callable)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $index = call_user_func_array($callable, [$value, $key]);
            $result[$index] = $value;
        }

        return $result;
    }

    /**
     * @param array $array
     * @param string $key
     * @return bool
     */
    public static function sortByKey(array &$array, $key)
    {
        return usort($array, function ($a, $b) use ($key) {
            return strcmp($a[$key], $b[$key]);
        });
    }

    /**
     * @param array $array
     * @param string $propertyPath
     * @return bool
     */
    public static function sortByPropertyPath(array &$array, $propertyPath)
    {
        $propertyAccessor = self::getPropertyAccessor();
        return usort($array, function ($a, $b) use ($propertyAccessor, $propertyPath) {
            return strcmp(
                $propertyAccessor->getValue($a, $propertyPath),
                $propertyAccessor->getValue($b, $propertyPath)
            );
        });
    }

    /**
     * @param array $array
     * @param string $name
     * @param mixed|null $defaultValue
     * @return mixed|null
     */
    public static function getValue(array $array, $name, $defaultValue = null)
    {
        return array_key_exists($name, $array)
            ? $array[$name]
            : $defaultValue;
    }

    /**
     * @param array $array
     * @param string $propertyPath
     * @param mixed|null $defaultValue
     * @return mixed|null
     */
    public static function getValueByPropertyPath(array $array, $propertyPath, $defaultValue = null)
    {
        $propertyAccessor = self::getPropertyAccessor();

        try {
            return $propertyAccessor->getValue($array, $propertyPath);
        } catch (\Exception $e) {
            return $defaultValue;
        }
    }

    /**
     * @param array $array
     * @param string $groupName
     * @param bool $caseSensitive
     * @return array
     */
    public static function groupBy(array $array, $groupName, $caseSensitive = false)
    {
        $result = [];
        foreach ($array as $name => $value) {
            $groupValue = $value[$groupName];
            if (is_string($groupValue) && false === $caseSensitive) {
                $groupValue = strtolower($groupValue);
            }
            $result[$groupValue][$name] = $value;
        }

        return $result;
    }

    /**
     * @return PropertyAccessor
     */
    protected static function getPropertyAccessor()
    {
        if (null === self::$propertyAccessor) {
            self::$propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        return self::$propertyAccessor;
    }
}
