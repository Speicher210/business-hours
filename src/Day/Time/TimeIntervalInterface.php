<?php

declare(strict_types = 1);

namespace Speicher210\BusinessHours\Day\Time;

/**
 * Interface for time interval.
 */
interface TimeIntervalInterface extends \JsonSerializable
{
    /**
     * Check if the interval contains the given time.
     *
     * @param Time $time The time to check.
     * @return boolean
     */
    public function contains(Time $time): bool;

    /**
     * Get the start time.
     *
     * @return Time
     */
    public function getStart(): Time;

    /**
     * Get the end time.
     *
     * @return Time
     */
    public function getEnd(): Time;
}
