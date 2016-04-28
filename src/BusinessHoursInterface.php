<?php
/* 

* This file is part of Business-hours. 
* Copyright (c) 2015 - 2016 original code: Florian Voutzinos <florian@voutzinos.com
* Copyright (c) 2015 - 2016 additions and changes: Speicher 210 GmbH
* For the full copyright and license information, please view the LICENSE * file that was distributed with this source code. 

*/

namespace Speicher210\BusinessHours;

use Speicher210\BusinessHours\Day\DayInterface;

/**
 * BusinessHours interface.
 */
interface BusinessHoursInterface extends \JsonSerializable
{
    /**
     * Get the timezone for this business hours.
     *
     * @return \DateTimeZone
     */
    public function getTimezone();

    /**
     * Get the days.
     *
     * @return DayInterface[]
     */
    public function getDays();

    /**
     * Check if a given date is within business hours.
     *
     * @param \DateTime $date
     * @return boolean
     */
    public function within(\DateTime $date);

    /**
     * Returns the next business hours date and time when it will enter the opening hours or closing hours.
     *
     * @param \DateTime $date The date.
     * @return \DateTime
     */
    public function getNextChangeDateTime(\DateTime $date);

    /**
     * Returns the previous business hours date and time when it was in the opening hours or closing hours.
     *
     * @param \DateTime $date The date.
     * @return \DateTime
     */
    public function getPreviousChangeDateTime(\DateTime $date);
}
