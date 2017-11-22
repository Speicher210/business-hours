<?php

declare(strict_types = 1);

namespace Speicher210\BusinessHours\Day\Time;

/**
 * Time interval that spans covering the day completely.
 */
class AllDayTimeInterval extends TimeInterval
{
    public function __construct()
    {
        parent::__construct(new Time(0, 0, 0), new Time(24, 0, 0));
    }
}
