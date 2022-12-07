<?php

/**
 * This file is part of Business-hours.
 * Copyright (c) 2015 - 2016 original code: Florian Voutzinos <florian@voutzinos.com
 * Copyright (c) 2015 - 2017 additions and changes: Speicher 210 GmbH
 * For the full copyright and license information, please view the LICENSE * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Speicher210\BusinessHours\Day;

use JsonSerializable;
use Speicher210\BusinessHours\Day\Time\Time;
use Speicher210\BusinessHours\Day\Time\TimeIntervalInterface;

interface DayInterface extends JsonSerializable
{
    public const WEEK_DAY_MONDAY    = 1;
    public const WEEK_DAY_TUESDAY   = 2;
    public const WEEK_DAY_WEDNESDAY = 3;
    public const WEEK_DAY_THURSDAY  = 4;
    public const WEEK_DAY_FRIDAY    = 5;
    public const WEEK_DAY_SATURDAY  = 6;
    public const WEEK_DAY_SUNDAY    = 7;

    public function getDayOfWeek(): int;

    public function getDayOfWeekName(): string;

    /**
     * @return TimeIntervalInterface[]
     */
    public function getOpeningHoursIntervals(): array;

    public function getOpeningTime(): Time;

    public function getClosingTime(): Time;

    /**
     * Get the closest opening hours interval for the given time (including it or in the past).
     *
     * @param Time $time The time.
     */
    public function getClosestPreviousOpeningHoursInterval(Time $time): TimeIntervalInterface|null;

    /**
     * Get the closest opening hours interval for the given time (including it or in the future).
     *
     * @param Time $time The time.
     */
    public function getClosestNextOpeningHoursInterval(Time $time): TimeIntervalInterface|null;

    /**
     * Get the previous opening hours interval excluding current (if inside of given time).
     *
     * @param Time $time The time.
     */
    public function getPreviousOpeningHoursInterval(Time $time): TimeIntervalInterface|null;

    /**
     * Get the next opening hours interval excluding current (if inside of given time).
     *
     * @param Time $time The time.
     */
    public function getNextOpeningHoursInterval(Time $time): TimeIntervalInterface|null;

    /**
     * Checks if the given time is within opening hours of the day.
     *
     * @param Time $time The time
     */
    public function isWithinOpeningHours(Time $time): bool;
}
