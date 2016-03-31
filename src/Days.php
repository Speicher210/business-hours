<?php

namespace Speicher210\BusinessHours;

/**
 * Days enumeration.
 */
class Days
{
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;
    const SUNDAY = 7;

    private static $strings = array(
        self::MONDAY => 'Monday',
        self::TUESDAY => 'Tuesday',
        self::WEDNESDAY => 'Wednesday',
        self::THURSDAY => 'Thursday',
        self::FRIDAY => 'Friday',
        self::SATURDAY => 'Saturday',
        self::SUNDAY => 'Sunday'
    );

    /**
     * Returns a string representation of a day.
     *
     * @param int $dayOfWeek
     *
     * @return string|null
     */
    public static function toString($dayOfWeek)
    {
        return isset(self::$strings[$dayOfWeek]) ? self::$strings[$dayOfWeek] : null;
    }

    /**
     * Returns an array of days.
     *
     * @return int[]
     */
    public static function toArray()
    {
        return array(self::MONDAY, self::TUESDAY, self::WEDNESDAY, self::THURSDAY, self::FRIDAY, self::SATURDAY, self::SUNDAY);
    }

    private function __construct()
    {
    }
}
