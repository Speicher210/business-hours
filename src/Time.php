<?php

namespace Speicher210\BusinessHours;

/**
 * Represents a time.
 */
class Time
{
    /**
     * The hours part of the time.
     *
     * @var integer
     */
    protected $hours;

    /**
     * The minutes part of the time.
     *
     * @var integer
     */
    protected $minutes;

    /**
     * The seconds part of the time.
     *
     * @var integer
     */
    protected $seconds;

    /**
     * Constructor.
     *
     * @param integer $hours The hours.
     * @param integer $minutes The minutes.
     * @param integer $seconds The seconds.
     */
    public function __construct($hours, $minutes, $seconds = 0)
    {
        $this->hours = (int)$hours;
        $this->minutes = (int)$minutes;
        $this->seconds = (int)$seconds;
    }

    /**
     * Creates a new time from a string.
     *
     * @param string $time
     * @return Time
     * @throws \InvalidArgumentException If the passed time is invalid
     */
    public static function fromString($time)
    {
        try {
            $date = new \DateTime($time);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf('Invalid time "%s".', $time));
        }

        return static::fromDate($date);
    }

    /**
     * Creates a new time from a date.
     *
     * @param \DateTime $date
     * @return Time
     */
    public static function fromDate(\DateTime $date)
    {
        return new static($date->format('H'), $date->format('i'), $date->format('s'));
    }

    /**
     * Checks if this time is before or equal to an other time.
     *
     * @param Time $other The time to compare it against.
     * @return boolean
     */
    public function isBeforeOrEqual(Time $other)
    {
        return $this->toInteger() <= $other->toInteger();
    }

    /**
     * Checks if this time is after or equal to an other time.
     *
     * @param Time $other The time to compare it against.
     * @return boolean
     */
    public function isAfterOrEqual(Time $other)
    {
        return $this->toInteger() >= $other->toInteger();
    }

    /**
     * Check if this time is equal to another time.
     *
     * @param Time $other The time to compare it against.
     * @return boolean
     */
    public function isEqual(Time $other)
    {
        return $this->toInteger() === $other->toInteger();
    }

    /**
     * Get the integer representation of the time.
     *
     * @return integer
     */
    public function toInteger()
    {
        return (int)sprintf('%d%02d%02d', $this->hours, $this->minutes, $this->seconds);
    }

    /**
     * Get the hours.
     *
     * @return integer
     */
    public function getHours()
    {
        return $this->hours;
    }

    /**
     * Get the minutes.
     *
     * @return integer
     */
    public function getMinutes()
    {
        return $this->minutes;
    }

    /**
     * Get the seconds.
     *
     * @return integer
     */
    public function getSeconds()
    {
        return $this->seconds;
    }

    /**
     * Returns a string representation of the time.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%02d:%02d:%02d', $this->hours, $this->minutes, $this->seconds);
    }
}
