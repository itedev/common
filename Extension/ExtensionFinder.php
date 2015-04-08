<?php

namespace ITE\Common\Extension;

use Symfony\Component\ClassLoader\ClassMapGenerator;

/**
 * Class ExtensionFinder
 *
 * @package ITE\Common\Extension
 * @author  sam0delkin <t.samodelkin@gmail.com>
 */
class ExtensionFinder
{
    /**
     * Find all classes, that extending base class or implementing interface in given dir.
     *
     * @param string      $dir                The directory to search in.
     * @param string      $extensionInterface Class or Interface name to find extensions to.
     * @param string|null $ownDir             If specified, then files located in ownDir will be ignored.
     *
     * @return array The array of Class names.
     */
    public static function findExtensions($dir, $extensionInterface, $ownDir = null)
    {
        $ownDir   = $ownDir ? realpath($ownDir) : null;
        $classMap = ClassMapGenerator::createMap($dir);

        $extensions = [];

        foreach ($classMap as $className => $filePath) {
            if ($ownDir && strpos($filePath, $ownDir) !== false) {
                continue;
            }

            $reflected = new \ReflectionClass($className);

            if (!$reflected->isInstantiable()) {
               continue;
            }
            try {
                if ($reflected->newInstanceWithoutConstructor() instanceof $extensionInterface) {
                    $extensions[] = $className;
                }
            } catch (\ReflectionException $e) {
                continue;
            }
        }

        return $extensions;
    }

    /**
     * Load all extensions, that extending base class or implementing interface in given dir, using given loader.
     *
     * @param callable      $loader             Function that will be called to load extension.
     * @param string        $dir                The directory to search in.
     * @param string        $extensionInterface Class or Interface name to find extensions to.
     * @param string|null   $ownDir             If specified, then files located in ownDir will be ignored.
     * @param callable|null $builder If specified, then this function will be called to create extension
     *                               instance, new operator will be used otherwise.
     */
    public static function loadExtensions($loader, $dir, $extensionInterface, $ownDir = null, $builder = null)
    {
        if (!is_callable($loader)) {
            throw new \InvalidArgumentException(sprintf('Loader should be callable, "%s" given.', gettype($loader)));
        }

        $extensions = self::findExtensions($dir, $extensionInterface, $ownDir);

        foreach ($extensions as $extension) {
            if ($builder) {
                if (!is_callable($builder)) {
                    throw new \InvalidArgumentException(sprintf('Builder should be callable, "%s" given.',
                        gettype($builder))
                    );
                }
                $extensionInstance = call_user_func($builder, $extension);
            } else {
                $reflected = new \ReflectionClass($extension);
                $extensionInstance = $reflected->newInstanceWithoutConstructor();
            }

            call_user_func($loader, $extensionInstance);
        }
    }

}