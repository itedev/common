<?php

namespace ITE\Common\Util;

/**
 * Class DateTimeUtils
 *
 * @author sam0delkin <t.samodelkin@gmail.com>
 */
class DateTimeUtils
{
    /**
     * Return choices array of week days for Form choice field.
     *
     * @param null $translationPrefix
     * @param bool $startsWithSunday
     *
     * @return array
     */
    public static function getWeekDayChoices($translationPrefix = null, $startsWithSunday = false)
    {
        $days = [
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
        ];

        if ($startsWithSunday) {
            array_unshift($days, 'sunday');
        } else {
            $days[] = 'sunday';
        }

        if (null === $translationPrefix) {
            return array_combine($days, $days);
        } else {
            $choices = [];

            foreach ($days as $day) {
                $choices[$day] = sprintf('%s.%s', $translationPrefix, $day);
            }

            return $choices;
        }
    }
}
