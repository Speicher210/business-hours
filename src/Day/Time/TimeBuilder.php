<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours\Day\Time;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;
use Throwable;
use Webmozart\Assert\Assert;
use function Safe\sprintf;
use function strpos;

class TimeBuilder
{
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

    public static function fromDate(DateTimeInterface $date) : Time
    {
        return new Time((int) $date->format('H'), (int) $date->format('i'), (int) $date->format('s'));
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
}
