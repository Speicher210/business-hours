<?php

namespace Speicher210\BusinessHours;

/**
 * Represents a standard business day.
 */
class Day extends AbstractDay
{
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
