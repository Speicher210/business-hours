<?php

declare(strict_types = 1);

namespace Speicher210\BusinessHours;

use Speicher210\BusinessHours\Day\Day;
use Speicher210\BusinessHours\Day\DayBuilder;
use Speicher210\BusinessHours\Day\DayInterface;
use Speicher210\BusinessHours\Day\Time\TimeBuilder;
use Speicher210\BusinessHours\Day\Time\TimeInterval;

/**
 * Build a BusinessHours concrete implementation.
 */
final class BusinessHoursBuilder
{
    /**
     * Build a BusinessHours from an array.
     *
     * @param array $data The business hours data.
     * @return BusinessHours
     */
    public static function fromAssociativeArray(array $data): BusinessHours
    {
        if (!isset($data['days'], $data['timezone']) || !\is_array($data['days'])) {
            throw new \InvalidArgumentException('Array is not valid.');
        }

        $days = [];
        foreach ($data['days'] as $day) {
            $days[] = DayBuilder::fromAssociativeArray($day);
        }

        return new BusinessHours($days, new \DateTimeZone($data['timezone']));
    }

    /**
     * Create a new BusinessHours with a different timezone from an existing BusinessHours.
     *
     * @param BusinessHours $businessHours The original business hours.
     * @param \DateTimeZone $newTimezone The new timezone.
     * @return BusinessHours
     */
    public static function shiftToTimezone(BusinessHours $businessHours, \DateTimeZone $newTimezone): BusinessHours
    {
        $now = new \DateTime('now');
        $oldTimezone = $businessHours->getTimezone();
        $offset = $newTimezone->getOffset($now) - $oldTimezone->getOffset($now);

        if ($offset === 0) {
            return clone $businessHours;
        }

        $tmpDays = \array_fill_keys(Day::getDaysOfWeek(), []);
        foreach ($businessHours->getDays() as $day) {
            foreach ($day->getOpeningHoursIntervals() as $interval) {
                $start = $interval->getStart()->toSeconds() + $offset;
                $end = $interval->getEnd()->toSeconds() + $offset;

                // Current day.
                if ($start < 86400 && $end > 0) {
                    $startForCurrentDay = \max($start, 0);
                    $endForCurrentDay = \min($end, 86400);

                    $dayOfWeek = $day->getDayOfWeek();
                    $interval = new TimeInterval(
                        TimeBuilder::fromSeconds($startForCurrentDay),
                        TimeBuilder::fromSeconds($endForCurrentDay)
                    );
                    $tmpDays[$dayOfWeek][] = $interval;
                }

                // Previous day.
                if ($start < 0) {
                    $startForPreviousDay = 86400 + $start;
                    $endForPreviousDay = \min(86400, 86400 + $end);

                    $dayOfWeek = self::getPreviousDayOfWeek($day->getDayOfWeek());
                    $interval = new TimeInterval(
                        TimeBuilder::fromSeconds($startForPreviousDay),
                        TimeBuilder::fromSeconds($endForPreviousDay)
                    );
                    $tmpDays[$dayOfWeek][] = $interval;
                }

                // Next day.
                if ($end > 86400) {
                    $startForNextDay = \max(0, $start - 86400);
                    $endForNextDay = $end - 86400;

                    $dayOfWeek = self::getNextDayOfWeek($day->getDayOfWeek());
                    $interval = new TimeInterval(
                        TimeBuilder::fromSeconds($startForNextDay),
                        TimeBuilder::fromSeconds($endForNextDay)
                    );
                    $tmpDays[$dayOfWeek][] = $interval;
                }
            }
        }

        $tmpDays = \array_filter($tmpDays);
        $days = self::flattenDaysIntervals($tmpDays);

        return new BusinessHours($days, $newTimezone);
    }

    /**
     * Flatten days intervals.
     *
     * @param array $days The days to flatten.
     * @return DayInterface[]
     */
    private static function flattenDaysIntervals(array $days): array
    {
        \ksort($days);

        $flattenDays = [];
        foreach ($days as $dayOfWeek => $intervals) {
            $flattenDays[] = DayBuilder::fromArray($dayOfWeek, $intervals);
        }

        return $flattenDays;
    }

    /**
     * Get previous day of week for a given day of week.
     *
     * @param integer $dayOfWeek The day of week.
     * @return integer
     */
    private static function getPreviousDayOfWeek($dayOfWeek): int
    {
        return DayInterface::WEEK_DAY_MONDAY === $dayOfWeek ? DayInterface::WEEK_DAY_SUNDAY : --$dayOfWeek;
    }

    /**
     * Get next day of week for a given day of week.
     *
     * @param integer $dayOfWeek The day of week.
     * @return integer
     */
    private static function getNextDayOfWeek($dayOfWeek): int
    {
        return DayInterface::WEEK_DAY_SUNDAY === $dayOfWeek ? DayInterface::WEEK_DAY_MONDAY : ++$dayOfWeek;
    }
}
