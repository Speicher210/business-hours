<?php

namespace Speicher210\BusinessHours;

/**
 * Day interface.
 */
interface DayInterface extends \JsonSerializable
{
    const WEEK_DAY_MONDAY = 1;
    const WEEK_DAY_TUESDAY = 2;
    const WEEK_DAY_WEDNESDAY = 3;
    const WEEK_DAY_THURSDAY = 4;
    const WEEK_DAY_FRIDAY = 5;
    const WEEK_DAY_SATURDAY = 6;
    const WEEK_DAY_SUNDAY = 7;

    /**
     * Gets the day of week.
     *
     * @return integer
     */
    public function getDayOfWeek();

    /**
     * Get the name of the day.
     *
     * @return string
     */
    public function getDayOfWeekName();

    /**
     * Get the opening hours intervals.
     *
     * @return TimeInterval[]
     */
    public function getOpeningHoursIntervals();

    /**
     * Gets the opening time of the day.
     *
     * @return Time
     */
    public function getOpeningTime();

    /**
     * Gets the closing time of the day.
     *
     * @return Time
     */
    public function getClosingTime();

    /**
     * Get the closest opening hours interval for the given time (including it or in the future).
     *
     * @param Time $time The time.
     * @return TimeInterval|null
     */
    public function getClosestOpeningHoursInterval(Time $time);

    /**
     * Get the next opening hours interval excluding current (if inside of given time).
     *
     * @param Time $time The time.
     * @return TimeInterval|null
     */
    public function getNextOpeningHoursInterval(Time $time);

    /**
     * Checks if the given time is within opening hours of the day.
     *
     * @param Time $time The time
     * @return boolean
     */
    public function isWithinOpeningHours(Time $time);
}
