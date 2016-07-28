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
     * @param array $array
     * @param mixed $value
     * @return bool
     */
    public static function remove(array &$array, $value)
    {
        $key = array_search($value, $array, true);
        if ($key === false) {
            return false;
        }

        unset($array[$key]);

        return true;
    }

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
     * @param string $direction
     * @param callable|null $comparisonFunction
     * @return bool
     */
    public static function sortByKey(array &$array, $key, $direction = 'asc', $comparisonFunction = null)
    {
        $result = uasort($array, function ($a, $b) use ($key, $comparisonFunction) {
            $aValue = $a[$key];
            $bValue = $b[$key];

            if (is_callable($comparisonFunction)) {
                return call_user_func_array($comparisonFunction, [$aValue, $bValue]);
            } else {
                if ($aValue == $bValue) {
                    return 0;
                } else {
                    return $aValue > $bValue ? 1 : -1;
                }
            }
        });
        if ($result && 'desc' === $direction) {
            $array = array_reverse($array, true);
        }

        return $result;
    }

    /**
     * @param array $array
     * @param string $propertyPath
     * @param string $direction
     * @param callable|null $comparisonFunction
     * @return bool
     */
    public static function sortByPropertyPath(
        array &$array,
        $propertyPath,
        $direction = 'asc',
        $comparisonFunction = null
    ) {
        $propertyAccessor = self::getPropertyAccessor();
        $result = uasort($array, function ($a, $b) use ($propertyAccessor, $propertyPath, $comparisonFunction) {
            $aValue = $propertyAccessor->getValue($a, $propertyPath);
            $bValue = $propertyAccessor->getValue($b, $propertyPath);

            if (is_callable($comparisonFunction)) {
                return call_user_func_array($comparisonFunction, [$aValue, $bValue]);
            } else {
                if ($aValue == $bValue) {
                    return 0;
                } else {
                    return $aValue > $bValue ? 1 : -1;
                }
            }
        });
        if ($result && 'desc' === $direction) {
            $array = array_reverse($array, true);
        }

        return $result;
    }

    /**
     * @param array $array
     * @param string $key
     * @param mixed|null $defaultValue
     * @return mixed|null
     */
    public static function getValue(array $array, $key, $defaultValue = null)
    {
        return array_key_exists($key, $array)
            ? $array[$key]
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
     * @param string|array $groupBy
     * @param bool $caseSensitive
     * @return array
     */
    public static function groupBy(array $array, $groupBy, $caseSensitive = false)
    {
        $groupKeys = is_array($groupBy) ? $groupBy : [$groupBy];
        $result = [];
        foreach ($array as $key => $value) {
            $groupValues = [];
            foreach ($groupKeys as $groupKey) {
                $groupValue = $value[$groupKey];
                if (is_string($groupValue) && false === $caseSensitive) {
                    $groupValue = strtolower($groupValue);
                }

                $groupValues[$groupKey] = $groupValue;
            }
            $group = implode('-', $groupValues);

            $result[$group][$key] = $value;
        }

        return $result;
    }

    /**
     * @param array|object[] $array1
     * @param array|object[] $array2
     * @return array|object[]
     */
    public static function objectArrayDiff(array $array1, array $array2)
    {
        return array_udiff($array1, $array2, function ($object1, $object2) {
            return strcmp(spl_object_hash($object1), spl_object_hash($object2));
        });
    }

    /**
     * @return PropertyAccessor
     */
    protected static function getPropertyAccessor()
    {
        if (null === self::$propertyAccessor) {
            self::$propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
                ->enableExceptionOnInvalidIndex()
                ->getPropertyAccessor();
        }

        return self::$propertyAccessor;
    }
}
