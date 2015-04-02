<?php

namespace ITE\Common\Util;

/**
 * Class DateIntervalUtils
 * @package EXP\CoreBundle\Util
 */
class DateIntervalUtils
{
    /**
     * @param \DateInterval $a
     * @param \DateInterval $b
     * @return int
     */
    public static function compare(\DateInterval $a, \DateInterval $b)
    {
        $aSecs = self::toSecs($a);
        $bSecs = self::toSecs($b);

        if ($aSecs === $bSecs) {
            return 0;
        }

        return $aSecs > $bSecs ? 1 : -1;
    }

    /**
     * @param \DateInterval $interval
     * @return int
     */
    public static function toSecs(\DateInterval $interval)
    {
        if (false !== $interval->days) {
            return $interval->days * 24 * 60 * 60
            + $interval->h * 60 * 60
            + $interval->i * 60
            + $interval->s;
        }

        return $interval->y * 365 * 24 * 60 * 60
        + $interval->m * 30 * 24 * 60 * 60
        + $interval->d * 24 * 60 * 60
        + $interval->h * 60 * 60
        + $interval->i * 60
        + $interval->s;
    }
} 