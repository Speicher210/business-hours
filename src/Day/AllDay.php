<?php

declare(strict_types = 1);

namespace Speicher210\BusinessHours\Day;

use Speicher210\BusinessHours\Day\Time\AllDayTimeInterval;

/**
 * A day with an all day open interval.
 */
class AllDay extends Day
{
    /**
     * @param integer $dayOfWeek The day of the week.
     */
    public function __construct(int $dayOfWeek)
    {
        $openingHoursIntervals = [
            new AllDayTimeInterval(),
        ];

        parent::__construct($dayOfWeek, $openingHoursIntervals);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();
        $data['allDay'] = true;

        return $data;
    }
}
