<?php

namespace ITE\Common\Util;

/**
 * Class LocaleUtils
 */
class LocaleUtils
{
    /**
     * @var \NumberFormatter
     */
    protected static $numberFormatter;
    
    /**
     * @param null $precision
     * @return int
     */
    public static function getPrecision($precision = null)
    {
        if ($precision) {
            return $precision;
        }

        $formatter = self::getNumberFormatter();

        return $formatter->getAttribute(\NumberFormatter::MAX_FRACTION_DIGITS); // FRACTION_DIGITS ?
    }

    /**
     * @return int
     */
    public static function getGroupingSize()
    {
        $formatter = self::getNumberFormatter();

        return $formatter->getAttribute(\NumberFormatter::GROUPING_SIZE);
    }

    /**
     * @return string
     */
    public static function getGroupingSeparatorSymbol()
    {
        $formatter = self::getNumberFormatter();

        return $formatter->getSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL);
    }

    /**
     * @return string
     */
    public static function getDecimalSeparatorSymbol()
    {
        $formatter = self::getNumberFormatter();

        return $formatter->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
    }

    /**
     * @return \NumberFormatter
     */
    protected static function getNumberFormatter()
    {
        if (null === self::$numberFormatter) {
            self::$numberFormatter = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::DECIMAL);
        }

        return self::$numberFormatter;
    }
} 