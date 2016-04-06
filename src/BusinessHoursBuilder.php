<?php

namespace Speicher210\BusinessHours;

use Speicher210\BusinessHours\Day\DayBuilder;

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
}
