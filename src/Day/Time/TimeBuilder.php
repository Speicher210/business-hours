<?php

namespace Speicher210\BusinessHours\Day\Time;

/**
 * Builder for Time.
 */
class TimeBuilder
{

    /**
     * Create a new Time from an array.
     *
     * @param array $data The data.
     * @return Time
     */
    public static function fromArray(array $data)
    {
        if (!isset($data['hours'])) {
            throw new \InvalidArgumentException('Array is not valid.');
        }

        return new Time(
            $data['hours'],
            isset($data['minutes']) ? $data['minutes'] : 0,
            isset($data['seconds']) ? $data['seconds'] : 0
        );
    }

    /**
     * Create a new time from a string.
     *
     * @param string $time The time as a string.
     * @return Time
     * @throws \InvalidArgumentException If the passed time is invalid.
     */
    public static function fromString($time)
    {
        if (empty($time)) {
            throw new \InvalidArgumentException('Invalid time "".');
        }

        try {
            $date = new \DateTime($time);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf('Invalid time "%s".', $time), 0, $e);
        }

        $return = static::fromDate($date);
        if (strpos($time, '24') === 0) {
            $return->setHours(24);
        }

        return $return;
    }

    /**
     * Create a new time from a date.
     *
     * @param \DateTime $date The date.
     * @return Time
     */
    public static function fromDate(\DateTime $date)
    {
        return new Time($date->format('H'), $date->format('i'), $date->format('s'));
    }
}
