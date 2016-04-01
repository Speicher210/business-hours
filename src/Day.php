<?php

namespace Speicher210\BusinessHours;

/**
 * Represents a standard business day.
 */
class Day extends AbstractDay
{
    /**
     * Get the days of week.
     *
     * @return int[]
     */
    public static function getDaysOfWeek()
    {
        return array(
            self::WEEK_DAY_MONDAY,
            self::WEEK_DAY_TUESDAY,
            self::WEEK_DAY_WEDNESDAY,
            self::WEEK_DAY_THURSDAY,
            self::WEEK_DAY_FRIDAY,
            self::WEEK_DAY_SATURDAY,
            self::WEEK_DAY_SUNDAY
        );
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return array(
            'dayOfWeek' => $this->dayOfWeek,
            'openingIntervals' => $this->openingHoursIntervals,
        );
    }
}
