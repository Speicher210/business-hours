<?php

namespace Speicher210\BusinessHours;

use Speicher210\BusinessHours\Day\AllDay;
use Speicher210\BusinessHours\Day\Day;
use Speicher210\BusinessHours\Day\DayBuilder;
use Speicher210\BusinessHours\Day\DayInterface;
use Speicher210\BusinessHours\Day\Time\TimeBuilder;
use Speicher210\BusinessHours\Day\Time\TimeInterval;
use Speicher210\BusinessHours\Day\Time\TimeIntervalInterface;

/**
 * Build a BusinessHours concrete implementation.
 */
class BusinessHoursBuilder
{
    /**
     * Build a BusinessHours from an array.
     *
     * @param array $data The business hours data.
     * @return BusinessHours
     */
    public static function fromAssociativeArray(array $data)
    {
        if (!isset($data['days']) || !is_array($data['days']) || !isset($data['timezone'])) {
            throw new \InvalidArgumentException('Array is not valid.');
        }

        $days = array();
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
    public static function shiftToTimezone(BusinessHours $businessHours, \DateTimeZone $newTimezone)
    {
        $oldTimezone = $businessHours->getTimezone();
        $offset = $newTimezone->getOffset(new \DateTime('now', $newTimezone)) - $oldTimezone->getOffset(new \DateTime('now', $oldTimezone));

        if ($offset === 0) {
            return clone $businessHours;
        }

        $tmpDays = array();
        foreach ($businessHours->getDays() as $day) {
            $currentDayIntervals = array();
            foreach ($day->getOpeningHoursIntervals() as $interval) {
                $start = $interval->getStart()->toSeconds() + $offset;
                $end = $interval->getEnd()->toSeconds() + $offset;

                // Current day.
                if ($start < 86400 && $end > 0) {
                    $startForCurrentDay = max($start, 0);
                    $endForCurrentDay = min($end, 86400);

                    $currentDayIntervals[] = new TimeInterval(TimeBuilder::fromSeconds($startForCurrentDay), TimeBuilder::fromSeconds($endForCurrentDay));
                }

                // Previous day.
                if ($start < 0) {
                    $startForPreviousDay = 86400 + $start;
                    $endForPreviousDay = min(86400, 86400 + $end);
                    $previousDayInterval = new TimeInterval(TimeBuilder::fromSeconds($startForPreviousDay), TimeBuilder::fromSeconds($endForPreviousDay));
                    $previousDayOfWeek = self::getPreviousDayOfWeek($day->getDayOfWeek());

                    if (isset($tmpDays[$previousDayOfWeek])) {
                        $tmpDays[$previousDayOfWeek] = array_merge($tmpDays[$previousDayOfWeek], array($previousDayInterval));
                    } else {
                        $tmpDays[$previousDayOfWeek] = array($previousDayInterval);
                    }

                }

                // Next day.
                if ($end > 86400) {
                    $startForNextDay = max(0, $start - 86400);
                    $endForNextDay = $end - 86400;
                    $nextDayInterval = new TimeInterval(TimeBuilder::fromSeconds($startForNextDay), TimeBuilder::fromSeconds($endForNextDay));
                    $nextDayOfWeek = self::getNextDayOfWeek($day->getDayOfWeek());
                    if (isset($tmpDays[$nextDayOfWeek])) {
                        $tmpDays[$nextDayOfWeek] = array_merge($tmpDays[$nextDayOfWeek], array($nextDayInterval));
                    } else {
                        $tmpDays[$nextDayOfWeek] = array($nextDayInterval);
                    }
                }
            };

            if (count($currentDayIntervals)) {
                if (isset($tmpDays[$day->getDayOfWeek()])) {
                    $tmpDays[$day->getDayOfWeek()] = array_merge($tmpDays[$day->getDayOfWeek()], $currentDayIntervals);
                } else {
                    $tmpDays[$day->getDayOfWeek()] = $currentDayIntervals;
                };
            }
        }

        ksort($tmpDays);

        $days = array();
        foreach ($tmpDays as $dayOfWeek => $intervals) {
            $day = new Day($dayOfWeek, $intervals);
            $tmpIntervals = $day->getOpeningHoursIntervals();

            /** @var TimeIntervalInterface $interval */
            $interval = reset($tmpIntervals);

            if (count($tmpIntervals) === 1
                && $interval->getStart()->getHours() === 0 && $interval->getStart()->getMinutes() === 0
                && $interval->getEnd()->getHours() === 24 && $interval->getEnd()->getMinutes() === 0
            ) {
                $day = new AllDay($dayOfWeek);
            }

            $days[] = $day;
        }

        return new BusinessHours($days, $newTimezone);
    }

    /**
     * Get previous day of week for a given day of week.
     *
     * @param integer $dayOfWeek The day of week.
     * @return integer
     */
    private static function getPreviousDayOfWeek($dayOfWeek)
    {
        return DayInterface::WEEK_DAY_MONDAY === $dayOfWeek ? DayInterface::WEEK_DAY_SUNDAY : --$dayOfWeek;
    }

    /**
     * Get next day of week for a given day of week.
     *
     * @param integer $dayOfWeek The day of week.
     * @return integer
     */
    private static function getNextDayOfWeek($dayOfWeek)
    {
        return DayInterface::WEEK_DAY_SUNDAY === $dayOfWeek ? DayInterface::WEEK_DAY_MONDAY : ++$dayOfWeek;
    }
}
