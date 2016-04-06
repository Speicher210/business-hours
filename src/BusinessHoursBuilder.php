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

    /**
     * Create a new BusinessHours with a different timezone from an existing BusinessHours.
     *
     * @param BusinessHours $businessHours The original business hours.
     * @param \DateTimeZone $newTimezone The new timezone.
     * @return BusinessHours
     */
    public static function shiftToTimezone(BusinessHours $businessHours, \DateTimeZone $newTimezone)
    {
        if ($businessHours->getTimezone()->getName() === $newTimezone->getName()) {
            return clone $businessHours;
        }

        $days = array();
        // TODO implement
        return new BusinessHours($days, $newTimezone);
    }
}
