<?php

/**
 * This file is part of Business-hours.
 * Copyright (c) 2015 - 2016 original code: Florian Voutzinos <florian@voutzinos.com
 * Copyright (c) 2015 - 2017 additions and changes: Speicher 210 GmbH
 * For the full copyright and license information, please view the LICENSE * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Speicher210\BusinessHours;

use DateTime;
use DateTimeZone;
use JsonSerializable;
use Speicher210\BusinessHours\Day\DayInterface;

interface BusinessHoursInterface extends JsonSerializable
{
    /**
     * Get the timezone for this business hours.
     */
    public function getTimezone() : DateTimeZone;

    /**
     * @return DayInterface[]
     */
    public function getDays() : array;

    /**
     * Check if a given date is within business hours.
     */
    public function within(DateTime $date) : bool;

    /**
     * Returns the next business hours date and time when it will enter the opening hours or closing hours.
     *
     * @param DateTime $date The date.
     */
    public function getNextChangeDateTime(DateTime $date) : DateTime;

    /**
     * Returns the previous business hours date and time when it was in the opening hours or closing hours.
     *
     * @param DateTime $date The date.
     */
    public function getPreviousChangeDateTime(DateTime $date) : DateTime;
}
