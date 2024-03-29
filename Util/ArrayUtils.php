<?php

namespace ITE\Common\Util;

use ITE\Common\Exception\InvalidArgumentException;
use ITE\Common\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathBuilder;
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
     * @return int|string|null
     */
    public static function getFirstKey(array $array)
    {
        if (empty($array)) {
            return null;
        }

        reset($array);

        return key($array);
    }

    /**
     * @param array $array
     * @return mixed|null
     */
    public static function getFirstValue(array $array)
    {
        if (empty($array)) {
            return null;
        }

        return reset($array);
    }

    /**
     * @param array $array
     * @return int|string|null
     */
    public static function getLastKey(array $array)
    {
        if (empty($array)) {
            return null;
        }

        end($array);
        // no need to reset because it is copy of array

        return key($array);
    }

    /**
     * @param array $array
     * @return mixed|null
     */
    public static function getLastValue(array $array)
    {
        if (empty($array)) {
            return null;
        }

        // no need to reset because it is copy of array

        return end($array);
    }

    /**
     * @param array $array
     * @param \Closure $callback
     * @return array
     */
    public static function partition(array $array, \Closure $callback)
    {
        $matches = $noMatches = [];
        foreach ($array as $key => $element) {
            if ($callback($element, $key)) {
                $matches[$key] = $element;
            } else {
                $noMatches[$key] = $element;
            }
        }

        return [$matches, $noMatches];
    }

    /**
     * @param array $array
     * @param \Closure $callback
     * @return mixed
     */
    public static function total(array $array, \Closure $callback, $total = 0)
    {
        foreach ($array as $key => $element) {
            $total = $callback($total, $element, $key);
        }

        return $total;
    }

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
     * @param array $array
     * @param callable $callable
     */
    public static function removeByCallable(array &$array, $callable)
    {
        foreach ($array as $key => $value) {
            if (true === call_user_func_array($callable, [$value, $key])) {
                unset($array[$key]);
            }
        }
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
     * @param $callback
     * @param array $array
     * @return array
     */
    public static function arrayMapKeys($callback, array $array)
    {
        $keys = array_map(function ($key) use ($callback, $array) {
            return call_user_func($callback, $key);
        }, array_keys($array));

        return array_combine($keys, array_values($array));
    }
    
    /**
     * @param $callback
     * @param array $array
     * @return array
     */
    public static function arrayMapKeyRecursive($callback, array $array)
    {
        $ret = array_combine(array_map($callback, array_keys($array)), array_values($array));

        foreach ($ret as &$item) {
            if (is_array($item)) {
                $item = self::arrayMapKeyRecursive($callback, $item);
            }
        }

        return $ret;
    }

    public static function transform(array $array, callable $callback): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            [$newKey, $newValue] = $callback($value, $key);

            $result[$newKey] = $newValue;
        }

        return $result;
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
     * @param array $keys
     * @return array
     */
    public static function arrayDiffKey(array $array, array $keys)
    {
        return array_diff_key($array, array_flip($keys));
    }

    /**
     * @param array $array
     * @param string $offset
     * @param null $length
     * @param mixed $replacement
     * @return array
     */
    public static function arraySpliceAssoc(array &$array, $offset, $length, $replacement)
    {
        $replacement = (array) $replacement;

        $keyIndices = array_flip(array_keys($array));
        if (isset($array[$offset]) && is_string($offset)) {
            $offset = $keyIndices[$offset];
        }
        if (isset($array[$length]) && is_string($length)) {
            $length = $keyIndices[$length] - $offset;
        }

        $array = array_slice($array, 0, $offset, true)
        + $replacement
        + array_slice($array, $offset + $length, null, true);

        return $array;
    }

    /**
     * @param array $array
     * @return mixed
     */
    public static function arrayShiftAssoc(array &$array)
    {
        $value = reset($array);
        unset($array[key($array)]);

        return $value;
    }

    /**
     * @param array $array
     * @param callable $callback
     * @return bool
     */
    public static function exists(array $array, $callback)
    {
        foreach ($array as $i => $value) {
            if (call_user_func_array($callback, [$value, $i])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $array
     * @param callable $callback
     * @return bool
     */
    public static function forAll(array $array, $callback)
    {
        foreach ($array as $i => $value) {
            if (!call_user_func_array($callback, [$value, $i])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $array
     * @param int|string $key
     * @param bool $strict
     * @param null $defaultValue
     * @return array
     */
    public static function unwrapByKey(array $array, $key, $strict = true, $defaultValue = null)
    {
        return array_map(function ($value) use ($key, $strict, $defaultValue) {
            if (!is_array($value)) {
                throw new UnexpectedTypeException($value, 'array');
            }
            if (!array_key_exists($key, $value)) {
                if ($strict) {
                    throw new InvalidArgumentException(sprintf('Key "%s" is missing.'));
                } else {
                    return $defaultValue;
                }
            } else {
                return $value[$key];
            }
        }, $array);
    }

    /**
     * @param array $array
     * @param string $propertyPath
     * @param bool $strict
     * @param null $defaultValue
     * @return array
     */
    public static function unwrapByPropertyPath(array $array, $propertyPath, $strict = true, $defaultValue = null)
    {
        $propertyAccessor = self::getPropertyAccessor();

        return array_map(function ($value) use ($propertyAccessor, $propertyPath, $strict, $defaultValue) {
            if (!is_array($value) && !is_object($value)) {
                throw new UnexpectedTypeException($value, 'object or array');
            }
            try {
                return $propertyAccessor->getValue($value, $propertyPath);
            } catch (\Exception $e) {
                if ($strict) {
                    throw $e;
                }

                return $defaultValue;
            }
        }, $array);
    }

    /**
     * @param array $array
     * @param string $key
     * @param bool $removeKey
     * @return array
     */
    public static function indexByKey(array $array, $key, $removeKey = false)
    {
        $result = [];
        foreach ($array as $i => $value) {
            $index = $value[$key];

            if ($removeKey) {
                unset($value[$key]);
            }
            $result[$index] = $value;
        }

        return $result;
    }

    /**
     * @param array $array
     * @param string|PropertyPathInterface $propertyPath
     * @param bool $strict
     * @param mixed $defaultValue
     * @return array
     * @throws \Exception
     */
    public static function indexByPropertyPath(array $array, $propertyPath, $strict = true, $defaultValue = null)
    {
        $propertyAccessor = self::getPropertyAccessor();

        $result = [];
        foreach ($array as $i => $value) {
            try {
                $propertyValue = $propertyAccessor->getValue($value, $propertyPath);
            } catch (\Exception $e) {
                if ($strict) {
                    throw $e;
                }

                $propertyValue = $defaultValue;
            }
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

    public static function sort(array &$array, string $direction = 'asc', callable $comparisonFunction = null): bool
    {
        $result = uasort($array, function ($a, $b) use ($comparisonFunction) {
            if (is_callable($comparisonFunction)) {
                return call_user_func_array($comparisonFunction, [$a, $b]);
            } else {
                if ($a == $b) {
                    return 0;
                }

                return $a > $b ? 1 : -1;
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
                }

                return $aValue > $bValue ? 1 : -1;
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
     * @param array $fields
     * @return bool
     */
    public static function multiSortByKey(
        array &$array,
        array $fields
    ) {
        return uasort($array, function ($a, $b) use ($fields) {
            $result = 0;
            foreach ($fields as $key => $options) {
                if (is_string($options)) {
                    $direction = $options;
                    $comparisonFunction = null;
                } elseif (is_array($options)) {
                    $direction = $options['direction'];
                    $comparisonFunction = self::getValue($options, 'comparison_function', null);
                } else {
                    throw new UnexpectedTypeException($options, 'string or array');
                }

                $aValue = $a[$key];
                $bValue = $b[$key];

                if (is_callable($comparisonFunction)) {
                    $result = call_user_func_array($comparisonFunction, [$aValue, $bValue]);
                } else {
                    if ($aValue == $bValue) {
                        $result = 0;
                    } else {
                        $result = $aValue > $bValue ? 1 : -1;
                    }
                }
                if ('desc' === $direction) {
                    $result *= -1;
                }
                if (0 !== $result) {
                    break;
                }
            }

            return $result;
        });
    }

    /**
     * @param array $array
     * @param array $fields
     * @return bool
     */
    public static function multiSortByPropertyPath(
        array &$array,
        array $fields
    ) {
        $propertyAccessor = self::getPropertyAccessor();

        return uasort($array, function ($a, $b) use ($propertyAccessor, $fields) {
            $result = 0;
            foreach ($fields as $propertyPath => $options) {
                if (is_string($options)) {
                    $direction = $options;
                    $comparisonFunction = null;
                } elseif (is_array($options)) {
                    $direction = $options['direction'];
                    $comparisonFunction = self::getValue($options, 'comparison_function', null);
                } else {
                    throw new UnexpectedTypeException($options, 'string or array');
                }

                $aValue = $propertyAccessor->getValue($a, $propertyPath);
                $bValue = $propertyAccessor->getValue($b, $propertyPath);

                if (is_callable($comparisonFunction)) {
                    $result = call_user_func_array($comparisonFunction, [$aValue, $bValue]);
                } else {
                    if ($aValue == $bValue) {
                        $result = 0;
                    } else {
                        $result = $aValue > $bValue ? 1 : -1;
                    }
                }
                if ('desc' === $direction) {
                    $result *= -1;
                }
                if (0 !== $result) {
                    break;
                }
            }

            return $result;
        });
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

                if ($groupValue instanceof \DateTime) {
                    $groupValue = $groupValue->format(\DateTime::ATOM);
                }

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
     * @param array $array
     * @param callable $callable
     * @param bool $caseSensitive
     * @return array
     */
    public static function groupByCallable(array $array, $callable, $caseSensitive = false)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $group = call_user_func_array($callable, [$value, $key]);
            if (is_string($group) && false === $caseSensitive) {
                $group = strtolower($group);
            }

            $result[$group][$key] = $value;
        }

        return $result;
    }

    /**
     * @param array $array
     * @param string|array $groupBy
     * @param bool $caseSensitive
     * @param bool $strict
     * @param mixed $defaultGroupValue
     * @return array
     */
    public static function groupByPropertyPath(
        array $array,
        $groupBy,
        $caseSensitive = false,
        $strict = true,
        $defaultGroupValue = null
    ) {
        $propertyAccessor = self::getPropertyAccessor();

        $groupPropertyPaths = is_array($groupBy) ? $groupBy : [$groupBy];
        $result = [];
        foreach ($array as $key => $value) {
            $groupValues = [];
            foreach ($groupPropertyPaths as $groupPropertyPath) {
                try {
                    $groupValue = $propertyAccessor->getValue($value, $groupPropertyPath);

                    if ($groupValue instanceof \DateTime) {
                        $groupValue = $groupValue->format(\DateTime::ATOM);
                    }
                } catch (\Exception $e) {
                    if ($strict) {
                        throw $e;
                    }

                    $groupValue = $defaultGroupValue;
                }

                if (is_string($groupValue) && false === $caseSensitive) {
                    $groupValue = strtolower($groupValue);
                }

                $groupValues[$groupPropertyPath] = $groupValue;
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
     * @param array $array
     * @param string $propertyPath
     * @return mixed
     */
    public static function maxByPropertyPath(array $array, $propertyPath)
    {
        return max(self::unwrapByPropertyPath($array, $propertyPath));
    }

    /**
     * @param array $array
     * @param string $propertyPath
     * @return mixed
     */
    public static function minByPropertyPath(array $array, $propertyPath)
    {
        return min(self::unwrapByPropertyPath($array, $propertyPath));
    }

    /**
     * @param array  $array
     * @param string $propertyPath
     *
     * @return array
     */
    public static function arrayUniqueByPropertyPath(array $array, $propertyPath)
    {
        $accessor = self::getPropertyAccessor();
        $values = [];
        $keys = [];

        foreach ($array as $key => $item) {
            $value = $accessor->getValue($item, $propertyPath);

            if (isset($values[$value])) {
                continue;
            }

            $values[$value] = $item;
            $keys[$value] = $key;
        }

        return array_combine($keys, $values);
    }

    public static function arrayUnique(array $array): array
    {
        $uniqueArray = [];

        foreach ($array as $key => $value) {
            if (!in_array($value, $uniqueArray, true)) {
                $uniqueArray[$key] = $value;
            }
        }

        return $uniqueArray;
    }
    
    public static function unsetByPropertyPath(array &$array, $propertyPath): void
    {
        $accessor = self::getPropertyAccessor();
        $propertyPath = new PropertyPath($propertyPath);
        $propertyPathBuilder = new PropertyPathBuilder($propertyPath);
        $firstTime = true;

        for ($i = $propertyPath->getLength() - 1; $i >= 0; $i--) {
            $previousIndex = $propertyPath->getElement($i);
            $propertyPathBuilder->remove($i);
            if (0 === $propertyPathBuilder->getLength()) {
                if (empty($array[$previousIndex])) {
                    unset($array[$previousIndex]);
                }

                return;
            }
            $currentValue = $accessor->getValue($array, $propertyPathBuilder->getPropertyPath());

            if (!is_array($currentValue) || empty($currentValue)) {
                break;
            }
            if ($firstTime || empty($currentValue[$previousIndex])) {
                unset($currentValue[$previousIndex]);
                $firstTime = false;
            }
            $accessor->setValue($array, $propertyPathBuilder->getPropertyPath(), $currentValue);
        }
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
