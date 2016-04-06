<?php

namespace Speicher210\BusinessHours;

/**
 * BusinessHours interface.
 */
interface BusinessHoursInterface extends \JsonSerializable
{
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
     * Returns the closest business hours opening hours interval for the given date.
     *
     * @param \DateTime $date The date.
     * @return DateTimeInterval
     */
    public function closestDateInterval(\DateTime $date);
}
