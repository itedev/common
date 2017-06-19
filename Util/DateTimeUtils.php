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
    public static function compare(\DateTime $a, \DateTime $b)
    {
        if ($a == $b) {
            return 0;
        }

        return $a < $b ? -1 : 1;
    }

    /**
     * @param \DateTime|null $date
     * @param \DateTime|null $time
     * @return \DateTime|null
     */
    public static function createFromDateAndTime(\DateTime $date = null, \DateTime $time = null)
    {
        if (null === $date || null === $time) {
            return null;
        }

        $dateTime = clone $date;
        $dateTime->setTimezone($time->getTimezone());
        $dateTime->setTime(
            (int) $time->format('H'),
            (int) $time->format('i'),
            (int) $time->format('s')
        );

        //$dateTime = clone $time;
        //$dateTime->setDate(
        //    (int) $time->format('Y'),
        //    (int) $time->format('n'),
        //    (int) $time->format('j')
        //);

        return $dateTime;
    }
}
