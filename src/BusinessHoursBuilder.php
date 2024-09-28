<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours;

use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;
use Psl\Dict;
use Psl\Vec;
use Speicher210\BusinessHours\Day\Day;
use Speicher210\BusinessHours\Day\DayBuilder;
use Speicher210\BusinessHours\Day\DayInterface;
use Speicher210\BusinessHours\Day\Time\Time;
use Speicher210\BusinessHours\Day\Time\TimeInterval;

use function array_fill_keys;
use function is_array;
use function max;
use function min;

final class BusinessHoursBuilder
{
    /**
     * Build a BusinessHours from an array.
     *
     * @param mixed[] $data The business hours data.
     */
    public static function fromAssociativeArray(array $data): BusinessHours
    {
        if (! isset($data['days'], $data['timezone']) || ! is_array($data['days'])) {
            throw new InvalidArgumentException('Array is not valid.');
        }

        $days = [];
        foreach ($data['days'] as $day) {
            $days[] = DayBuilder::fromAssociativeArray($day);
        }

        return new BusinessHours($days, new DateTimeZone($data['timezone']));
    }

    /**
     * Create a new BusinessHours with a different timezone from an existing BusinessHours.
     *
     * Shifting requires information about the offset. The offset can vary depending on DST.
     * To cover this scenario, we require a DateTimeInterface to be passed for extracting offset information.
     *
     * Example:
     *  Opening time of 10:00 with timezone UTC means either 11:00 or 12:00 for Europe/Berlin depending on the date.
     */
    public static function shiftToTimezone(BusinessHoursInterface $businessHours, DateTimeInterface $dateTime): BusinessHoursInterface
    {
        $oldTimezone = $businessHours->getTimezone();
        $newTimezone = $dateTime->getTimezone();

        $offset = $dateTime->getOffset() - $oldTimezone->getOffset($dateTime);

        if ($offset === 0) {
            return clone $businessHours;
        }

        $tmpDays = array_fill_keys(Day::getDaysOfWeek(), []);
        foreach ($businessHours->getDays() as $day) {
            foreach ($day->getOpeningHoursIntervals() as $interval) {
                $start = $interval->getStart()->toSeconds() + $offset;
                $end   = $interval->getEnd()->toSeconds() + $offset;

                // Current day.
                if ($start < 86400 && $end > 0) {
                    $startForCurrentDay = max($start, 0);
                    $endForCurrentDay   = min($end, 86400);

                    $dayOfWeek             = $day->getDayOfWeek();
                    $interval              = new TimeInterval(
                        Time::fromSeconds($startForCurrentDay),
                        Time::fromSeconds($endForCurrentDay),
                    );
                    $tmpDays[$dayOfWeek][] = $interval;
                }

                // Previous day.
                if ($start < 0) {
                    $startForPreviousDay = 86400 + $start;
                    $endForPreviousDay   = min(86400, 86400 + $end);

                    $dayOfWeek             = self::getPreviousDayOfWeek($day->getDayOfWeek());
                    $interval              = new TimeInterval(
                        Time::fromSeconds($startForPreviousDay),
                        Time::fromSeconds($endForPreviousDay),
                    );
                    $tmpDays[$dayOfWeek][] = $interval;
                }

                // Next day.
                if ($end <= 86400) {
                    continue;
                }

                $startForNextDay = max(0, $start - 86400);
                $endForNextDay   = $end - 86400;

                $dayOfWeek             = self::getNextDayOfWeek($day->getDayOfWeek());
                $interval              = new TimeInterval(
                    Time::fromSeconds($startForNextDay),
                    Time::fromSeconds($endForNextDay),
                );
                $tmpDays[$dayOfWeek][] = $interval;
            }
        }

        $days = Vec\map_with_key(
            Dict\sort_by_key(
                Dict\filter($tmpDays, static fn (array $intervals): bool => $intervals !== []),
            ),
            static fn (int $dayOfWeek, array $intervals): Day => DayBuilder::fromArray($dayOfWeek, $intervals),
        );

        return new BusinessHours($days, $newTimezone);
    }

    /**
     * Get previous day of week for a given day of week.
     *
     * @param int $dayOfWeek The day of week.
     */
    private static function getPreviousDayOfWeek(int $dayOfWeek): int
    {
        return $dayOfWeek === DayInterface::WEEK_DAY_MONDAY ? DayInterface::WEEK_DAY_SUNDAY : --$dayOfWeek;
    }

    /**
     * Get next day of week for a given day of week.
     *
     * @param int $dayOfWeek The day of week.
     */
    private static function getNextDayOfWeek(int $dayOfWeek): int
    {
        return $dayOfWeek === DayInterface::WEEK_DAY_SUNDAY ? DayInterface::WEEK_DAY_MONDAY : ++$dayOfWeek;
    }
}
