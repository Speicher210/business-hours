<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours\Day;

class Day extends AbstractDay
{
    /**
     * @return int[]
     */
    public static function getDaysOfWeek() : array
    {
        return [
            self::WEEK_DAY_MONDAY,
            self::WEEK_DAY_TUESDAY,
            self::WEEK_DAY_WEDNESDAY,
            self::WEEK_DAY_THURSDAY,
            self::WEEK_DAY_FRIDAY,
            self::WEEK_DAY_SATURDAY,
            self::WEEK_DAY_SUNDAY,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize() : array
    {
        return [
            'dayOfWeek' => $this->dayOfWeek,
            'openingIntervals' => $this->openingHoursIntervals,
        ];
    }
}
