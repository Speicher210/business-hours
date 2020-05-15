<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours\Day\Time;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;
use JsonSerializable;
use Throwable;
use Webmozart\Assert\Assert;
use function Safe\sprintf;
use function strpos;

class Time implements JsonSerializable
{
    protected int $hours;

    protected int $minutes = 0;

    protected int $seconds = 0;

    public function __construct(int $hours, int $minutes = 0, int $seconds = 0)
    {
        $this->setHours($hours);
        $this->setMinutes($minutes);
        $this->setSeconds($seconds);
    }

    public static function fromDate(DateTimeInterface $date) : self
    {
        return new self((int) $date->format('H'), (int) $date->format('i'), (int) $date->format('s'));
    }

    /**
     * @throws InvalidArgumentException If the passed time is invalid.
     */
    public static function fromString(string $time) : Time
    {
        Assert::notEmpty($time, 'Invalid time %s.');

        try {
            $date = new DateTime($time);
        } catch (Throwable $e) {
            throw new InvalidArgumentException(sprintf('Invalid time "%s".', $time), 0, $e);
        }

        $return = static::fromDate($date);
        if (strpos($time, '24') === 0) {
            $return->setHours(24);
        }

        return $return;
    }

    public static function fromSeconds(int $seconds) : Time
    {
        if ($seconds < 0 || $seconds > 86400) {
            throw new InvalidArgumentException(sprintf('Invalid time "%s".', $seconds));
        }

        $data = [
            'hours' => (int) ($seconds / 3600),
            'minutes' => ($seconds / 60) % 60,
            'seconds' => $seconds % 60,
        ];

        return self::fromArray($data);
    }

    /**
     * @param int[] $data
     *
     * @psalm-param array{hours: int, minutes?: int, seconds?: int} $data
     */
    public static function fromArray(array $data) : Time
    {
        if (! isset($data['hours'])) {
            throw new InvalidArgumentException('Array is not valid.');
        }

        return new Time(
            $data['hours'],
            $data['minutes'] ?? 0,
            $data['seconds'] ?? 0
        );
    }

    /**
     * Checks if this time is before or equal to an other time.
     *
     * @param Time $other The time to compare it against.
     */
    public function isBeforeOrEqual(Time $other) : bool
    {
        return $this->toSeconds() <= $other->toSeconds();
    }

    /**
     * Checks if this time is after or equal to an other time.
     *
     * @param Time $other The time to compare it against.
     */
    public function isAfterOrEqual(Time $other) : bool
    {
        return $this->toSeconds() >= $other->toSeconds();
    }

    /**
     * Check if this time is equal to another time.
     *
     * @param Time $other The time to compare it against.
     */
    public function isEqual(Time $other) : bool
    {
        return $this->toSeconds() === $other->toSeconds();
    }

    /**
     * Get the time representation in seconds.
     */
    public function toSeconds() : int
    {
        return 3600 * $this->hours + 60 * $this->minutes + $this->seconds;
    }

    /**
     * Set the hours.
     *
     * @param int $hours The hours.
     */
    public function setHours(int $hours) : void
    {
        $this->timeElementsAreValid($hours, $this->minutes, $this->seconds);

        $this->hours = $hours;
    }

    /**
     * Get the hours.
     */
    public function getHours() : int
    {
        return $this->hours;
    }

    /**
     * Set the minutes.
     *
     * @param int $minutes The minutes
     */
    public function setMinutes(int $minutes) : void
    {
        $this->timeElementsAreValid($this->hours, $minutes, $this->seconds);

        $this->minutes = $minutes;
    }

    /**
     * Get the minutes.
     */
    public function getMinutes() : int
    {
        return $this->minutes;
    }

    /**
     * Set the seconds.
     *
     * @param int $seconds The seconds.
     */
    public function setSeconds(int $seconds) : void
    {
        $this->timeElementsAreValid($this->hours, $this->minutes, $seconds);

        $this->seconds = $seconds;
    }

    /**
     * Get the seconds.
     */
    public function getSeconds() : int
    {
        return $this->seconds;
    }

    /**
     * Check if the time elements are valid.
     *
     * @param int $hours The hours.
     * @param int $minutes The minutes.
     * @param int $seconds The seconds.
     *
     * @throws InvalidArgumentException If the elements are not valid.
     */
    private function timeElementsAreValid(int $hours, int $minutes, int $seconds) : bool
    {
        $exception = new InvalidArgumentException(
            sprintf('Invalid time "%02d:%02d:%02d".', $hours, $minutes, $seconds)
        );

        if ((int) sprintf('%d%02d%02d', $hours, $minutes, $seconds) > 240000) {
            throw $exception;
        }

        if ($hours < 0 || $minutes < 0 || $seconds < 0) {
            throw $exception;
        }

        if ($hours <= 24 && $minutes <= 59 && $seconds <= 59) {
            return true;
        }

        throw $exception;
    }

    /**
     * @return array<string,int>
     */
    public function jsonSerialize() : array
    {
        return [
            'hours' => $this->hours,
            'minutes' => $this->minutes,
            'seconds' => $this->seconds,
        ];
    }

    public function asString() : string
    {
        return sprintf('%02d:%02d:%02d', $this->hours, $this->minutes, $this->seconds);
    }

    public function __toString() : string
    {
        return $this->asString();
    }
}
