<?php

namespace ITE\Common\Annotation\Metadata;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;

/**
 * Class AnnotationMetadata
 *
 * @author  sam0delkin <t.samodelkin@gmail.com>
 */
class AnnotationMetadata
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @var array
     */
    protected $classAnnotations;

    /**
     * @var array
     */
    protected $methodAnnotations;

    /**
     * @var array
     */
    protected $propertyAnnotations;

    /**
     * @var AnnotationReader
     */
    private $reader;

    /**
     * @var \ReflectionClass
     */
    private $reflected;

    /**
     * @param string $className
     * @param Reader $reader
     */
    public function __construct($className, Reader $reader = null)
    {
        $this->className = $className;
        $this->reader    = $reader ?: new AnnotationReader();
        $this->reflected = new \ReflectionClass($className);
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return \ReflectionClass
     */
    public function getReflected()
    {
        return $this->reflected;
    }

    /**
     * @return array
     */
    public function getClassAnnotations()
    {
        $this->loadClassAnnotations();

        return $this->classAnnotations;
    }

    /**
     * @param $annotationName
     * @return object|null
     */
    public function getClassAnnotation($annotationName)
    {
        $annotations = $this->getClassAnnotations();
        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * @param string|null $methodName
     * @return array
     */
    public function getMethodAnnotations($methodName = null)
    {
        $this->loadMethodAnnotations();

        if (!$methodName) {
            return $this->methodAnnotations;
        }

        if (!isset($this->methodAnnotations[$methodName])) {
            return [];
        }

        return $this->methodAnnotations[$methodName];
    }

    /**
     * @param $methodName
     * @param $annotationName
     * @return object|null
     */
    public function getMethodAnnotation($methodName, $annotationName)
    {
        $annotations = $this->getMethodAnnotations($methodName);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public function getPropertiesAnnotations()
    {
        $this->loadPropertyAnnotations();

        return $this->propertyAnnotations;
    }

    /**
     * @param string|null $propertyName
     * @return array
     */
    public function getPropertyAnnotations($propertyName = null)
    {
        $this->loadPropertyAnnotations();

        if (!$propertyName) {
            return $this->propertyAnnotations;
        }

        if (!isset($this->propertyAnnotations[$propertyName])) {
            return [];
        }

        return $this->propertyAnnotations[$propertyName];
    }

    /**
     * @param $propertyName
     * @param $annotationName
     * @return object|null
     */
    public function getPropertyAnnotation($propertyName, $annotationName)
    {
        $annotations = $this->getPropertyAnnotations($propertyName);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }

    protected function loadClassAnnotations()
    {
        if ($this->classAnnotations !== null) {
            return;
        }

        $this->classAnnotations = $this->reader->getClassAnnotations($this->reflected);
    }

    protected function loadPropertyAnnotations()
    {
        if ($this->propertyAnnotations !== null) {
            return;
        }

        $this->propertyAnnotations = [];
        foreach ($this->reflected->getProperties() as $reflectionProperty) {
            $this->propertyAnnotations[$reflectionProperty->getName()] = $this->reader->getPropertyAnnotations(
                $reflectionProperty
            );
        }
    }

    protected function loadMethodAnnotations()
    {
        if ($this->methodAnnotations !== null) {
            return;
        }

        $this->methodAnnotations = [];
        foreach ($this->reflected->getMethods() as $reflectionMethod) {
            $this->methodAnnotations[$reflectionMethod->getName()] = $this->reader->getMethodAnnotations(
                $reflectionMethod
            );
        }
    }

}
