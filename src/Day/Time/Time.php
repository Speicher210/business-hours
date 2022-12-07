<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours\Day\Time;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;
use JsonSerializable;
use Psl\Math;
use Psl\Str;
use Psl\Type;
use Throwable;
use Webmozart\Assert\Assert;

use function round;
use function str_starts_with;

use const PHP_ROUND_HALF_DOWN;
use const PHP_ROUND_HALF_UP;

class Time implements JsonSerializable
{
    public const ROUND_HALF_UP = PHP_ROUND_HALF_UP;

    public const ROUND_HALF_DOWN = PHP_ROUND_HALF_DOWN;

    public const ROUND_UP = 5;

    public const ROUND_DOWN = 6;

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

    public static function fromDate(DateTimeInterface $date): self
    {
        return new self(
            Type\int()->coerce($date->format('H')),
            Type\int()->coerce($date->format('i')),
            Type\int()->coerce($date->format('s')),
        );
    }

    /**
     * @throws InvalidArgumentException If the passed time is invalid.
     */
    public static function fromString(string $time): Time
    {
        Assert::notEmpty($time, 'Invalid time %s.');

        try {
            $date = new DateTime($time);
        } catch (Throwable $e) {
            throw new InvalidArgumentException(Str\format('Invalid time "%s".', $time), 0, $e);
        }

        $return = static::fromDate($date);
        if (str_starts_with($time, '24')) {
            return $return->withHours(24);
        }

        return $return;
    }

    public static function fromSeconds(int $seconds): Time
    {
        if ($seconds < 0 || $seconds > 86400) {
            throw new InvalidArgumentException(
                Str\format(
                    'Invalid time "%s%02d:%02d:%02d".',
                    $seconds < 0 ? '-' : '',
                    Math\abs(Math\div($seconds, 3600)),
                    Math\abs(Math\div($seconds, 60) % 60),
                    Math\abs($seconds % 60),
                ),
            );
        }

        $data = [
            'hours' => Math\div($seconds, 3600),
            'minutes' => Math\div($seconds, 60) % 60,
            'seconds' => $seconds % 60,
        ];

        return self::fromArray($data);
    }

    /**
     * @param array{hours: int, minutes?: int, seconds?: int} $data
     */
    public static function fromArray(array $data): Time
    {
        Assert::keyExists($data, 'hours', 'Array is not valid.');

        return new Time(
            $data['hours'],
            $data['minutes'] ?? 0,
            $data['seconds'] ?? 0,
        );
    }

    /**
     * Checks if this time is before or equal to an other time.
     *
     * @param Time $other The time to compare it against.
     */
    public function isBeforeOrEqual(Time $other): bool
    {
        return $this->toSeconds() <= $other->toSeconds();
    }

    /**
     * Checks if this time is after or equal to an other time.
     *
     * @param Time $other The time to compare it against.
     */
    public function isAfterOrEqual(Time $other): bool
    {
        return $this->toSeconds() >= $other->toSeconds();
    }

    /**
     * Check if this time is equal to another time.
     *
     * @param Time $other The time to compare it against.
     */
    public function isEqual(Time $other): bool
    {
        return $this->toSeconds() === $other->toSeconds();
    }

    /**
     * Get the time representation in seconds.
     */
    public function toSeconds(): int
    {
        return 3600 * $this->hours + 60 * $this->minutes + $this->seconds;
    }

    public function hours(): int
    {
        return $this->hours;
    }

    public function withHours(int $hours): self
    {
        return new self($hours, $this->minutes, $this->seconds);
    }

    /**
     * Add hours to the current time.
     *
     * @throws InvalidArgumentException If the result is not a valid time.
     */
    public function addHours(int $hours): self
    {
        return $this->addSeconds($hours * 3600);
    }

    /**
     * Subtract hours from the current time.
     *
     * @throws InvalidArgumentException If the result is not a valid time.
     */
    public function subtractHours(int $hours): self
    {
        return $this->subtractSeconds($hours * 3600);
    }

    public function minutes(): int
    {
        return $this->minutes;
    }

    public function withMinutes(int $minutes): self
    {
        return new self($this->hours, $minutes, $this->seconds);
    }

    /**
     * Add minutes to the current time.
     *
     * @throws InvalidArgumentException If the result is not a valid time.
     */
    public function addMinutes(int $minutes): self
    {
        return $this->addSeconds($minutes * 60);
    }

    /**
     * Subtract seconds from the current time.
     *
     * @throws InvalidArgumentException If the result is not a valid time.
     */
    public function subtractMinutes(int $minutes): self
    {
        return $this->subtractSeconds($minutes * 60);
    }

