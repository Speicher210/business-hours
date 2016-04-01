<?php

namespace Speicher210\BusinessHours;

/**
 * Interface for time interval.
 */
interface TimeIntervalInterface extends \JsonSerializable
{
    /**
     * Checks if the interval contains the given time.
     *
     * @param Time $time The time to check.
     * @return boolean
     */
    public function contains(Time $time);

    /**
     * Get the start time.
     *
     * @return Time
     */
    public function getStart();

    /**
     * Get the end time.
     *
     * @return Time
     */
    public function getEnd();
}
