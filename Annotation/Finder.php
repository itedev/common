<?php
/**
 * This file is created by sam0delkin (t.samodelkin@gmail.com).
 * IT-Excellence (http://itedev.com)
 * Date: 02.04.2015
 * Time: 12:24
 */

namespace ITE\Common\Annotation;

use ITE\Common\Annotation\Metadata\AnnotationsMetadata;
use Symfony\Component\Finder\Finder as FileFinder;

/**
 * Class Finder
 *
 * @package ITE\Common\Annotation
 */
class Finder
{
    /**
     * Find all annotations in given directory/directories.
     *
     * @param string|array $dir         The dir(s) to find annotations in.
     * @param bool         $recursively Specify is files will be found recursively.
     * @return AnnotationsMetadata Metadata, contains all annotations data.
     */
    public function findAnnotationsInDir($dir, $recursively = false)
    {
        $finder      = new FileFinder();
        $annotations = [];

        if (!is_array($dir)) {
            $dir = [$dir];
        }

        $finder->files()->name('*.php')->in($dir);

        if (!$recursively) {
            $finder->depth('== 0');
        }

        foreach ($finder as $file) {
            $classes = $this->getFileClasses($file);

            foreach ($classes as $className) {
                $annotations[$className] = new AnnotationsMetadata($className);
            }
        }

        return $annotations;
    }

    /**
     * Return all PHP classes, declared in file.
     *
     * @param $fileName
     * @return array
     */
    protected function getFileClasses($fileName)
    {
        $code = file_get_contents($fileName);

        $classes = array();
        $tokens  = token_get_all($code);
        $count   = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i - 2][0] == T_CLASS
                && $tokens[$i - 1][0] == T_WHITESPACE
                && $tokens[$i][0] == T_STRING
            ) {

                $class_name = $tokens[$i][1];
                $classes[]  = $class_name;
            }
        }

        return $classes;
    }
}