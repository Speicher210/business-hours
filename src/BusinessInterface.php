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
     *
     * @return bool
     */
    public function within(\DateTime $date);
    
    /**
     * Returns the closest business date and time for the given date.
     *
     * @param \DateTime $date The date.
     *
     * @return \DateTime
     */
    public function closest(\DateTime $date);

    /**
     * Returns the closest business opening hours interval for the given date.
     *
     * @param \DateTime $date The date.
     *
     * @return \DateTime[]
     */
    public function closestDateInterval(\DateTime $date);
}
