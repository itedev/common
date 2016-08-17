<?php

namespace ITE\Common\Util;

/**
 * Class DateTimeUtils
 *
 * @author c1tru55 <mr.c1tru55@gmail.com>
 */
class DateTimeUtils
{
    /**
     * @param \DateTime $a
     * @param \DateTime $b
     * @return int
     */
    public function compare(\DateTime $a, \DateTime $b)
    {
        if ($a == $b) {
            return 0;
        }

        return $a < $b ? -1 : 1;
    }
}
