<?php

namespace Speicher210\BusinessHours;

/**
 * Represents a time.
 */
class Time implements \JsonSerializable
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
    protected $minutes = 0;

    /**
     * The seconds part of the time.
     *
     * @var integer
     */
    protected $seconds = 0;

    /**
     * Constructor.
     *
     * @param integer $hours The hours.
     * @param integer $minutes The minutes.
     * @param integer $seconds The seconds.
     */
    public function __construct($hours, $minutes = 0, $seconds = 0)
    {
        $this->setHours($hours);
        $this->setMinutes($minutes);
        $this->setSeconds($seconds);
    }

    /**
     * Creates a new time from a string.
     *
     * @param string $time
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
     * Set the hours.
     *
     * @param integer $hours The hours.
     * @return Time
     */
    public function setHours($hours)
    {
        $hours = (int)$hours;

        $this->timeElementsAreValid($hours, $this->minutes, $this->seconds);

        $this->hours = (int)$hours;

        return $this;
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
     * Set the minutes.
     *
     * @param integer $minutes The minutes
     * @return Time
     */
    public function setMinutes($minutes)
    {
        $minutes = (int)$minutes;

        $this->timeElementsAreValid($this->hours, $minutes, $this->seconds);

        $this->minutes = (int)$minutes;

        return $this;
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
     * Set the seconds.
     *
     * @param integer $seconds The seconds.
     * @return Time
     */
    public function setSeconds($seconds)
    {
        $seconds = (int)$seconds;

        $this->timeElementsAreValid($this->hours, $this->minutes, $seconds);

        $this->seconds = (int)$seconds;

        return $this;
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
     * Check if the time elements are valid.
     *
     * @param integer $hours The hours.
     * @param integer $minutes The minutes.
     * @param integer $seconds The seconds.
     * @return boolean
     * @throws \InvalidArgumentException If the elements are not valid.
     */
    private function timeElementsAreValid($hours, $minutes, $seconds)
    {
        $exception = new \InvalidArgumentException(
            sprintf('Invalid time "%02d:%02d:%02d".', $hours, $minutes, $seconds)
        );

        if ((int)sprintf('%d%02d%02d', $hours, $minutes, $seconds) > 240000) {
            throw $exception;
        } elseif ($hours < 0 || $minutes < 0 || $seconds < 0) {
            throw $exception;
        } elseif ($hours <= 24 && $minutes <= 59 && $seconds <= 59) {
            return true;
        }

        throw $exception;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return array(
            'hours' => $this->hours,
            'minutes' => $this->minutes,
            'seconds' => $this->seconds,
        );
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
