<?php

namespace Speicher210\BusinessHours\Day;

use Speicher210\BusinessHours\Day\Time\Time;
use Speicher210\BusinessHours\Day\Time\TimeBuilder;
use Speicher210\BusinessHours\Day\Time\TimeInterval;

/**
 * Build a DayInterface concrete implementation.
 */
class DayBuilder
{
    /**
     * Create a new Day.
     *
     * @param integer $dayOfWeek The day of week.
     * @param array $openingIntervals The opening intervals.
     * @return Day
     */
    public static function fromArray($dayOfWeek, array $openingIntervals)
    {
        $intervals = array();
        foreach ($openingIntervals as $interval) {
            $intervals[] = new TimeInterval(
                TimeBuilder::fromString($interval[0]),
                TimeBuilder::fromString($interval[1])
            );
        }

        return new Day($dayOfWeek, $intervals);
    }

    /**
     * Create a DayInterface object from an array.
     *
     * @param array $data The day data.
     * @return DayInterface
     */
    public static function fromAssociativeArray(array $data)
    {
        if (!isset($data['openingIntervals']) || !is_array($data['openingIntervals']) || !isset($data['dayOfWeek'])) {
            throw new \InvalidArgumentException('Array is not valid.');
        }

        $openingIntervals = array();
        foreach ($data['openingIntervals'] as $openingInterval) {
            $start = TimeBuilder::fromArray($openingInterval['start']);
            $end = TimeBuilder::fromArray($openingInterval['end']);
            if (self::isIntervalAllDay($start, $end)) {
                return new AllDay($data['dayOfWeek']);
            }

            $openingIntervals[] = new TimeInterval($start, $end);
        }

        return new Day($data['dayOfWeek'], $openingIntervals);
    }

    /**
     * Check if an interval array is all day.
     *
     * @param Time $start The start time.
     * @param Time $end The end time.
     * @return boolean
     */
    private static function isIntervalAllDay(Time $start, Time $end)
    {
        if ($start->getHours() !== 0 || $start->getMinutes() !== 0 || $start->getSeconds() !== 0) {
            return false;
        }

        if ($end->getHours() !== 24 || $end->getMinutes() !== 0 || $end->getSeconds() !== 0) {
            return false;
        }

        return true;
    }
}