<?php

namespace Speicher210\BusinessHours;

/**
 * Business interface.
 */
interface BusinessInterface
{
    /**
     * Tells if a given date is within business hours.
     *
     * @param \DateTime $date
     * @return boolean
     */
    public function within(\DateTime $date);

    /**
     * Returns the next business date and time when it will enter the opening hours or closing hours.
     *
     * @param \DateTime $date The date.
     * @return \DateTime
     */
    public function getNextChangeDateTime(\DateTime $date);

    /**
     * Returns the closest business opening hours interval for the given date.
     *
     * @param \DateTime $date The date.
     * @return DateTimeInterval
     */
    public function closestDateInterval(\DateTime $date);
}
