<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours\Day\Time;

use JsonSerializable;

interface TimeIntervalInterface extends JsonSerializable
{
    /**
     * Check if the interval contains the given time.
     *
     * @param Time $time The time to check.
     */
    public function contains(Time $time): bool;

    public function getStart(): Time;

    public function getEnd(): Time;
}
