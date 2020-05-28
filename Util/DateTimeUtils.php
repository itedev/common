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
     * @param null $inputTimezone
     * @param null $outputTimezone
     *
     * @return \DateTime|false|null
     */
    public static function createFromDateAndTime(\DateTime $date = null, \DateTime $time = null, $inputTimezone = null, $outputTimezone = null)
    {
        if (null === $date || null === $time) {
            return null;
        }

        $inputTimezone = $inputTimezone ? new \DateTimeZone($inputTimezone) : new \DateTimeZone('UTC');
        $outputTimezone = $outputTimezone ? new \DateTimeZone($outputTimezone) : new \DateTimeZone('UTC');

        $dateTimeDate = clone $date;
        $dateTimeDate->setTimezone($outputTimezone);
        $dateTimeTime = clone $time;
        $dateTimeTime->setTimezone($outputTimezone);

        $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', sprintf(
            '%s-%s-%s %s:%s:%s',
            $dateTimeDate->format('Y'),
            $dateTimeDate->format('m'),
            $dateTimeDate->format('d'),
            $dateTimeTime->format('H'),
            $dateTimeTime->format('i'),
            $dateTimeTime->format('s')
        ), $outputTimezone);

        $dateTime->setTimezone($inputTimezone);

        //$dateTime = clone $time;
        //$dateTime->setDate(
        //    (int) $time->format('Y'),
        //    (int) $time->format('n'),
        //    (int) $time->format('j')
        //);

        return $dateTime;
    }
}
