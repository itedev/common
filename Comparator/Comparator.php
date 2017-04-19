<?php

namespace ITE\Common\Comparator;

/**
 * Class Comparator
 *
 * @author c1tru55 <mr.c1tru55@gmail.com>
 */
class Comparator
{
    /**
     * @param mixed $a
     * @param mixed $b
     * @param bool $strict
     * @return bool
     */
    public static function isChanged($a, $b, $strict = false)
    {
        if (null !== $a && null !== $b) {
            return $strict ? $a !== $b : $a != $b;
        } elseif (null === $a && null === $b) {
            return false;
        }

        return true;
    }
}
