<?php

namespace ITE\Common\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use ITE\Common\Annotation\Metadata\AnnotationMetadata;
use Symfony\Component\ClassLoader\ClassMapGenerator;

/**
 * Class AnnotationFinder
 *
 * @package ITE\Common\Annotation
 * @author  sam0delkin <t.samodelkin@gmail.com>
 */
class AnnotationFinder
{
    /**
     * Find all annotations in given directory/directories.
     *
     * @param string|array     $dir         The dir(s) to find annotations in.
     * @param bool             $recursively Specify is files will be found recursively.
     * @param AnnotationReader $annotationReader Annotation reader instance
     *
     * @return Metadata\AnnotationMetadata[] Metadata, contains all annotations data.
     */
    public function findAnnotationsInDir($dir, $recursively = false, AnnotationReader $annotationReader = null)
    {
        $iterator = $recursively ? new \RecursiveDirectoryIterator($dir) : new \DirectoryIterator($dir);
        $classes  = array_keys(ClassMapGenerator::createMap($iterator));

        $annotations = [];

        foreach ($classes as $className) {
            $annotations[$className] = new AnnotationMetadata($className, $annotationReader);
        }

        return $annotations;
    }
}