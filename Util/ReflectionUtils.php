<?php

namespace ITE\Common\Util;

/**
 * Class ReflectionUtils
 *
 * @author c1tru55 <mr.c1tru55@gmail.com>
 */
class ReflectionUtils
{
    /**
     * @param object $object
     * @param string $name
     * @return mixed
     */
    public static function getValue($object, $name)
    {
        $class = get_class($object);
        $refClass = new \ReflectionClass($class);
        while (!$refClass->hasProperty($name)) {
            $refClass = $refClass->getParentClass();
        }

        $refProp = $refClass->getProperty($name);
        $refProp->setAccessible(true);

        return $refProp->getValue($object);
    }

    /**
     * @param object $object
     * @param string $name
     * @param mixed $value
     */
    public static function setValue($object, $name, $value)
    {
        $class = get_class($object);
        $refClass = new \ReflectionClass($class);
        while (!$refClass->hasProperty($name)) {
            $refClass = $refClass->getParentClass();
        }

        $refProp = $refClass->getProperty($name);
        $refProp->setAccessible(true);

        $refProp->setValue($object, $value);
    }

    /**
     * @param object $object
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public static function callObjectMethod($object, $method, array $arguments = [])
    {
        $refObject = new \ReflectionObject($object);
        $method = $refObject->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $arguments);
    }
}