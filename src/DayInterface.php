<?php

namespace Speicher210\BusinessHours;

/**
 * Day interface.
 */
interface DayInterface
{
    /**
     * Gets the day of week.
     *
     * @return int
     */
    public function getDayOfWeek();

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
     * Checks if the given time is within opening hours of the day.
     *
     * @param Time $time The time
     * @return bool
     */
    public function isWithinOpeningHours(Time $time);
}
