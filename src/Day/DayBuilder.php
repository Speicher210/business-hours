<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours\Day;

use InvalidArgumentException;
use Speicher210\BusinessHours\Day\Time\Time;
use Speicher210\BusinessHours\Day\Time\TimeBuilder;
use Speicher210\BusinessHours\Day\Time\TimeInterval;
use Speicher210\BusinessHours\Day\Time\TimeIntervalInterface;
use function assert;
use function is_array;
use function reset;

/**
 * Build a DayInterface concrete implementation.
 */
final class DayBuilder
{
    /**
     * Create a new Day.
     *
     * @param int     $dayOfWeek        The day of week.
     * @param mixed[] $openingIntervals The opening intervals.
     */
    public static function fromArray(int $dayOfWeek, array $openingIntervals) : Day
    {
        $intervals = [];
        foreach ($openingIntervals as $interval) {
            if ($interval instanceof TimeIntervalInterface) {
                $intervals[] = $interval;
            } elseif (is_array($intervals)) {
                $intervals[] = new TimeInterval(
                    TimeBuilder::fromString($interval[0]),
                    TimeBuilder::fromString($interval[1])
                );
            }
        }

        $day          = new Day($dayOfWeek, $intervals);
        $dayIntervals = $day->getOpeningHoursIntervals();
        $dayInterval  = reset($dayIntervals);
        assert($dayInterval instanceof TimeIntervalInterface);
        if (self::isIntervalAllDay($dayInterval->getStart(), $dayInterval->getEnd())) {
            return new AllDay($dayOfWeek);
        }

        return $day;
    }

    /**
     * Create a DayInterface object from an array.
     *
     * @param mixed[] $data The day data.
     */
    public static function fromAssociativeArray(array $data) : DayInterface
    {
        if (! isset($data['openingIntervals'], $data['dayOfWeek']) || ! is_array($data['openingIntervals'])) {
            throw new InvalidArgumentException('Array is not valid.');
        }

        $openingIntervals = [];
        foreach ($data['openingIntervals'] as $openingInterval) {
            if (! isset($openingInterval['start'], $openingInterval['end'])) {
                throw new InvalidArgumentException('Array is not valid.');
            }

            $start = TimeBuilder::fromArray($openingInterval['start']);
            $end   = TimeBuilder::fromArray($openingInterval['end']);
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
     * @param Time $end   The end time.
     */
    private static function isIntervalAllDay(Time $start, Time $end) : bool
    {
        if ($start->getHours() !== 0 || $start->getMinutes() !== 0 || $start->getSeconds() !== 0) {
            return false;
        }

        return $end->getHours() === 24 && $end->getMinutes() === 0 && $end->getSeconds() === 0;
    }
}