    public function seconds(): int
    {
        return $this->seconds;
    }

    public function withSeconds(int $seconds): self
    {
        return new self($this->hours, $this->minutes, $seconds);
    }

    /**
     * Add seconds to the current time.
     *
     * @throws InvalidArgumentException If the result is not a valid time.
     */
    public function addSeconds(int $seconds): self
    {
        return self::fromSeconds($this->toSeconds() + $seconds);
    }

    /**
     * Subtract seconds from the current time.
     *
     * @throws InvalidArgumentException If the result is not a valid time.
     */
    public function subtractSeconds(int $seconds): self
    {
        return self::fromSeconds($this->toSeconds() - $seconds);
    }

    public function addTime(Time $time): self
    {
        return $this
            ->addHours($time->hours())
            ->addMinutes($time->minutes())
            ->addSeconds($time->seconds());
    }

    public function subtractTime(Time $time): self
    {
        return $this
            ->subtractHours($time->hours())
            ->subtractMinutes($time->minutes())
            ->subtractSeconds($time->seconds());
    }

    /**
     * Check if the time elements are valid.
     *
     * @throws InvalidArgumentException If the elements are not valid.
     */
    private function assertTimeElementsAreValid(int $hours, int $minutes, int $seconds): bool
    {
        $exception = new InvalidArgumentException(
            Str\format('Invalid time "%02d:%02d:%02d".', $hours, $minutes, $seconds),
        );

        if ((int) Str\format('%d%02d%02d', $hours, $minutes, $seconds) > 240000) {
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

    private function assertRoundingMode(int $roundingMode): void
    {
        Assert::oneOf(
            $roundingMode,
            [
                self::ROUND_HALF_UP,
                self::ROUND_HALF_DOWN,
                self::ROUND_UP,
                self::ROUND_DOWN,
            ],
        );
    }

    /**
     * @param int           $precision    Number of minutes to round.
     * @param self::ROUND_* $roundingMode The rounding mode. One of the ROUND_* constants.
     */
    public function roundToMinutes(int $precision, int $roundingMode = self::ROUND_HALF_UP): self
    {
        $this->assertRoundingMode($roundingMode);

        $roundingSeconds = $precision * 60;

        if ($roundingMode === self::ROUND_UP) {
            $newSeconds = Math\ceil($this->toSeconds() / $roundingSeconds) * $roundingSeconds;

            return self::fromSeconds((int) $newSeconds);
        }

        if ($roundingMode === self::ROUND_DOWN) {
            $newSeconds = Math\floor($this->toSeconds() / $roundingSeconds) * $roundingSeconds;

            return self::fromSeconds((int) $newSeconds);
        }

        $newSeconds = round($this->toSeconds() / $roundingSeconds, 0, $roundingMode) * $roundingSeconds;

        return self::fromSeconds((int) $newSeconds);
    }

    /**
     * @param self::ROUND_* $roundingMode
     */
    public function roundToHour(int $roundingMode = self::ROUND_HALF_UP): self
    {
        return $this->roundToMinutes(60, $roundingMode);
    }

    public function equals(Time $other): bool
    {
        return $this->compareTo($other) === 0;
    }

    public function greaterThan(Time $other): bool
    {
        return $this->compareTo($other) > 0;
    }

    public function greaterThanOrEqual(Time $other): bool
    {
        return $this->compareTo($other) >= 0;
    }

    public function lessThan(Time $other): bool
    {
        return $this->compareTo($other) < 0;
    }

    public function lessThanOrEqual(Time $other): bool
    {
        return $this->compareTo($other) <= 0;
    }

    public function compareTo(Time $time): int
    {
        return $this->toSeconds() <=> $time->toSeconds();
    }

    public static function min(Time $first, Time ...$otherTimes): Time
    {
        $min = $first;

        foreach ($otherTimes as $time) {
            if (! $time->lessThan($min)) {
                continue;
            }

            $min = $time;
        }

        return $min;
    }

    public static function max(Time $first, Time ...$otherTimes): Time
    {
        $max = $first;

        foreach ($otherTimes as $time) {
            if (! $time->greaterThan($max)) {
                continue;
            }

            $max = $time;
        }

        return $max;
    }

    /**
     * @return array<string,int>
     */
    public function jsonSerialize(): array
    {
        return [
            'hours' => $this->hours,
            'minutes' => $this->minutes,
            'seconds' => $this->seconds,
        ];
    }

    public function asString(): string
    {
        return Str\format('%02d:%02d:%02d', $this->hours, $this->minutes, $this->seconds);
    }

    public function __toString(): string
    {
        return $this->asString();
    }
}
