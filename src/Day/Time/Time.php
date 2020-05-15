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

    protected int $minutes;

    protected int $seconds;

    public function __construct(int $hours, int $minutes = 0, int $seconds = 0)
    {
        $this->assertTimeElementsAreValid($hours, $minutes, $seconds);

        $this->hours   = $hours;
        $this->minutes = $minutes;
        $this->seconds = $seconds;
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
            return $return->withHours(24);
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

    public function withHours(int $hours) : self
    {
        return new self($hours, $this->minutes, $this->seconds);
    }

    public function hours() : int
    {
        return $this->hours;
    }

    public function withMinutes(int $minutes) : self
    {
        return new self($this->hours, $minutes, $this->seconds);
    }

    public function minutes() : int
    {
        return $this->minutes;
    }

    public function withSeconds(int $seconds) : self
    {
        return new self($this->hours, $this->minutes, $seconds);
    }

    public function seconds() : int
    {
        return $this->seconds;
    }

    /**
     * Check if the time elements are valid.
     *
     * @throws InvalidArgumentException If the elements are not valid.
     */
    private function assertTimeElementsAreValid(int $hours, int $minutes, int $seconds) : bool
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
